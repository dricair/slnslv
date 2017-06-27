<?php
/**
  * Payment controller class for REST api 
  *
  * Contains controller class to deal with payments/inscriptions, for REST api. 
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Entity\User;

/**
 * Controller for the Payment class to answer REST api.
 */
class PaymentRestController extends Controller {

    /**
     * Change the value of a missing inscription item to a licensee
     *
     * @param int $saison_id Id of the saison
     * @param int $id Id of the licensee
     * @param int $inscription Inscription item index
     * @param int $missing New missing value
     *
     * @return int New missing value
     */
    public function editLicenseeInscriptionMissingAction(Request $request, $id, $inscription, $missing) {
        if (!$this->getUser()->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas accéder cette page");
        }

        $em = $this->getDoctrine()->getManager();
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->getOpen();
        if (!$saison) 
              throw $this->createNotFoundException("Il n'y a pas de saison ouverte.");

        $licensee = $this->getLicenseeRepository()->find($id);
        if (!$licensee) {
            throw $this->createNotFoundException("Ce licencié n'existe pas.");
        }

        $saison_link = $licensee->getSaisonLink($saison);
        if (!$saison_link)
            throw $this->createNotFoundException("Cette saison n'existe pas pour ce licencié.");

        $missing = $saison_link->setInscriptionMissing($inscription, $missing);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($saison_link);
        $em->flush();

        return $missing;
    }

    /**
     * Change the value of missing payment for all user's licensees
     *
     * @param int $id User id
     * @param int $missing New missing value
     *
     * @return int New missing value
     */
    public function editUserPaymentMissingAction(Request $request, $id, $inscription, $missing) {
        if (!$this->getUser()->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas accéder cette page");
        }

        $em = $this->getDoctrine()->getManager();
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->getOpen();
        if (!$saison) 
              throw $this->createNotFoundException("Il n'y a pas de saison ouverte.");

        $user = $this->getUserRepository()->find($id);
        if (!$user) {
            throw $this->createNotFoundException("Cet utilisateur n'existe pas.");
        }

        $new_missing = FALSE;
        $em = $this->getDoctrine()->getManager();
        $licensees = $user->getLicensees();
        foreach ($licensees as &$licensee) {
            $saison_link = $licensee->getSaisonLink($saison);
            if ($saison_link) {
                $new_missing = $new_missing or $saison_link->setInscriptionMissing(LicenseeSaison::PAIEMENT, $missing);
                $em->persist($saison_link);
                $em->flush();
            }
        }

        return $new_missing;
    }


    /**
     * Get repository for the licensees
     *
     * @return LicenseeRepository Repository for Licensee instances.
     */
    protected function getLicenseeRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:Licensee');
    }
    /**
     * Get repository for the licensees
     *
     * @return LicenseeRepository Repository for Licensee instances.
     */
    protected function getUserRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:User');
    }
}

