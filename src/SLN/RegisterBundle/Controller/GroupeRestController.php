<?php
/**
  * Group controller class for REST api 
  *
  * Contains controller class to deal with groups, for REST api. 
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Form\GroupeType;

/**
 * Controller for the Groupe class to answer REST api.
 */
class GroupeRestController extends Controller {

    /**
     * Get the description for a Groupe. Return the Groupe itself.
     *
     * @param int $id Id for the Groupe
     * @return Groupe Requested groupe.
     */
    public function getGroupAction($id){
        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('SLNRegisterBundle:Groupe')->find($id);

        if(!is_object($groupe)){
            throw $this->createNotFoundException();
        }

        // Use the Expose and VirtualProperty of the Groupe object to serialize
        return $groupe;
    }
}


