<?php
/**
  * Misc controller class. 
  *
  * Contains controller class to deal with various functions.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\Enquiry;
use SLN\RegisterBundle\Form\EnquiryType;

/**
 * Controller class for misc functions
 */
class MiscController extends Controller
{
    /**
     * About action: render a page for the About page.
     *
     * @return Response Rendered page.
     */
    public function aboutAction()
    {
        return $this->render('SLNRegisterBundle:Misc:about.html.twig');
    }

    
    /**
     * Contact action: render a page for contact the webmaster.
     *
     * When the form is validated, send an email to the webmaster.
     *
     * @return Response Rendered form.
     * @see Enquiry Internally uses Enquiry class.
     */
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
                  ->setFrom('slnslv@free.fr')
                  ->setTo($this->container->getParameter('sln_register.emails.contact_email'))
                  ->setCc('cairaud@gmail.com')
                  ->setReplyTo($enquiry->getEmail())
                  ->setBody($this->renderView('SLNRegisterBundle:Misc:contactEmail.txt.twig', array('enquiry' => $enquiry)));

                $this->get('mailer')->send($message);
                $this->get('session')->getFlashBag()->add(
                  'notice',
                  sprintf("Votre message '" . $enquiry->getSubject() . "' vient d'être envoyé.")
                );

                return $this->redirect($this->generateUrl('SLNRegisterBundle_contact'));
            }
        }

        return $this->render('SLNRegisterBundle:Misc:contact.html.twig', array(
            'form' => $form->createView()
        ));
    }


    /**
     * Private test function, normally not available
     *
     * @param int $id Integer parameter.
     *
     * @return Response Rendered response
     */
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
