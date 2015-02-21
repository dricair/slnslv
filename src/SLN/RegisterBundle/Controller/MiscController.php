<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MiscController extends Controller
{
    public function aboutAction()
    {
        return $this->render('SLNRegisterBundle:Misc:about.html.twig');
    }

    public function contactAction()
    {
        return $this->render('SLNRegisterBundle:Misc:contact.html.twig');
    }

}
