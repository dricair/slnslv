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

        $extra = -1;
        if (strpos($id, ".") !== false) {
            $val = explode(".", $id);
            $id = $val[0];
            $extra = intval($val[1]);
        }

        $licenseeRepository = $em->getRepository('SLNRegisterBundle:Licensee');
        $groupeRepository = $em->getRepository('SLNRegisterBundle:Groupe');

        if ($id >= Licensee::FONCTIONS_OFFSET) {
            // Special functions use a group index for the lookup
            $id = $id - Licensee::FONCTIONS_OFFSET;
            $fonctions = Licensee::getFonctionNames();
            if (!array_key_exists($id, $fonctions))
                throw $this->createNotFoundException("La fonction $id n'existe pas");

            $licensees = [];
            foreach($licenseeRepository->getAllForFonction($id) as $licensee) {
                if ($id != Licensee::OFFICIEL or $extra == -1 or $licenseeRepository->userHasInGroup($licensee, $extra))
                    $licensees[] = array("id" => $licensee->getId(), "name" => $licensee->getPrenom() . " " . $licensee->getNom());
            }
        }

        else {
            $groupe = $groupeRepository->find($id);
            if(!is_object($groupe)){
                throw $this->createNotFoundException("Le groupe $id n'existe pas");
            }

            $licensees = [];
            foreach($licenseeRepository->getAllForGroupe($groupe) as $licensee) {
                if ($extra == -1 or !$groupe->getMultiple() or in_array($extra, $licensee->getGroupeJours()))
                    $licensees[] = array("id" => $licensee->getId(), "name" => $licensee->getPrenom() . " " . $licensee->getNom());
            }
            
        }

        return $licensees;
    }
}



