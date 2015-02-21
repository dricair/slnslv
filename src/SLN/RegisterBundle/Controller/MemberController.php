<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MemberController extends Controller
{
    public function indexAction()
    {
        return $this->render('SLNRegisterBundle:Member:index.html.twig');
    }
}
