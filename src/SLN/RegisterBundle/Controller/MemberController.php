<?php
/**
  * Member controller class. 
  *
  * Contains controller class to deal with members. Mostly admin functions.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Form\Type\UserType;

use SLN\RegisterBundle\Entity\Repository\UserRepository;
use SLN\RegisterBundle\Entity\Repository\LicenseeRepository;

/**
 * Member controller.
 */
class MemberController extends Controller
{
    /**
     * List the members.
     *
     * @param bool $admin If True, page is accessed with admin rights.
     *
     * @return Response Rendered page.
     */
    public function listAction($admin=False)
    {
        $members = $this->getUserRepository()->getAll();
        $em = $this->getDoctrine()->getManager();
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->getCurrent();

        return $this->render('SLNRegisterBundle:Member:list.html.twig', array('members' => $members,
                                                                              'saison' => $saison));
    }

    /**
     * Form to create a new member or edit an existing one (From admin)
     *
     * @param int  $id    Id of the User to edit.
     * @param bool $admin If True, the page is accessed with admin rights.
     * 
     * @return Response Rendered page
     */
    public function editAction($id, $admin=false) {
        if ($id == 0) {
          $user = new User();
        } else {
          $user = $this->getUserRepository()->find($id);

          if (!$user) {
              throw $this->createNotFoundException('Ce membre n\'existe pas dans la base de données.');
          }
        }

        $request = $this->getRequest();
        $form    = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Password not set for new user
            if ($admin && $id == 0) {
                $tokenGenerator = $this->get('fos_user.util.token_generator');
                $user->setPlainPassword(substr($tokenGenerator->generateToken(), 0, 8));

                $message = \Swift_Message::newInstance()
                 ->setSubject("Bienvenue {$user->getPrenom()}")
                 ->setFrom('slnslv@free.fr')
                 ->setTo($user->getEmail())
                 ->setBody($this->renderView('SLNRegisterBundle:Member:createAccount.txt.twig', array('user' => $user)));

                $this->get('mailer')->send($message);
            }
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              sprintf("Le membre '%s %s' a été %s avec succès.", $user->getPrenom(), $user->getNom(), $id == 0 ? "ajouté" : "modifié")
            );

            return $this->redirect($this->generateUrl('SLNRegisterBundle_member_edit', array('id' => $user->getId())));
        }

        else if ($request->getMethod() == 'POST') {
            $this->get('session')->getFlashBag()->add('error', "Des erreurs sont présentes dans le formulaire");
        }


        $open_licensees = null;
        $current_licensees = null;
        $current_saison = null;
        $open_saison = null;
        if ($admin) {
            $em = $this->getDoctrine()->getManager();
            $current_saison = $em->getRepository('SLNRegisterBundle:Saison')->getCurrent();
            $open_saison = $em->getRepository('SLNRegisterBundle:Saison')->getOpen();

            $current_licensees = $this->getLicenseeRepository()->getLicenseesForUser($user->getId(), $current_saison);
            $open_licensees = $this->getLicenseeRepository()->getLicenseesForUser($user->getId(), $open_saison);
        }
 
        return $this->render('SLNRegisterBundle:Member:edit.html.twig', array(
            'member' => $user,
            'form' => $form->createView(),
            'title' => $id == 0 ? "Ajouter un membre" : "Modifier un membre",
            'id' => $id,
            'admin' => $admin,
            'current_licensees' => $current_licensees,
            'open_licensees' => $open_licensees,
            'current_saison' => $current_saison,
            'open_saison' => $open_saison));
    }

    /**
     * Inscription sheets.
     *
     * Generate a HTML page to download a PDF contaning the inscription sheets for Licensee of the given User.
     *
     * @param int $user_id Id of the User 
     *
     * @return Response Rendered page.
     */
    public function inscriptionsAction($user_id) {
        $user = $this->getUserFromID($user_id);

        return $this->render('SLNRegisterBundle:Member:inscriptions.html.twig', array(
            'member' => $user));
    }


    /**
     * Inscription sheets. 
     *
     * Generate a PDF for all the Licensee of the given User.
     *
     * @param int $user_id Id of the User.
     * 
     * @return Response Render HTML page to download the PDF file.
     */
    public function inscriptions_pdfAction($user_id) {
        $user = $this->getUserFromID($user_id);
        $em = $this->getDoctrine()->getManager();
        $open_saison = $em->getRepository('SLNRegisterBundle:Saison')->getOpen();
        $licensees = $this->getLicenseeRepository()->getLicenseesForUser($user_id, $open_saison);

        $pdf = $this->container->get("white_october.tcpdf")->create();
        $assets = $this->container->get('templating.helper.assets');

        $title = "Feuilles d'inscriptions - {$user->getPrenom()} {$user->getNom()}";
        $first = True;
        foreach ($licensees as $licensee) {
            // For your people and no groupe selected, attach a default group (Not saved)
            if ($licensee->getGroupe($open_saison) == Null and $licensee->getAge() < 12) {
                $groupe = new Groupe();
                $groupe->setNom("<Inconnu>");
                $groupe->setCategorie(Groupe::ECOLE);

                $saison_link = new LicenseeSaison();
                $saison_link->setGroupe($groupe);
                $saison_link->setSaison($open_saison);
                $saison_link->setLicensee($licensee);
                $licensee->addSaisonLink($saison_link);
            }

            if ($licensee->getGroupe($open_saison) != Null)
                $licensee->inscriptionSheet($pdf, $assets, $open_saison, $title=$first ? $title : "");
        }

        $response = new Response($pdf->Output('inscriptions.pdf', 'I'));
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }


    /**
     * Get user from ID. If user is not current ID or a user with staff role, 
     * raise an exception.
     *
     * @param int $user_id Id of the given User
     *
     * @return User User instance
     *
     * @throws AccessDeniedException if $user_id does not match current user, and current user does not have admin rights.
     */
    public function getUserFromID($user_id) {
        $user = $this->getUserRepository()->find($user_id);

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
     * Get the repository for users
     *
     * @return UserRepository User repository
     */
    protected function getUserRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:User');
    }

    /**
     * Get the repository for licensees
     *
     * @return LicenseeRepository Licensee Repository
     */
    protected function getLicenseeRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:Licensee');
    }

}
