<?php
/**
  * Licensee controller class. 
  *
  * Contains controller class to deal with groups. Mostly admin functions.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Form\LicenseeType;

use SLN\RegisterBundle\Entity\Repository\LicenseeRepository;

/**
 * Licensee controller.
 */
class LicenseeController extends Controller
{
    /**
     * Show a Licensee entry.
     *
     * @param int $id Id of the licensee to show.
     *
     * @return Response Rendered page.
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $licensee = $this->getLicenseeRepository()->find($id);

        if (!$licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        return $this->render('SLNRegisterBundle:Licensee:show.html.twig', array(
            'licensee' => $licensee,
        ));
    }

    /**
     * Form to create a new licensee or edit an existing one. Answers to GET and POST requests.
     *
     * @param int  $id           Id of the Licensee to edit. If 0, a new Licensee instead.
     * @param int  $user_id      Id of the User, used when a Licensee is created.
     * @param bool $inside_page  If true, the page is rendered inside another page, no header is generated
     * @param bool $admin        If true, the page is accessed with admin rights.
     *
     * @return Response Rendered page.
     */
    public function editAction($id, $user_id=0, $inside_page=FALSE, $admin=FALSE) {
        if ($id == 0) {
          $licensee = new Licensee();
          if ($user_id == 0 and !$admin)
              throw $this->createNotFoundException("Cet utilisateur n'existe pas.");

          if ($user_id != 0) {
              $user = $this->getUserFromID($user_id);
              $licensee->setUser($user);
          }
        } else {
          $em = $this->getDoctrine()->getManager();
          $licensee = $this->getLicenseeRepository()->find($id);
          $user = $this->getUserFromID($licensee->getUser()->getId());

          if (!$licensee) {
              throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
          }
        }

        $request = $this->getRequest();
        $form    = $this->createForm(new LicenseeType(), $licensee, array("admin" => $admin));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($licensee);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              sprintf("Le licencié '%s %s' a été %s avec succès.", $licensee->getPrenom(), $licensee->getNom(), $id = 0 ? "ajouté" : "modifié")
            );

            if ($admin) {
              return $this->redirect($this->generateUrl('SLNRegisterBundle_admin_licensee_edit', array(
                                     'id' => $licensee->getId(), $user_id => $licensee->getUser()->getId(),
                                     'admin' => $admin)
                                    ));
            } else {
              return $this->redirect($this->generateUrl('SLNRegisterBundle_homepage', array()));
            }
        }
 
        return $this->render($inside_page ? 'SLNRegisterBundle:Licensee:form.html.twig' :
                                            'SLNRegisterBundle:Licensee:edit.html.twig', array(
            'licensee' => $licensee,
            'form' => $form->createView(),
            'title' => $id == 0 ? "Ajouter un licencié" : "Editer le licencié \"{$licensee->getPrenom()} {$licensee->getNom()}\"",
            'id' => $id,
            'user_id' => $user_id,
            'admin' => $admin));
    }


    /**
      * Delete a licensee from a given user.
      * 
      * @param int  $id    Id of the licensee to delete.
      * @param bool $admin If true, the page is accessed with admin rights.
      *
      * @return Response Rendered page.
      */
    public function deleteAction($id, $admin=FALSE) {
        $licensee = $this->getLicenseeRepository()->find($id);

        if (!$licensee instanceof Licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        $user = $this->getUserFromID($licensee->getUser()->getId());
        $user->removeLicensee($licensee);

        $em = $this->getDoctrine()->getManager();
        $em->remove($licensee);
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('SLNRegisterBundle_homepage'));
    }

    /** 
     * List licensee with optional filters and sorting.
     * 
     * @param bool $admin If True, the page is accessed with admin rights
     *
     * @return Response Rendered page.
     */
    public function listAction($admin=FALSE) {
        $groupes = array();
        $officiels = array();
        $bureau = array();
        $total = 0;

        $no_group = $this->getLicenseeRepository()->getAllNoGroups();
        $total += count($no_group);

        foreach ($no_group as $key => $licensee) {
            $remove = false;
            if ($licensee->getOfficiel() || $licensee->getBureau())
                unset($no_group[$key]);

            if ($licensee->getOfficiel()) $officiels[] = $licensee;
            if ($licensee->getBureau()) $bureau[] = $licensee;
        }

        $licensees = $this->getLicenseeRepository()->getAllByGroups();

        foreach($licensees as $licensee) {
            if ($licensee->getGroupe() == Null)
                $groupe = "Pas de groupe";
            else
                $groupe = $licensee->getGroupe()->getNom();

            if (!array_key_exists($groupe, $groupes)) {
                $groupes[$groupe] = array("num" => 0, "licensees" => array());
            }

            $groupes[$groupe]["num"] += 1;
            $groupes[$groupe]["licensees"][] = $licensee;
            $total += 1;

            if ($licensee->getOfficiel()) $officiels[] = $licensee;
            if ($licensee->getBureau()) $bureau[] = $licensee;
        }

        return $this->render('SLNRegisterBundle:Licensee:list.html.twig', array('no_group' => $no_group,
                                                                                'groupes' => $groupes, 
                                                                                'officiels' => $officiels,
                                                                                'bureau' => $bureau,
                                                                                'total' => $total, 
                                                                                'admin' => $admin));
    }

    /**
     * Inscription sheets.
     *
     * Render a PDF file containing the inscription sheet for the Licensee, and return a HTML
     * page to download the file.
     *
     * @param int  $id    Id of the Licensee
     * @param bool $admin True if the page is accessed with admin rights.
     *
     * @return Response Rendered page
     */
    public function inscriptionAction($id, $admin=False) {
        $licensee = $this->getLicenseeRepository()->find($id);
        if (!$licensee instanceof Licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        $user = $this->getUserFromID($licensee->getUser()->getId());

        $pdf = $this->container->get("white_october.tcpdf")->create();
        $assets = $this->container->get('templating.helper.assets');

        $title = "Feuilles d'inscriptions - {$licensee->getPrenom()} {$licensee->getNom()}";

        // For your people and no groupe selected, attach a default group (Not saved)
        if ($licensee->getGroupe() == Null and $licensee->getAge() < 12) {
            $groupe = new Groupe();
            $groupe->setNom("<Inconnu>");
            $licensee->setGroupe(new Groupe());
        }

        if ($licensee->getGroupe() != Null)
            $licensee->inscriptionSheet($pdf, $assets, $title);

        $response = new Response($pdf->Output('inscriptions.pdf', 'I'));
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }


    /**
     * Get user from ID. If user is not current ID or a user with staff role, 
     * raise an exception.
     *
     * @param int $user_id Id of the User.
     *
     * @return User User instance with the given ID.
     *
     * @throws AccessDeniedException if the current user does not match, and current user does not have admin rights.
     */
    public function getUserFromID($user_id) {
        $em = $this->getDoctrine()
                   ->getManager();

        $user = $em->getRepository('SLNRegisterBundle:User')->find($user_id);
        if (!$user) {
            throw $this->createNotFoundException("Cet utilisateur n'existe pas.");
        }

        $currentUser = $this->getUser();

        // Only permit access from this user, or a user which is Admin
        if ($user->getId() != $currentUser->getId() and !$currentUser->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas accéder cette page");
        }

        return $user;
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
