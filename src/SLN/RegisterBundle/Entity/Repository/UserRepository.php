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

    /**
     * Search users
     */
    public function searchUsers($search) {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u')
           ->where('u.nom LIKE :search')
           ->orWhere('u.prenom LIKE :search')
           ->orWhere('u.email LIKE :search')
           ->orWhere('u.secondary_email LIKE :search')
           ->addOrderBy('u.nom',  'ASC')
           ->addOrderBy('u.prenom', 'ASC')
           ->setParameter('search', "%$search%");

        return $qb->getQuery()
                  ->getResult();

    }
}
