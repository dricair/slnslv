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
use SLN\RegisterBundle\Entity\User;

/**
 * Controller for the Payment class to answer REST api.
 */
class PaymentRestController extends Controller {

    /**
     * Change the value of a missing inscription item to a licensee
     *
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

        $licensee = $this->getLicenseeRepository()->find($id);
        if (!$licensee) {
            throw $this->createNotFoundException("Ce licencié n'existe pas.");
        }

        $missing = $licensee->setInscriptionMissing($inscription, $missing);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($licensee);
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

        $user = $this->getUserRepository()->find($id);
        if (!$user) {
            throw $this->createNotFoundException("Cet utilisateur n'existe pas.");
        }

        $new_missing = FALSE;
        $em = $this->getDoctrine()->getManager();
        $licensees = $user->getLicensees();
        foreach ($licensees as &$licensee) {
            $new_missing = $new_missing or $licensee->setInscriptionMissing(Licensee::PAIEMENT, $missing);
            $em->persist($licensee);
            $em->flush();
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

