<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * Form to create a new licensee or edit an existing one
     */
    public function editAction($id, $user_id=0, $inside_page=FALSE) {
        if ($id == 0) {
            $user = $this->getUserFromID($user_id);
            $licensee = new Licensee();
            $licensee->setUser($user);
        } else {
          $em = $this->getDoctrine()->getEntityManager();
          $licensee = $em->getRepository('SLNRegisterBundle:Licensee')->find($id);
          $user = $this->getUserFromID($licensee->getUser()->getId());

          if (!$licensee) {
              throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
          }
        }

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
 
        return $this->render($inside_page ? 'SLNRegisterBundle:Licensee:form.html.twig' :
                                            'SLNRegisterBundle:Licensee:edit.html.twig', array(
            'licensee' => $licensee,
            'form' => $form->createView(),
            'title' => $id == 0 ? "Ajouter un licencié" : "Modifier un licencié",
            'id' => $id,
            'user_id' => $user_id));
    }


    /**
      * Delete a licensee from a user
      */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $licensee = $em->getRepository('SLNRegisterBundle:Licensee')->find($id);
        $user = $this->getUserFromID($licensee->getUser()->getId());

        if (!$licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        $user->removeLicensee($licensee);
        $em->remove($licensee);
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('SLNRegisterBundle_homepage'));
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
            throw new AccessDeniedException();
        }

        return $user;
    }
}
