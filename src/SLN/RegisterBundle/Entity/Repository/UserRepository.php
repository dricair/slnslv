<?php
/**
  * User Repository.
  *
  * @see User Repository for the User class.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * User Repository
 */
class UserRepository extends EntityRepository
{
    
    /**
     * Get all users
     *
     * @return User[] List of User
     */
    public function getAll() {
        $qb = $this->createQueryBuilder('u')
                   ->select('u')
                   ->addOrderBy('u.nom', 'ASC');

        return $qb->getQuery()
                  ->getResult();
    }
}
