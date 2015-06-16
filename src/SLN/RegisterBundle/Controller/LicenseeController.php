<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

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

        $licensee = $this->getLicenseeRepository()->find($id);

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
    public function editAction($id, $user_id=0, $inside_page=FALSE, $admin=FALSE) {
        if ($id == 0) {
          $user = $this->getUserFromID($user_id);
          $licensee = new Licensee();
          $licensee->setUser($user);
        } else {
          $em = $this->getDoctrine()->getEntityManager();
          $licensee = $this->getLicenseeRepository()->find($id);
          $user = $this->getUserFromID($licensee->getUser()->getId());

          if (!$licensee) {
              throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
          }
        }

        $request = $this->getRequest();
        $form    = $this->createForm(new LicenseeType(), $licensee);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($licensee);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              sprintf("Le licencié '%s %s' a été %s avec succès.", $licensee->getPrenom(), $licensee->getNom(), $id = 0 ? "ajouté" : "modifié")
            );

            if ($admin) {
              return $this->redirect($this->generateUrl('SLNRegisterBundle_admin_licensee_edit', array(
                                     'id' => $licensee->getId(), $user_id => $licensee->getUser()->getId())
                                    ));
            } else {
              return $this->redirect($this->generateUrl('SLNRegisterBundle_homepage', array()));
            }
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
    public function deleteAction($id, $admin=FALSE) {
        $licensee = $this->getLicenseeRepository()->find($id);
        $user = $this->getUserFromID($licensee->getUser()->getId());

        if (!$licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        $user->removeLicensee($licensee);

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($licensee);
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('SLNRegisterBundle_homepage'));
    }

    /** 
     * List licensee with optional filters and sorting
     */
    public function listAction($admin=FALSE) {
        $licensees = $this->getLicenseeRepository()->getAllByGroups();

        return $this->render('SLNRegisterBundle:Licensee:list.html.twig', array('licensees' => $licensees));
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

        if ($user->getId() != $currentUser->getId() and !$currentUser->hasRole('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $user;
    }


    /**
     * Create a PDF and return it
     *
     * @return Response
     */
    public function pdftestAction() {
        $pdf = $this->container->get("white_october.tcpdf")->create();

        $pdf->AddPage();
        $response = new Response($pdf->Output('test.pdf', 'I'));
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }


    /**
     * Get repository for the licensees
     *
     * @return Repository
     */
    protected function getLicenseeRepository() {
        $em = $this->getDoctrine()
                   ->getEntityManager();
        return $em->getRepository('SLNRegisterBundle:Licensee');
    }
}
