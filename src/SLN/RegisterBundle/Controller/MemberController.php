<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SLN\RegisterBundle\Entity\User;

class MemberController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()
                   ->getEntityManager();

        $members = $em->getRepository('SLNRegisterBundle:User')
                      ->getAll();

        return $this->render('SLNRegisterBundle:Member:index.html.twig', array('members' => $members));
    }
}
