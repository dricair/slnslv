<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Licensee controller.
 */
class LicenseeController extends Controller
{
    /**
     * Show a Licensee entry
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $licensee = $em->getRepository('SLNRegisterBundle:Licensee')->find($id);

        if (!$licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        return $this->render('SLNRegisterBundle:Licensee:show.html.twig', array(
            'licensee'      => $licensee,
        ));
    }
}
