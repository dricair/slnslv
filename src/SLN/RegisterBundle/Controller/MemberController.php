<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SLN\RegisterBundle\Entity\User;

use SLN\RegisterBundle\Entity\Repository;

class MemberController extends Controller
{
    /*
     * List the members
     */
    public function listAction()
    {
        $members = $this->getRepository()->getAll();

        return $this->render('SLNRegisterBundle:Member:list.html.twig', array('members' => $members));
    }

    /*
     * Inscription sheets
     */
    public function inscriptionsAction($user_id) {
        $user = $this->getUserFromID($user_id);
        $licensees = $this->getLicenseeRepository()->getLicenseesForUser($user_id);

        $year = date('Y');
        $month = date('n');
        if ($month < 5) $year = $year - 1;

        return $this->render('SLNRegisterBundle:Member:inscription_sheets.html.twig', array('user' => $user, 'licensees' => $licensees,
          'year' => $year));
    }

    /*
     * Permits changing the roles for the users
     */
    public function roleAction()
    {
        $members = $this->getRepository()->getAll();

        return $this->render('SLNRegisterBundle:Member:list_role.html.twig', array('members' => $members));
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

        if ($user->getId() != $currentUser->getId()) {
            // TODO: check that user is current user or a staff user.
            throw new AccessDeniedException();
        }

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
