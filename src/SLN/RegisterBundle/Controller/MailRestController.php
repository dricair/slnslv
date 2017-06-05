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
use SLN\RegisterBundle\Entity\LicenseeMail;

/**
 * Controller for the Mail class to answer REST api.
 */
class MailRestController extends Controller {
    /**
     * Number of licensees to send before giving an answer
     */
    const USE_SENDGRID = true;
    const MAX_SENT = 5; //self::USE_SENDGRID ? 5 : 1;

    /**
     * Send the emails that are in the current session
     *
     * @return array Status and fails.
     */
    public function getEmailAction(Request $request, $id) {
        if (!$this->getUser()->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas accéder cette page");
        }

        $mail = $this->getLicenseeMailRepository()->find($id);

        if (!$mail) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        // Development version: force a destination address
        $delivery_address = null;
        if ($this->container->hasParameter('swiftmailer.disable_delivery') && $this->container->getParameter('swiftmailer.disable_delivery')) {
            $delivery_address = $this->container->getParameter('swiftmailer.delivery_address');
        }

        // Get session information
        $session = $request->getSession();

        if (!$session->has('mail/id') or $session->get('mail/id') != $mail->getId()) {
            // Initial call - Fill licensee list and field to the session
            $session->set('mail/id', $mail->getId());
            $session->set('mail/licensees', $mail->getLicensees()->toArray());
            $session->set('mail/title', $mail->getTitle());
            $session->set('mail/body', $mail->getBody());
            $session->set('mail/text_body', $mail->getBodyAsText());
            $session->set('mail/text_title', $mail->getTitleAsText());
        }

        $licensee_list = $session->get('mail/licensees');
        $title = $session->get('mail/title');
        $body = $session->get('mail/body');
        $text_body = $session->get('mail/text_body');
        $text_title = $session->get('mail/text_title');

        // Pop licensees from the list and send a mail
        $failures = array();

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

                foreach ($mail->getFiles() as $uploadFile) {
                    if (!$uploadFile->getInline())
                        $email->addAttachment($uploadFile->getFile()->getRealPath(), $uploadFile->getFilename());
                }

                $sendgrid->send($email);

                if ($licensee->getUser()->getSecondaryEmail()) {
                    $email->setTos(array($delivery_address ? $delivery_address : $licensee->getUser()->getSecondaryEmail()));
                    if ($delivery_address)
                        $email->subject .= " (Dest: " . $licensee->getUser()->getSecondaryEmail() . ")";
                    $sendgrid->send($email);
                }

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
            $session->remove('mail/id');
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


    /**
     * Get repository for the mails
     *
     * @return LicenseeMailRepository Repository for LicenseeMail instances.
     */
    protected function getLicenseeMailRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:LicenseeMail');
    }
}



