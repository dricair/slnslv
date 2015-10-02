<?php
/**
  * Groupe Repository.
  *
  * @see Groupe Repository for the Groupe class.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use SLN\RegisterBundle\Entity\Groupe;

/**
 * Groupe Repository
 */
class GroupeRepository extends EntityRepository
{
    /**
     * Returns all the groups, sorted by Category, then order
     *
     * @return Groupe[] list of groups
     */
    public function findAll() {
        $qb = $this->createQueryBuilder('g')
                   ->select('g')
                   ->addOrderBy('g.categorie', 'ASC')
                   ->addOrderBy('g.order', 'ASC');

        return $qb->getQuery()
                  ->getResult();
    }

}
