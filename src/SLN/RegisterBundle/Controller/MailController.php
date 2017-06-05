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

use SLN\RegisterBundle\Entity\LicenseeMail;
use SLN\RegisterBundle\Form\Type\LicenseeMailFormType;

/**
 * Mail controller.
 */
class MailController extends Controller {
    /**
     * Send a mail to a licensee or list of licensees, with facilities to chose them
     *
     * @param int $id Mail to edit (0 if none)
     * @param int $defaultLicensee Licensee selected by default, none if Null
     * @param int $defaultGroup Groupe selected by default, none if Null
     */
    public function mailAction(Request $request, $saison_id, $id=0, $defaultLicensee = null, $defaultGroup=null) {
        $title = "Envoi de mails";

        $defaultLicensees = array();

        $em = $this->getDoctrine()->getManager();
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->findOrCurrent($saison_id);

        if (!$saison) 
              throw $this->createNotFoundException("Cette saison n'existe pas.");

        if ($id == 0) {
            $mail = new LicenseeMail();
            $mail->setSaison($saison);
        }
        else {
            $mail = $this->getLicenseeMailRepository()->find($id);

            if (!$mail) {
                throw $this->createNotFoundException('Ce mail n\'existe pas dans la base de données.');
            }

            $defaultLicensees = $mail->getLicensees();
            $defaultLicensee = null;
            $defaultGroupe = null;
        }

        if ($defaultGroup) {
            $defaultGroup = $this->getGroupeRepository()->find($defaultGroup);
            if (is_object($defaultGroup))
                foreach($this->getLicenseeRepository()->getAllForGroupe($defaultGroup) as $licensee)
                    $defaultLicensees[] = $licensee;
        }

        if ($defaultLicensee) {
            $licensee = $this->getLicenseeRepository()->find($defaultLicensee);
            if (is_object($licensee)) $defaultLicensees[] = $licensee;
        }

        $request = $this->getRequest();
        $form = $this->createForm(new LicenseeMailFormType(), $mail, array("defaultGroup" => $defaultGroup, 
                                                                           "em" => $this->getDoctrine()->getManager()));
        $form->handleRequest($request);

        if ($form->isValid()) {

            $mail->setSender($this->getUser());

            $files = $mail->getFiles();
            $filesRepository = $this->getUploadFileRepository();

            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                if ($file->getNoId()) {
                    $new_file = $filesRepository->find($file->getId());
                    
                    if (!is_object($new_file)) {
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            sprintf("Le fichier %s n'a pas été trouvé. L'avez vous effacé ?", $file->getFilename())
                        );
                        unset($files[$i]);
                        $i--;
                    }

                    else
                        $files[$i] = $new_file;
                }
            }

            $mail->setFiles($files);

            $em = $this->getDoctrine()->getManager();
            $em->persist($mail);
            $em->flush();

            return $this->redirect($this->generateUrl('SLNRegisterBundle_mail_confirm', array('id' => $mail->getId())));
        }

        return $this->render('SLNRegisterBundle:Mail:edit.html.twig', array('id' => $id,
                                                                            'form' => $form->createView(), 
                                                                            'title' => $title, 
                                                                            'saison' => $saison,
                                                                            'defaultLicensees' => $defaultLicensees ));
    }


    /**
     * Page to confirm the sending of the mail, with the list of licensees. This page contains a button to start
     * sending the mails (Using AJAX)
     *
     */
    public function confirmAction(Request $request, $id) {
        $mail = $this->getLicenseeMailRepository()->find($id);

        if (!$mail) {
            throw $this->createNotFoundException('Ce mail n\'existe pas dans la base de données.');
        }

        return $this->render('SLNRegisterBundle:Mail:confirm.html.twig', array('id' => $mail->getId(),
                                                                               'licensees' => $mail->getLicensees(), 
                                                                               'title' => $mail->getTitle(), 
                                                                               'body' => $mail->getBody(),
                                                                               'files' => $mail->getFiles(),
                                                                               'saison' => $mail->getSaison()));
    }


    /**
     * Get list of mails, all or for a specific user
     */
    public function listAction(Request $request, $id=0, $page=1, $admin=FALSE) {
        $max_per_page = 10;

        if ($admin) {
            $mails = $this->getLicenseeMailRepository()->paginateMails($id, $page, $max_per_page);
        }

        $num_pages = intval((count($mails) + $max_per_page - 1) / $max_per_page);
        return $this->render('SLNRegisterBundle:Mail:list.html.twig', array('mails' => $mails,
                                                                            'id' => $id,
                                                                            'page' => $page,
                                                                            'num_pages' => $num_pages,
                                                                            'admin' => $admin));
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

    /**
     * Get repository for the groups
     *
     * @return GroupeRepository Repository for Groupe instances.
     */
    protected function getGroupeRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:Groupe');
    }

    /**
     * Get repository for the mails
     *
     * @return LicenseeMailRepository Repository for LicenseeMail instances.
     */
    protected function getLicenseeMailRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:LicenseeMail');
    }

    /**
     * Get repository for the uploaded files
     *
     * @return UploadFileRepository Repository for UploadFile instances.
     */
    protected function getUploadFileRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:UploadFile');
    }
}



