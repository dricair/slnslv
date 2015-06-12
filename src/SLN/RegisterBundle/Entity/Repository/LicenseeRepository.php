<?php

namespace SLN\RegisterBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * LicenseeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LicenseeRepository extends EntityRepository {

    public function getLicenseesForUser($userId, $active = True) {

        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->where('l.user = :user_id')
                   ->addOrderBy('l.naissance')
                   ->setParameter('user_id', $userId);

        //if ($active === True) {
        //    // activeDate: 1er sept - 31 août
        //    $activeDate = new \DateTime();
        //    if (date('n', $activeDate->getTimestamp()) >= 9) $activeDate->setDate(date('Y', $activeDate->getTimestamp()), 9, 1);
        //    else $activeDate->setDate(date('Y', $activeDate->getTimestamp())-1, 9, 1);

        //    $qb->andWhere('l.date_licence is NULL OR l.date_licence >= :activeDate')
        //       ->setParameter('activeDate', $activeDate);
        //}

        return $qb->getQuery()
                  ->getResult();
    }
}
