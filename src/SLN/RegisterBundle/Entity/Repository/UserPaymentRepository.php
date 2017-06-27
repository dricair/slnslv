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
     public function getPaymentsForUSer($saison, $userId) {
         $qb = $this->createQueryBuilder('p')
                    ->select('p')
                    ->where('p.user = :user_id')
                    ->andWhere('p.saison = :saison_id')
                    ->setParameter('user_id', $userId)
                    ->setParameter('saison_id', $saison->getId());

         return $qb->getQuery()
                   ->getResult();
     }
     
}

