<?php
/**
  * Group controller class. 
  *
  * Contains controller class to deal with groups. Mostly admin functions.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Form\Type\SaisonType;

/**
 * Saison controller.
 */
class SaisonController extends Controller {
    
    /**
     * Create or edit a saison. 
     *
     * Can be used for GET and POST requests. 
     *
     * @param int $id Id of the group to edit. If 0 or not specified, create a saison
     *
     * @return Response Rendered page
     */
    public function editAction($id=0) {
        if ($id == 0) {
            $saison = new Saison();
        } else {
          $em = $this->getDoctrine()->getManager();
          $saison = $em->getRepository('SLNRegisterBundle:Saison')->find($id);

          if (!is_object($saison)) {
              throw $this->createNotFoundException('Ce saison n\'existe pas dans la base de données.');
          }
        }

        $request = $this->getRequest();
        $form    = $this->createForm(new SaisonType(), $saison);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()
                   ->getManager();
            $em->persist($saison);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              sprintf("La saison '%s' a été %s avec succès.", $saison->getNom(), $id = 0 ? "ajouté" : "modifié")
            );

            return $this->redirect($this->generateUrl('SLNRegisterBundle_saison_show', array(
                'id' => $saison->getId()
            )));
        }

        else if ($request->getMethod() == 'POST') {
            $this->get('session')->getFlashBag()->add('error', "Des erreurs sont présentes dans le formulaire");
        }


        return $this->render('SLNRegisterBundle:Saison:edit.html.twig', array(
            'saison'  => $saison,
            'form'    => $form->createView(),
            'title'   => $id == 0 ? "Ajouter une saison" : "Editer cette saison",
            'id'      => $id
        ));
    }

    /**
     * Show a saison.
     *
     * Show the saison detail as well as the licensees that are in this saison.
     *
     * @param int  $id     Id of the group to show
     * @param bool $admin  True if the page is accessed with admin rights
     *
     * @return Response Rendered page
     */
    public function showAction($id, $admin=false) {
        $em = $this->getDoctrine()->getManager();
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->find($id);

        if (!$saison) {
            throw $this->createNotFoundException('Cette saison n\'existe pas dans la base de données.');
        }

        $licensees = $em->getRepository('SLNRegisterBundle:Licensee')->getAll($saison);

        return $this->render('SLNRegisterBundle:Saison:show.html.twig', array(
          'saison' => $saison,
          'licensees' => $licensees,
          'admin' => $admin));
    }


    /**
     * List of the groups.
     *
     * @return Response Rendered page.
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $saisons = $em->getRepository('SLNRegisterBundle:Saison')->findAll();

        return $this->render('SLNRegisterBundle:Saison:list.html.twig', array(
          'saisons' => $saisons));
    }

    /* TODO: TEMPORARY */
    public function updateAction() {
        $em = $this->getDoctrine()->getManager();

        $saison = $em->getRepository('SLNRegisterBundle:Saison')->find(1);
        $mails = $em->getRepository('SLNRegisterBundle:LicenseeMail')->findAll();
        
        foreach ($mails as &$mail) {
            $mail->setSaison($saison);
            $em->persist($mail);
        }

        $em->flush();

        return $this->showAction(1);
    }




}


