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
        $year = date('Y');
        $month = date('n');
        if ($month < 5) $year = $year - 1;
        $details = null;

        if ($this->isLoggedIn()) {
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()
                       ->getManager();

            $activeLicensees = $em->getRepository('SLNRegisterBundle:Licensee')
                               ->getLicenseesForUser($currentUser->getId());

            $currentUser->addExtraTarif();
            $details = $currentUser->paymentInfo();

        }

        return $this->render('SLNRegisterBundle:Home:index.html.twig', array('activeLicensees' => $activeLicensees,
                                                                             'year' => $year,
                                                                             'inscription_names' => Licensee::getInscriptionNames(),
                                                                             'payment_val' => Licensee::PAIEMENT,
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
