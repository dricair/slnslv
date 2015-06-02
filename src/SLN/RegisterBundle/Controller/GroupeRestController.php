<?php
// REST API for the groups

namespace SLN\RegisterBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Form\GroupeType;


class GroupeRestController extends Controller {
    public function getGroupAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $groupe = $em->getRepository('SLNRegisterBundle:Groupe')->find($id);

        if(!is_object($groupe)){
            throw $this->createNotFoundException();
        }

        return $groupe;
    }
}


