<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SLN\RegisterBundle\Entity\User;

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
     * Permits changing the roles for the users
     */
    public function roleAction()
    {
        $members = $this->getRepository()->getAll();

        return $this->render('SLNRegisterBundle:Member:list_role.html.twig', array('members' => $members));
    }



    /*
     * Get the repository for users
     *
     * @return Repository
     */
    protected function getRepository() {
        $em = $this->getDoctrine()
                   ->getEntityManager();
        return $em->getRepository('SLNRegisterBundle:User');
    }
}
