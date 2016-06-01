<?php
/**
  * Payment Repository.
  *
  * @see Repository for the UserPayment class.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use SLN\RegisterBundle\Entity\UserPayment;

/**
 * UserPayment Repository
 */
class UserPaymentRepository extends EntityRepository {
    /**
     * Return the payments for a specific user.
     *
     * @param int  $user_id Id of the User containing the Licensee
     *
     * @return Payment[] List of payments
     */
     public function getPaymentsForUSer($userId) {
         $qb = $this->createQueryBuilder('p')
                    ->select('p')
                    ->where('p.user = :user_id')
                    ->setParameter('user_id', $userId);

         return $qb->getQuery()
                   ->getResult();
     }
     
}

