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

use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\UserPayment;
use SLN\RegisterBundle\Form\Type\UserPaymentType;

use SLN\RegisterBundle\Entity\Repository\UserPaymentRepository;

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
    public function editAction($user_id, $id=0) {
        $user = $this->getUserFromID($user_id);

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

        $request = $this->getRequest();
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

        return $this->render('SLNRegisterBundle:Payments:edit.html.twig', array(
            'title' => sprintf("Paiements pour %s %s", $user->getPrenom(), $user->getNom()),
            'user_id' => $user_id,
            'id' => $id,
            'payments' => $payments,
            'form' => $form->createView(),
            'admin' => TRUE
        ));
    }


    /**
     * Search all payments/registerings that are incomplete
     * Return a full list by default
     */
    public function searchAction() {
       return $this->render('SLNRegisterBundle:Payments:search.html.twig');
    }


    /**
     * Delete a payment for a specific user.
     *
     * @param int $id Id of the payment
     */
    public function deleteAction($id) {
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


}


