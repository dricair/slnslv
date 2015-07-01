<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Form\LicenseeType;

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

        $licensee = $this->getLicenseeRepository()->find($id);

        if (!$licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        return $this->render('SLNRegisterBundle:Licensee:show.html.twig', array(
            'licensee' => $licensee,
        ));
    }

    /**
     * Form to create a new licensee or edit an existing one
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
          $em = $this->getDoctrine()->getEntityManager();
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
            $em = $this->getDoctrine()->getEntityManager();
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
      * Delete a licensee from a user
      */
    public function deleteAction($id, $admin=FALSE) {
        $licensee = $this->getLicenseeRepository()->find($id);
        $user = $this->getUserFromID($licensee->getUser()->getId());

        if (!$licensee instanceof Licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        $user->removeLicensee($licensee);

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($licensee);
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('SLNRegisterBundle_homepage'));
    }

    /** 
     * List licensee with optional filters and sorting
     */
    public function listAction($admin=FALSE) {
        $groupes = array();
        $total = 0;

        $no_group = $this->getLicenseeRepository()->getAllNoGroups();
        $total += count($no_group);

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
        }

        return $this->render('SLNRegisterBundle:Licensee:list.html.twig', array('no_group' => $no_group,
                                                                                'groupes' => $groupes, 
                                                                                'total' => $total, 
                                                                                'admin' => $admin));
    }

    /*
     * Inscription sheets
     */
    public function inscriptionAction($id, $admin=False) {
        $licensee = $this->getLicenseeRepository()->find($id);
        if (!$licensee instanceof Licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        $user = $this->getUserFromID($licensee->getId());

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
     * raise an exception
     */
    public function getUserFromID($user_id) {
        $em = $this->getDoctrine()
                   ->getEntityManager();

        $user = $em->getRepository('SLNRegisterBundle:User')->find($user_id);
        if (!$user) {
            throw $this->createNotFoundException("Cet utilisateur n'existe pas.");
        }

        $currentUser = $this->getUser();

        // Only permit access from this user, or a user which is Admin
        if ($user->getId() != $currentUser->getId() and !$currentUser->hasRole('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $user;
    }


    /**
     * Get repository for the licensees
     *
     * @return Repository
     */
    protected function getLicenseeRepository() {
        $em = $this->getDoctrine()
                   ->getEntityManager();
        return $em->getRepository('SLNRegisterBundle:Licensee');
    }
}
