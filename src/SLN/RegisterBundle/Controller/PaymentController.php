<?php
/**
  * Payments controller class. 
  *
  * Contains controller class to deal with payments. Mostly admin functions.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;

use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\UserPayment;
use SLN\RegisterBundle\Entity\Tarif;
use SLN\RegisterBundle\Form\Type\UserPaymentType;

use SLN\RegisterBundle\Entity\Repository\UserPaymentRepository;

const FORMAT_CURRENCY_EUR		= '#,##0.00_-€';

/**
 * Payment controller.
 */
class PaymentController extends Controller {


    /**
     * Edit payments for a specific user. Contains form to create payments
     *
     * @param int $user_id  Id of the User
     * @param int $id       Id of the payment to edit. If 0, a new payment is created
     */
    public function editAction(Request $request, $user_id, $id=0) {
        $user = $this->getUserFromID($user_id);
        $user->addExtraTarif();

        if ($id == 0) {
            $payment = new UserPayment();
            $payment->setUser($user);
        } else  {
            $em = $this->getDoctrine()->getManager();
            $payment = $this->getPaymentsRepository()->find($id);

            if (!$payment) {
                throw $this->createNotFoundException('Ce paiement n\'existe pas dans la base de données.');
            }
        }

        $form    = $this->createForm(new UserPaymentType(), $payment);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              sprintf("Le paiement a été %s avec succès.", $id == 0 ? "ajouté" : "modifié")
            );

            return $this->redirect($this->generateUrl('SLNRegisterBundle_payment_user', array(
                                     'user_id' => $user_id)
                                    ));
        }

        else if ($request->getMethod() == 'POST') {
            $this->get('session')->getFlashBag()->add('error', "Des erreurs sont présentes dans le formulaire");
        }

        $payments = $this->getPaymentsRepository()->getPaymentsForUSer($user_id);
        $inscription_names = Licensee::getInscriptionNames();

        return $this->render('SLNRegisterBundle:Payments:edit.html.twig', array(
            'title' => sprintf("Paiements pour %s %s", $user->getPrenom(), $user->getNom()),
            'user' => $user,
            'id' => $id,
            'inscription_names' => $inscription_names, 
            'payment_val' => Licensee::PAIEMENT,
            'payments' => $payments,
            'form' => $form->createView(),
            'admin' => TRUE
        ));
    }


    /**
     * Search all payments/registerings that are incomplete
     * Return a full list by default
     */
    public function searchAction(Request $request) {
        $users = array();
        $search = $request->query->get('search', "");

        if ($search !== "") {
            foreach($this->getUserRepository()->searchUsers($search) as &$user) {
              $users[$user->getId()] = $user;
            }
            $licensees = $this->getLicenseeRepository()->searchLicensees($search);
        }

        else {
            $licensees = $this->getLicenseeRepository()->getAllIncomplete();
        }

        foreach($licensees as &$licensee) {
            $user_id = $licensee->getUser()->getId();
            if (!array_key_exists($user_id, $users))
                $users[$user_id] = $licensee->getUser();
        }

        foreach($users as $user_id => &$user) {
            $user->addExtraTarif();
        }

        $inscription_names = Licensee::getInscriptionNames();

        return $this->render('SLNRegisterBundle:Payments:search.html.twig',
                             array('search' => $search,
                                   'payment_val' => Licensee::PAIEMENT,
                                   'users' => array_values($users),
                                   'inscription_names' => $inscription_names));
    }


    /**
     * Delete a payment for a specific user.
     *
     * @param int $id Id of the payment
     */
    public function deleteAction(Request $request, $id) {
        $payment = $this->getPaymentsRepository()->find($id);

        if (!$payment instanceof UserPayment) {
            throw $this->createNotFoundException('Ce paiement n\'existe pas dans la base de données.');
        }

        $user = $this->getUserFromID($payment->getUser()->getId());
        $user->removePayment($payment);

        $em = $this->getDoctrine()->getManager();
        $em->remove($payment);
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('SLNRegisterBundle_payment_user', array('user_id' => $user->getId())));
    }

    /**
     * Export list of payments into Excel
     */
    public function exportAction(Request $request) {
        $users = array();
        $licensees = $this->getLicenseeRepository()->getAllByGroups();

        foreach($licensees as &$licensee) {
            $user_id = $licensee->getUser()->getId();
            if (!array_key_exists($user_id, $users))
                $users[$user_id] = $licensee->getUser();
        }

        foreach($users as $user_id => &$user) {
            $user->addExtraTarif();
        }

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Cédric Airaud")
           ->setTitle("Suivi des inscriptions")
           ->setSubject("Liste des cotisations et paiements");

        $data_licensees = array(array("Id", "Nom", "Prénom", "Groupe", "Cotisation", "Equipement", "Autres"));

        foreach ($licensees as &$licensee) {
            $total_cotisation = 0;
            $total_equipment = 0;
            $total_other = 0;
            $has_cotisation = FALSE;

            $tarifs = $licensee->getTarifList();
            foreach ($tarifs as &$tarif) {
                if ($tarif->type == Tarif::TYPE_GLOBAL or $tarif->type == Tarif::TYPE_1DAY) $has_cotisation = TRUE;
                if ($tarif->type == Tarif::TYPE_EQUIPMENT) $total_equipment += $tarif->value;
                else $total_cotisation += $tarif->value; 
            }

            if ($has_cotisation) {
                $data_licensees[] = array($licensee->getId(), $licensee->getNom(), $licensee->getPrenom(),
                                          $licensee->getGroupe()->getNom(), 
                                          $total_cotisation / 100, $total_equipment / 100, $total_other / 100);
            }
        }

        $num = count($data_licensees);
        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();
        $activeSheet->setTitle('Par licenciés');
        $activeSheet->fromArray($data_licensees, NULL, 'A1');
        $activeSheet->getStyle("E2:G$num")
                    ->getNumberFormat()
                    ->setFormatCode(FORMAT_CURRENCY_EUR);
        $activeSheet->getStyle("A1:G1")->applyFromArray(array("font" => array( "bold" => true)));
        $activeSheet->setAutoFilter("A1:G$num");

        $num_total = $num + 2;
        $activeSheet->setCellValue("D$num_total", "Total:");
        foreach (array("E", "F", "G") as $column) {
            $activeSheet->setCellValue("$column$num_total", "=SUM(${column}2:$column$num)");
            $activeSheet->getStyle("$column$num_total")
                        ->getNumberFormat()
                        ->setFormatCode(FORMAT_CURRENCY_EUR);
        }

        $data_payments  = array(array("Id", "Nom", "Prénom", "Paiement", "Valeur", "Description"));

        foreach ($users as &$user) {
            foreach ($user->getPayments() as &$payment) {
                $data_payments[] = array($user->getId(), $user->getNom(), $user->getPrenom(),
                                         $payment->getPtypeStr(), $payment->getValue() / 100, $payment->getDescription());
            }
        }

        $num = count($data_payments);
        $phpExcelObject->createSheet(1);
        $phpExcelObject->setActiveSheetIndex(1);
        $activeSheet = $phpExcelObject->getActiveSheet();
        $activeSheet->setTitle('Paiements');
        $activeSheet->fromArray($data_payments, NULL, 'A1');
        $activeSheet->getStyle("E2:E$num")
                    ->getNumberFormat()
                    ->setFormatCode(FORMAT_CURRENCY_EUR);
        $activeSheet->getStyle("A1:F1")->applyFromArray(array("font" => array( "bold" => true)));
        $activeSheet->setAutoFilter("A1:F$num");

        $num_total = $num + 2;
        $activeSheet->setCellValue("D$num_total", "Total:");
        $activeSheet->setCellValue("E$num_total", "=SUM(E2:E$num)");
        $activeSheet->getStyle("E$num_total")
                    ->getNumberFormat()
                    ->setFormatCode(FORMAT_CURRENCY_EUR);

        // Create the response
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'ListePayments.xlsx'
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
     * Get repository for the payments
     *
     * @return PaymentRepository Repository for UserPayment instances.
     */
    protected function getPaymentsRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:UserPayment');
    }

    /**
     * Get repository for the users
     *
     * @return LicenseeRepository Repository for Licensee instances.
     */
    protected function getUserRepository() {
        $em = $this->getDoctrine()
                   ->getManager();
        return $em->getRepository('SLNRegisterBundle:User');
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


