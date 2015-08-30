<?php
/**
  * Mail controller class. 
  *
  * Contains controller class to deal with mail, to users or groups
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\LicenseeSelect;
use SLN\RegisterBundle\Form\LicenseeSelectType;

/**
 * Mail controller.
 */
class MailController extends Controller {
    /**
     * Send a mail to a licensee or list of licensees, with facilities to chose them
     *
     * @param int $default Licensee selected by default, none if Null
     */
    public function licenseeAction($default=null) {
        $title = "Envoi de mails";

        $defaultGroup = null;
        $em = $this->getDoctrine()->getEntityManager();

        if ($default)
          $defaultGroup = $em->getRepository('SLNRegisterBundle:Groupe')->find($default);
        
        $select = new LicenseeSelect();
        $form = $this->createForm(new LicenseeSelectType(), $select, array("em" => $em, "defaultGroup" => $defaultGroup));

        $request = $this->getRequest();
        $form->handleRequest($request);

        return $this->render('SLNRegisterBundle:Mail:edit.html.twig', array('form' => $form->createView(), 'title' => $title ));
    }
}



