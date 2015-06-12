<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use SLN\RegisterBundle\Entity\Repository\LicenseeRepository;

/**
 * Home controller.
 */
class HomeController extends Controller
{
    /**
     * Show a choice between login and register
     */
    public function indexAction()
    {
        $activeLicensees = NULL;
        $year = date('Y');
        $month = date('n');
        if ($month < 5) $year = $year - 1;

        if ($this->isLoggedIn()) {
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()
                       ->getEntityManager();

            $activeLicensees = $em->getRepository('SLNRegisterBundle:Licensee')
                               ->getLicenseesForUser($currentUser->getId());

        }

        return $this->render('SLNRegisterBundle:Home:index.html.twig', array('activeLicensees' => $activeLicensees,
                                                                             'year' => $year));
    }


    /**
     * Returns true if a user is logged in
     */
    public function isLoggedIn() {
        $securityContext = $this->container->get('security.context');
        return $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }
}
