<?php

namespace SLN\RegisterBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    public function getAll() {
        $qb = $this->createQueryBuilder('u')
                   ->select('u')
                   ->addOrderBy('u.nom', 'ASC');

        return $qb->getQuery()
                  ->getResult();
    }
}