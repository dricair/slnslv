<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use SLN\RegisterBundle\Entity\Enquiry;
use SLN\RegisterBundle\Form\EnquiryType;

class MiscController extends Controller
{
    public function aboutAction()
    {
        return $this->render('SLNRegisterBundle:Misc:about.html.twig');
    }

    public function contactAction()
    {
        $enquiry = new Enquiry();
        $user = $this->getUser();
        if ($user->hasRole('ROLE_USER')) {
            $enquiry->setName($user->getPrenom() . " " . $user->getNom());
            $enquiry->setEmail($user->getEmail());
        }

        $form = $this->createForm(new EnquiryType(), $enquiry);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                // Perform some action, such as sending an email
                $message = \Swift_Message::newInstance()
                  ->setSubject('Question du site d\'inscription')
                  ->setFrom('postmaster@stadelaurentinnatation.fr')
                  ->setTo($this->container->getParameter('sln_register.emails.contact_email'))
                  ->setCc('cairaud@gmail.com')
                  ->setBody($this->renderView('SLNRegisterBundle:Misc:contactEmail.txt.twig', array('enquiry' => $enquiry)));

                $this->get('mailer')->send($message);
                $this->get('session')->getFlashBag()->add(
                  'notice',
                  sprintf("Votre message '" . $enquiry->getSubject() . "' vient d'être envoyé.")
                );

                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                return $this->redirect($this->generateUrl('SLNRegisterBundle_contact'));
            }
        }

        return $this->render('SLNRegisterBundle:Misc:contact.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function testAction($id) {
      $message = \Swift_Message::newInstance()
        ->setSubject('Hello Email')
        ->setFrom('postmaster@stadelaurentinnatation.fr')
        ->setTo('cairaud@gmail.com')
        ->setBody($this->renderView('SLNRegisterBundle:Misc:test.text.twig', array('id' => $id)));
      $this->get('mailer')->send($message);

      return $this->render('SLNRegisterBundle:Misc:test.html.twig', array('message'=> $message));
    }

}
