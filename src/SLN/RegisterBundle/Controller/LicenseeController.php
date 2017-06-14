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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\TwigBundle\Extension;

use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Entity\Horaire;

use SLN\RegisterBundle\Form\LicenseeType;
use SLN\RegisterBundle\Form\Type\LicenseeSaisonNewGroupeType;

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
     * @param int  $saison_id    Id of the saison to edit. If 0, current open saison.
     * @param int  $user_id      Id of the User, used when a Licensee is created.
     * @param bool $inside_page  If true, the page is rendered inside another page, no header is generated
     * @param bool $admin        If true, the page is accessed with admin rights.
     *
     * @return Response Rendered page.
     */
    public function editAction($id, $saison_id, $user_id=0, $inside_page=FALSE, $admin=FALSE) {
        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->getUser();
        if ($saison_id != 0 and !$currentUser->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas exécuter cette action.");
        }

        $saison = $em->getRepository('SLNRegisterBundle:Saison')->findOrOpen($saison_id);

        if (!$saison) 
              throw $this->createNotFoundException("Cette saison n'existe pas.");

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
            
        $previousGroupe = $licensee->getGroupe($saison);

        $form_saison_link = $licensee->getSaisonLink($saison);
        if (!$form_saison_link) {
            $form_saison_link = new LicenseeSaison();
            $form_saison_link->setLicensee($licensee);
            $form_saison_link->setSaison($saison);
            $form_saison_link->setGroupe($previousGroupe);
            $form_saison_link->setGroupeJours(array());

            $newGroupe = $licensee->getNewGroupe($saison);
            if ($new_groupe)
                $form_saison_link->setGroupe($newGroupe);
        }
        $licensee->setFormSaisonLink($form_saison_link);

        $request     = $this->getRequest();
        $form        = $this->createForm(LicenseeType::class, $licensee, array("admin" => $admin));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($licensee);
            $em->persist($licensee->getFormSaisonLink());
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              sprintf("Le licencié '%s %s' a été %s avec succès.", $licensee->getPrenom(), $licensee->getNom(), $id == 0 ? "ajouté" : "modifié")
            );

            if ($admin) {
              // Send a mail to the user if group has changed
              $groupe = $licensee->getGroupe($saison);
              if ($id != 0 and $groupe and ($previousGroupe != null and
                                            $groupe->getId() != $previousGroupe->getId())) {
                
                $message = \Swift_Message::newInstance()
                 ->setSubject("Changement de groupe pour {$licensee->getPrenom()} {$licensee->getNom()}")
                 ->setFrom('slnslv@free.fr')
                 ->setTo($user->getEmail())
                 ->setCc('cairaud@gmail.com')
                 ->setBody($this->renderView('SLNRegisterBundle:Licensee:changeGroupe.txt.twig', array('licensee' => $licensee)), "text/plain")
                 ->addPart($this->renderView('SLNRegisterBundle:Licensee:changeGroupe.html.twig', array('licensee' => $licensee)), "text/html");

                $this->get('mailer')->send($message);
              }

              return $this->redirect($this->generateUrl('SLNRegisterBundle_admin_licensee_edit', array(
                                     'id' => $licensee->getId(), $user_id => $licensee->getUser()->getId(),
                                     'saison_id' => $saison->getId(),
                                     'admin' => $admin)
                                    ));
            } else {
              return $this->redirect($this->generateUrl('SLNRegisterBundle_homepage', array()));
            }
        }

        else if ($request->getMethod() == 'POST') {
            $this->get('session')->getFlashBag()->add('error', "Des erreurs sont présentes dans le formulaire");
        }

        return $this->render($inside_page ? 'SLNRegisterBundle:Licensee:form.html.twig' :
                                            'SLNRegisterBundle:Licensee:edit.html.twig', array(
            'licensee' => $licensee,
            'form' => $form->createView(),
            'title' => $id == 0 ? "Ajouter un licencié" : "Editer le licencié \"{$licensee->getPrenom()} {$licensee->getNom()}\"",
            'id' => $id,
            'user_id' => $user_id,
            'saison' => $saison,
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
    public function deleteAction($id, $saison_id, $admin=FALSE) {
        $em = $this->getDoctrine()->getManager();
        $licensee = $this->getLicenseeRepository()->find($id);

        if (!$licensee instanceof Licensee) {
            throw $this->createNotFoundException('Ce licencié n\'existe pas dans la base de données.');
        }

        if ($saison_id != 0 and !$currentUser->hasRole('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas exécuter cette action.");
        }

        $user = $this->getUserFromID($licensee->getUser()->getId());
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->findOrOpen($saison_id);

        $saison_link = $licensee->getSaisonLink($saison);
        $em->remove($saison_link);
        $em->flush();

        return $this->redirectToPrevPage();
    }

    /** 
     * List licensee with optional filters and sorting.
     * 
     * @param int $saison_id Id of the saison to look at
     * @param bool $admin If True, the page is accessed with admin rights
     *
     * @return Response Rendered page.
     */
    public function listAction($saison_id, $admin=FALSE) {
        if (!$admin) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas accéder cette page");
        }

        $em = $this->getDoctrine()->getManager();
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->findOrCurrent($saison_id);
        if (!$saison) {
            throw $this->createNotFoundException("Cette saison n'existe pas.");
        }

        $fonctions = array();
        foreach(Licensee::getFonctionNames() as $fonction)
            $fonctions[$fonction] = array();

        $no_group = $this->getLicenseeRepository()->getAllNoGroups($saison);
        $total = count($no_group);

        foreach ($no_group as $key => $licensee) {
            $licensee_fonctions = $licensee->getFonctions();
            foreach(Licensee::getFonctionNames() as $index => $fonction) {
                if ($licensee_fonctions and in_array($index, $licensee_fonctions))
                    $fonctions[$fonction][] = $licensee;
            }

            if (count($licensee_fonctions) > 0)
                unset($no_group[$key]);
        }

        $licensees = $this->getLicenseeRepository()->getAllByGroups($saison);
        $total += count($licensees);

        foreach($licensees as $licensee) {
            $licensee_fonctions = $licensee->getFonctions();
            foreach(Licensee::getFonctionNames() as $index => $fonction) {
                if ($licensee_fonctions and in_array($index, $licensee_fonctions))
                    $fonctions[$fonction][] = $licensee;
            }
        }

        $groupes = Licensee::sortByGroups($licensees, $saison);

        return $this->render('SLNRegisterBundle:Licensee:list.html.twig', array('no_group' => $no_group,
                                                                                'groupes' => $groupes, 
                                                                                'fonctions' => $fonctions,
                                                                                'saison' => $saison,
                                                                                'total' => $total, 
                                                                                'admin' => $admin));
    }


    /**
     * Update newGroupe field of the licensees in a single page (Using Ajax)
     * @param int $saison_id Id of the saison to look at
     *
     * @return Response Rendered page.
     */
    public function newGroupeAction($saison_id) {
        $em = $this->getDoctrine()->getManager();
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->findOrCurrent($saison_id);
        if (!$saison) {
            throw $this->createNotFoundException("Cette saison n'existe pas.");
        }

        $licensees = $this->getLicenseeRepository()->getAllByGroups($saison);

        $forms = array();
        foreach ($licensees as &$licensee) {
            $saison_link = $licensee->getSaisonLink($saison);
            $forms[$licensee->getId()] = $this->createForm(LicenseeSaisonNewGroupeType::class, $saison_link)->createView();
        }

        return $this->render('SLNRegisterBundle:Licensee:list_group.html.twig', array('saison' => $saison,
                                                                                      'forms' => $forms,
                                                                                      'licensees' => $licensees));
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

    private function groupeNom($groupe, $day=-1) {
        if ($day == -1) return $groupe->getNom();
        else {
            $days = Horaire::getJours();
            return $days[$day] . " - " . $groupe->getNom();
        }
    }

    /**
     * Export the list of licensees to an Excel file
     *
     * Return an excel file to download.
     */
    public function exportAction() {
        $licensees = array_merge($this->getLicenseeRepository()->getAllByGroups(),
                                 $this->getLicenseeRepository()->getAllNoGroups());
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Cédric Airaud")
           ->setTitle("Liste des licenciés")
           ->setSubject("Liste des licenciés du club");

        $data = array(array("Nom", "Prénom", "Sexe", "Catégorie", "Date naissance", "IUF", "Responsable légal", "Ville", "Tél. fixe", "Tél portable", "Email"));
        $gdata = array(array("Nom", "Prénom", "Sexe", "Date naissance", "Inscription"));
        $group_data = array();
        $sheet = 0;

        foreach($licensees as &$licensee) {
          $user = $licensee->getUser();
          $groupe = $licensee->getGroupe();
          if ($groupe == NULL) continue;

          $days = array(-1);
          if ($groupe->getMultiple()) $days = $groupe->multipleList();
          if ($days === null) $days = array(-1);
          foreach($days as $day) {
              $gnom = $this->groupeNom($groupe, $day);
              if (!array_key_exists($gnom, $group_data)) {
                  $group_data[$gnom] = array($gdata[0]);
              }
          }

          $data[] = array($licensee->getNom(), 
                          $licensee->getPrenom(),
                          $licensee->getSexeName(),
                          $groupe->getNom(),
                          $licensee->getNaissance()->format("d/m/Y"),
                          $licensee->getIUF(),
                          sprintf("%s %s %s", $user->getTitreName(), $user->getNom(), $user->getPrenom()),
                          $user->getVille(),
                          $user->getTelDomicile(),
                          $user->getTelPortable(),
                          $user->getEmail());

          $missing = "Complet";
          if ($licensee->inscriptionMissingNum() > 0)
              $missing = $licensee->inscriptionMissingString();
          $gdata[1] = array($licensee->getNom(), 
                            $licensee->getPrenom(),
                            $licensee->getSexeName(),
                            $licensee->getNaissance()->format("d/m/Y"),
                            $missing);

          $days = array(-1);
          if ($groupe->getMultiple()) $days = $licensee->getGroupeJours();
          foreach ($days as $day) {
            $group_data[$this->groupeNom($groupe, $day)][] = $gdata[1];
          }
        }

        foreach($group_data as $gnom => $gdata) {
            $group_sheet = new \PHPExcel_Worksheet($phpExcelObject, substr($gnom,0,30));
            $phpExcelObject->addSheet($group_sheet, $sheet+1);
            $group_sheet->fromArray($gdata, NULL, 'A1');
            $sheet += 1;
        }

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();
        $activeSheet->setTitle('Liste globale');
        $activeSheet->fromArray($data, NULL, 'A1');

        // Create the response
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'ListeLicencies.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

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


    /**
     * Return a rediction to the previous page
     *
     * @return Redirect
     */
    protected function redirectToPrevPage() {
        $referer = $this->getRequest()->headers->get('referer', $this->generateUrl('SLNRegisterBundle_homepage'));
        return $this->redirect($referer);
    }
}
