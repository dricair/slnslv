<?php
/**
  * License Repository.
  *
  * @see Licensee Repository for the Licensee class.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\User;

/**
 * Licensee Repository
 */
class LicenseeRepository extends EntityRepository {

    /**
     * Return the licensees for a specific user.
     *
     * @param int  $user_id Id of the User containing the Licensee
     * @param bool $active  If False, only select the Licensee that are active
     *
     * @return Licensee[] List of Licensee
     *
     * @see User Licensees for a specific User
     * @todo Active filter not done for getLicenseesForUser
     */
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

    /**
     * Get all licensees, additionally joining the Groupe
     *
     * @return Licensee[] List of licensee
     */
    public function getAll() {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->join('l.groupe', 'g')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC');

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Get all licensees, ordered by groups. Do not include licensees that have no group.
     *
     * @return Licensee[] List of Licensee
     */
    public function getAllByGroups() {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->join('l.groupe', 'g')
                   ->where('g IS NOT NULL')
                   ->addOrderBy('g.id',  'ASC')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC');

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Get all licensees that have no group selected.
     *
     * @return Licensee[] List of Licensee
     */
    public function getAllNoGroups() {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->where('l.groupe IS NULL')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC');

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Get all licensees for a specific group
     *
     * @param Groupe $groupe Selected Groupe
     *
     * @return Licensee[] List of Licensee
     */
    public function getAllForGroupe(Groupe $groupe) {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->where('l.groupe = :groupe_id')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC')
                   ->setParameter('groupe_id', $groupe->getId());

        return $qb->getQuery()
                  ->getResult();
    }

}
