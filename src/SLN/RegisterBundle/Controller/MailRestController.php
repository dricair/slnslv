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

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\User;

/**
 * Controller for the Mail class to answer REST api.
 */
class MailRestController extends Controller {
    /**
     * Number of licensees to send before giving an answer
     */
    const USE_SENDGRID = true;
    const MAX_SENT = self::USE_SENDGRID ? 5 : 1;

    /**
     * Send the emails that are in the current session
     *
     * @return array Status and fails.
     */
    public function getEmailAction(Request $request) {
        if (!$this->getUser()->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas accéder cette page");
        }

        // Development version: force a destination address
        $delivery_address = null;
        if ($this->container->hasParameter('swiftmailer.single_address') && $this->container->getParameter('swiftmailer.single_address')) {
            $delivery_address = $this->container->getParameter('swiftmailer.single_address');
        }

        // Get session information
        $session = $request->getSession();

        if (!$session->has('mail/licensees')) {
            return array("result" => "fatal", "sent" => 0, "error" => "Problème pour la récupération des données.");
        }

        $licensee_list = $session->get('mail/licensees');
        $title = $session->get('mail/title');
        $body = $session->get('mail/body');
        $text_body = $session->get('mail/text_body');
        $text_title = $session->get('mail/text_title');
        $from = $session->get('mail/from');

        // Pop licensees from the list and send a mail
        $failures = array();

        // Assets for attached files
        $assets = $this->container->get('templating.helper.assets');

        $sent = 0;
        for ($i = 0; $i < self::MAX_SENT and count($licensee_list) > 0; $i++) {
            $licensee_id = array_pop($licensee_list);
            $licensee = $this->getLicenseeRepository()->find($licensee_id);

            if (!is_object($licensee)) {
                $failures[] = "Le licencié {$licensee_id} n'existe pas";
            } else {
              if (self::USE_SENDGRID) {
                $sendgrid = new \SendGrid($this->container->getParameter('mailer_key'));
                $email = new \SendGrid\Email();
                $email->addTo($delivery_address ? $delivery_address : $licensee->getUser()->getEmail())
                      ->setFrom('slnslv@free.fr')
                      ->setSubject($text_title)
                      ->setHtml($this->renderView('SLNRegisterBundle:Mail:mail_content.html.twig', 
                                                  array('licensee' => $licensee, 'title_value' => $title, 'body_value' => $body)))
                      ->setText($this->renderView('SLNRegisterBundle:Mail:mail_content.txt.twig', 
                                                  array('licensee' => $licensee, 'title_value' => $title, 'body_value' => $text_body)));

                if (false) 
                    $email->addAttachment("/docs/Cedric/Programmation/PHP/slnslv/web/uploads/Suivi individuel - {$licensee->getNom()} {$licensee->getPrenom()}.pdf");

                $sendgrid->send($email);
              } else {
                $message = \Swift_Message::newInstance()
                 ->setSubject($text_title)
                 ->setFrom('slnslv@free.fr')
                 ->setTo($licensee->getUser()->getEmail())
                 ->setBody($this->renderView('SLNRegisterBundle:Mail:mail_content.txt.twig', 
                                             array('licensee' => $licensee, 'title_value' => $title, 'body_value' => $text_body)), "text/plain")
                 ->addPart($this->renderView('SLNRegisterBundle:Mail:mail_content.html.twig', 
                                             array('licensee' => $licensee, 'title_value' => $title, 'body_value' => $body)), "text/html");
  
                $fails = array();
                $this->get('mailer')->send($message, $fails);
  
                if (count($fails) > 0) {
                    $s = "";
                    foreach ($fails as $f) { $s += "$f "; }
                    $failures[] = "$licensee: erreur d'envoi pour les adresses: $s";
                }
              }
            }

            $session->set("mail/licensees", $licensee_list);
            $sent += 1;
        }

        if (count($licensee_list) == 0) {
            $session->remove('mail/licensees');
        }
    
        return array("result" => count($licensee_list) > 0 ? "ok" : "done", "sent" => $sent, "failures" => $failures);
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
}



