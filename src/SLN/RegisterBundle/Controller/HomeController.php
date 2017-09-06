<?php
/**
  * Class for the Home controller. 
  *
  * Contains classes to live at the home page, containing most of the functions that are accessible
  * by a user.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Entity\Repository\LicenseeRepository;

/**
 * Home controller, accessible by the user.
 */
class HomeController extends Controller
{
    /**
     * Show a choice between login and register
     *
     * @return \Symfony\Component\HttpFoundation\Response Response for the function
     */
    public function indexAction()
    {
        $activeLicensees = NULL;
        $details = array();

        if (!$this->isLoggedIn()) {
            return $this->render('SLNRegisterBundle:Home:index_login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        $open_saison = $em->getRepository('SLNRegisterBundle:Saison')->getOpen();
        $currentUser = $this->getUser();

        $all_licensees = $em->getRepository('SLNRegisterBundle:Licensee')
                            ->getLicenseesForUser($currentUser->getId(), NULL);
        $active_licensees = array();
        $available_licensees = array();
        $no_licensee = count($all_licensees) == 0;

        $all_ok = TRUE;

        foreach ($all_licensees as &$licensee) {
            if ($licensee->getSaisonLink($open_saison)) {
                $active_licensees[] = $licensee;
                if ($licensee->inscriptionMissingNum($open_saison) != 0)
                    $all_ok = FALSE;
            } 

            else {
                $available_licensees[] = $licensee;
            }
        }


        if ($open_saison) {
            $currentUser->addExtraTarif($open_saison);
            $details = $currentUser->paymentInfo($open_saison);
        }

        return $this->render('SLNRegisterBundle:Home:index_show.html.twig', array('available_licensees' => $available_licensees,
                                                                                  'active_licensees' => $active_licensees,
                                                                                  'no_licensee' => $no_licensee,
                                                                                  'all_ok' => $all_ok,
                                                                                  'open_saison' => $open_saison,
                                                                                  'inscription_names' => LicenseeSaison::getInscriptionNames(),
                                                                                  'payment_val' => LicenseeSaison::PAIEMENT,
                                                                                  'payments_detail' => $details));
    }


    /**
     * Returns true if a user is logged in
     *
     * @return bool True if current user is logged in
     */
    public function isLoggedIn() {
        $securityContext = $this->container->get('security.context');
        return $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }
}
