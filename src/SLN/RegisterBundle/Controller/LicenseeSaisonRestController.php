<?php
/**
  * New groupe control for REST api 
  *
  * Change newGroupe field in a LicenseeSaison link
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Form\Type\LicenseeSaisonNewGroupeType;

/**
 * Controller for the LicenseeSaison class to answer REST api.
 */
class LicenseeSaisonRestController extends Controller {
    /**
     * Change the newGroupe field of the LicenseeSaison class
     *
     * @param Request $reques: request containing the POST data
     * @param int $id: Id of the saison_link
     *
     * @return Response
     */
    public function postNewGroupAction(Request $request, $id) {
        if (!$this->getUser()->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas accéder cette page");
        }

        $em = $this->getDoctrine()->getManager();
        $saison_link = $em->getRepository("SLNRegisterBundle:LicenseeSaison")->find($id);
        
        if (!$saison_link) {
            throw $this->createNotFoundException("Ce lien de saison n'existe pas.");
        }

        $data = json_decode($request->getContent(), true);

        $groupe = $em->getRepository("SLNRegisterBundle:Groupe")->find($data['new_groupe']);
        if (!$groupe) {
            throw $this->createNotFoundException("Ce groupe n'existe pas.");
        }

        $saison_link->setNewGroupe($groupe);
        $em->persist($saison_link);
        $em->flush();

        $licensee = $saison_link->getLicensee();

        $to = array($licensee->getUser()->getEmail());
        if ($licensee->getUser()->getSecondaryEmail())
            $to[] = $licensee->getUser()->getSecondaryEmail();

        $message = \Swift_Message::newInstance()
         ->setSubject("Changement de groupe pour {$licensee->getPrenom()} {$licensee->getNom()}")
         ->setFrom(array('slnslv@free.fr' => "Stade Laurentin Natation"))
         ->setTo($to)
         ->setCc(array('slnslv@free.fr' => "Stade Laurentin Natation"))
         ->setBody($this->renderView('SLNRegisterBundle:Licensee:changeGroupe.txt.twig', 
                                     array('licensee' => $licensee, 'groupe' => $groupe)), "text/plain")
         ->addPart($this->renderView('SLNRegisterBundle:Licensee:changeGroupe.html.twig', 
                                     array('licensee' => $licensee, 'groupe' => $groupe)), "text/html");

        $this->get('mailer')->send($message);

        return new JsonResponse(array("status" => "ok"), 200);
    }
}


