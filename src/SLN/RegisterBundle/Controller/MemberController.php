<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Form\Type\UserType;

use SLN\RegisterBundle\Entity\Repository;

class MemberController extends Controller
{
    /*
     * List the members
     */
    public function listAction()
    {
        $members = $this->getUserRepository()->getAll();

        return $this->render('SLNRegisterBundle:Member:list.html.twig', array('members' => $members));
    }

    /**
     * Form to create a new member or edit an existing one (From admin)
     */
    public function editAction($id) {
        if ($id == 0) {
          $user = new User();
        } else {
          $user = $this->getUserRepository()->find($id);

          if (!$user) {
              throw $this->createNotFoundException('Ce membre n\'existe pas dans la base de données.');
          }
        }

        $request = $this->getRequest();
        $form    = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()
                   ->getEntityManager();
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              sprintf("Le membre '%s %s' a été %s avec succès.", $user->getPrenom(), $user->getNom(), $id = 0 ? "ajouté" : "modifié")
            );

            return $this->redirect($this->generateUrl('SLNRegisterBundle_member_edit', array('id' => $user->getId())));
        }
 
        return $this->render('SLNRegisterBundle:Member:edit.html.twig', array(
            'member' => $user,
            'form' => $form->createView(),
            'title' => $id == 0 ? "Ajouter un membre" : "Modifier un membre",
            'id' => $id));
    }

    /*
     * Inscription sheets
     */
    public function inscriptionsAction($user_id) {
        $user = $this->getUserFromID($user_id);

        return $this->render('SLNRegisterBundle:Member:inscriptions.html.twig', array(
            'member' => $user));
    }


    /*
     * Inscription sheets
     */
    public function inscriptions_pdfAction($user_id) {
        $user = $this->getUserFromID($user_id);
        $licensees = $this->getLicenseeRepository()->getLicenseesForUser($user_id);

        $pdf = $this->container->get("white_october.tcpdf")->create();
        $assets = $this->container->get('templating.helper.assets');

        $title = "Feuilles d'inscriptions - {$user->getPrenom()} {$user->getNom()}";
        $first = True;
        foreach ($licensees as $licensee) {
            // For your people and no groupe selected, attach a default group (Not saved)
            if ($licensee->getGroupe() == Null and $licensee->getAge() < 12) {
                $groupe = new Groupe();
                $groupe->setNom("<Inconnu>");
                $licensee->setGroupe(new Groupe());
            }

            if ($licensee->getGroupe() != Null)
                $licensee->inscriptionSheet($pdf, $assets, $title=$first ? $title : "");
        }

        $response = new Response($pdf->Output('inscriptions.pdf', 'I'));
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }


    /**
     * Get user from ID. If user is not current ID or a user with staff role, 
     * raise an exception
     */
    public function getUserFromID($user_id) {
        $user = $this->getUserRepository()->find($user_id);

        if (!$user) {
            throw $this->createNotFoundException("Cet utilisateur n'existe pas.");
        }

        $currentUser = $this->getUser();

        // Only permit access from this user, or a user which is Admin
        //if ($user->getId() != $currentUser->getId() && !$currentUser->hasRole('ROLE_ADMIN')) {
        //    throw new AccessDeniedException();
        //}

        return $user;
    }


    /*
     * Get the repository for users
     *
     * @return Repository
     */
    protected function getUserRepository() {
        $em = $this->getDoctrine()
                   ->getEntityManager();
        return $em->getRepository('SLNRegisterBundle:User');
    }

    /*
     * Get the repository for licensees
     *
     * @return Repository
     */
    protected function getLicenseeRepository() {
        $em = $this->getDoctrine()
                   ->getEntityManager();
        return $em->getRepository('SLNRegisterBundle:Licensee');
    }

}
