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
use SLN\RegisterBundle\Entity\LicenseeSaison;

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
                   ->addOrderBy('g.groupe_order', 'ASC');

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Return the groups, sorted by category and order, which can be publicly visible
     *
     * @param bool $query: if true, return a query instead of results
     * @return QueryBuilder|Groupe[] list of groups
     */
    public function findPublic($query=FALSE) {
        $qb = $this->createQueryBuilder('g')
                   ->select('g')
                   ->where('g.show_public != 0')
                   ->addOrderBy('g.categorie', 'ASC')
                   ->addOrderBy('g.groupe_order', 'ASC');

        return $query ? $qb : $qb->getQuery()
                                 ->getResult();
    }

    /**
     * Return the groups, sorted by category and order, which can be publicly visible
     * If the given licensee is already in a group, this group is proposed as well
     *
     * @param Groupe $defaultGroupe: default Groupe to use if not null
     * @param bool $query: if true, return a query instead of results
     * @return QueryBuilder|Groupe[] list of groups
     */
    public function findLicenseePublic($defaultGroupe, $query=FALSE) {
        $qb = $this->findPublic(true);

        if ($defaultGroupe) {
            $qb->orWhere('g=:groupe_id')
               ->setParameter('groupe_id', $defaultGroupe->getId());
        }

        return $query ? $qb : $qb->getQuery()
                                 ->getResult();
    }

}
