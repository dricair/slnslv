<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Form\LicenseeType;

/**
 * Licensee controller.
 */
class LicenseeController extends Controller
{
    /**
     * Show a Licensee entry
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $licensee = $em->getRepository('SLNRegisterBundle:Licensee')->find($id);

        if (!$licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        return $this->render('SLNRegisterBundle:Licensee:show.html.twig', array(
            'licensee' => $licensee,
        ));
    }

    /**
     * Form to create a new licensee
     */
    public function newAction($user_id) {
        $user = $this->getUserFromID($user_id);

        $licensee = new Licensee();
        $licensee->setUser($user);
        $form = $this->createForm(new LicenseeType(), $licensee);

        return $this->render('SLNRegisterBundle:Licensee:form.html.twig', array(
            'licensee' => $licensee,
            'form' => $form->createView()));
    }

    /**
     * Receive data from the form
     */
    public function createAction($user_id) {
        $user = $this->getUserFromID($user_id);

        $licensee = new Licensee();
        $licensee->setUser($user);
        $request = $this->getRequest();
        $form    = $this->createForm(new LicenseeType(), $licensee);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()
                   ->getEntityManager();
            $em->persist($licensee);
            $em->flush();

            return $this->redirect($this->generateUrl('SLNRegisterBundle_homepage', array(
                '#licensee-' . $licensee->getId()
            )));
        }

        return $this->render('SLNRegisterBundle:Licensee:create.html.twig', array(
            'licensee' => $licensee,
            'form'    => $form->createView()
        ));
    }


    /**
     * Get user from ID. If user is not current ID or a user with staff role, 
     * raise an exception
     */
    public function getUserFromID($user_id) {
        $em = $this->getDoctrine()
                   ->getEntityManager();

        $user = $em->getRepository('SLNRegisterBundle:User')->find($user_id);
        if (!$user) {
            throw $this->createNotFoundException("Cet utilisateur n'existe pas.");
        }

        $currentUser = $this->getUser();

        if ($user->getId() != $currentUser->getId()) {
            // TODO: check that user is current user or a staff user.
            return $this->createAccessDeniedException("L'accès à cette page n'est pas autorisé");
        }

        return $user;
    }
}
