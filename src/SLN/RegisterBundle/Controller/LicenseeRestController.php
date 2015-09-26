<?php
/**
  * Licensee controller class for REST api 
  *
  * Contains controller class to deal with licensees, for REST api. 
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;

/**
 * Controller for the Licensee class to answer REST api.
 */
class LicenseeRestController extends Controller {

    /**
     * Get the list of licensees that are part of a group
     *
     * @param int $id Id for the Groupe
     * @return Licensee[] List of licensees
     */
    public function getLicenseesInGroupAction(Request $request, $id){
        if (!$this->getUser()->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas accéder cette page");
        }

        $em = $this->getDoctrine()->getManager();

        $groupe = $em->getRepository('SLNRegisterBundle:Groupe')->find($id);
        if(!is_object($groupe)){
            throw $this->createNotFoundException();
        }

        $licensees = [];
        foreach($em->getRepository('SLNRegisterBundle:Licensee')->getAllForGroupe($groupe) as $licensee) 
            $licensees[] = array("id" => $licensee->getId(), "name" => $licensee->getPrenom() . " " . $licensee->getNom());
        
        return $licensees;
    }
}



