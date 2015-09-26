<?php
/**
  * Mail controller class. 
  *
  * Contains controller class to deal with mail, to users or groups
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\LicenseeSelect;
use SLN\RegisterBundle\Form\LicenseeSelectType;

require_once("Html2Text.php");

/**
 * Mail controller.
 */
class MailController extends Controller {
    /**
     * Send a mail to a licensee or list of licensees, with facilities to chose them
     *
     * @param int $defaultLicensee Licensee selected by default, none if Null
     * @param int $defaultGroup Groupe selected by default, none if Null
     */
    public function licenseeAction(Request $request, $defaultLicensee = null, $defaultGroup=null) {
        $title = "Envoi de mails";

        $defaultLicensees = array();

        $select = new LicenseeSelect();

        // Restore from session data (Back button)
        $session = $request->getSession();
        if ($request->query->get('restore', false) and $session->has('mail/licensees')) {
            $licensee_list = $session->get('mail/licensees');
            $select->title = $session->get('mail/title');
            $select->body = $session->get('mail/body');
            
            $repository = $this->getLicenseeRepository();
            $defaultLicensees = $repository->findById($licensee_list);
        }
         
        else {
            if ($defaultGroup) {
                $defaultGroup = $em->getRepository('SLNRegisterBundle:Groupe')->find($defaultGroup);
                if (is_object($defaultGroup))
                    foreach($this->getLicenseeRepository()->getAllForGroupe($defaultGroup) as $licensee)
                        $defaultLicensees[] = $licensee;
            }

            if ($defaultLicensee) {
                $licensee = $this->getLicenseeRepository()->find($defaultLicensee);
                if (is_object($licensee)) $defaultLicensee[] = $licensee;
            }
        }

        $request = $this->getRequest();
        $form = $this->createForm(new LicenseeSelectType(), $select, array("defaultGroup" => $defaultGroup));
        $form->handleRequest($request);

        if ($form->isValid()) {

            // store an attribute for reuse during a later user request
            $licensee_list = array();
            foreach ($select->licensees as $licensee) {
                $licensee_list[] = $licensee;
            }

            $ht = new \Html2Text\Html2Text($select->body);
            $text_body = $ht->getText();
            $ht = new \Html2Text\Html2Text($select->title);
            $text_title = $ht->getText();

            $session->set('mail/licensees', $licensee_list);
            $session->set('mail/title', $select->title);
            $session->set('mail/body', $select->body);
            $session->set('mail/text_body', $text_body);
            $session->set('mail/text_title', $text_title);
            $session->set('mail/from', $request->getUri());

            return $this->redirect($this->generateUrl('SLNRegisterBundle_mail_confirm'));
        }

        return $this->render('SLNRegisterBundle:Mail:edit.html.twig', array('form' => $form->createView(), 'title' => $title, 'defaultLicensees' => $defaultLicensees ));
    }


    /**
     * Page to confirm the sending of the mail, with the list of licensees. This page contains a button to start
     * sending the mails (Using AJAX)
     *
     * Licensee list and mail information are transferred from previous POST using sessions
     *
     */
    public function confirmAction(Request $request) {
        $session = $request->getSession();

        if (!$session->has('mail/licensees')) {
            $session->getFlashBag()->add(
              'error',
              sprintf("Problème pour la récupération des données.")
            );

            return $this->redirect($this->generateUrl('SLNRegisterBundle_mail_licensee'));
        }

        $licensee_list = $session->get('mail/licensees');
        $title = $session->get('mail/title');
        $body = $session->get('mail/body');
        $text_body = $session->get('mail/text_body');
        $text_title = $session->get('mail/text_title');
        $from = $session->get('mail/from');

        $repository = $this->getLicenseeRepository();
        $licensees = $repository->findById($licensee_list);

        return $this->render('SLNRegisterBundle:Mail:confirm.html.twig', array('licensees' => $licensees, 'title' => $title, 'body' => $body, 'from' => $from));
    }


    /**
     * Get repository for the licensees
     *
     * @return LicenseeRepository Repository for Licensee instances.
     */
    protected function getLicenseeRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:Licensee');
    }
}



