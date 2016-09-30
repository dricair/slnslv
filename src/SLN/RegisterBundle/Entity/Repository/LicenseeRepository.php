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
                   ->addOrderBy('g.categorie',  'ASC')
                   ->addOrderBy('g.groupe_order',  'ASC')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC');

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Get all licensees that have no group selected.
     *
     * @param bool $builder If true, return the QueryBuilder instead of the result
     *
     * @return Licensee[] List of Licensee
     */
    public function getAllNoGroups($builder=false) {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->where('l.groupe IS NULL')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC');

        if ($builder) return $qb;
        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Get all licensees for a specific group
     *
     * @param Groupe $groupe Selected Groupe
     * @param bool $builder If true, return the QueryBuilder instead of the result
     *
     * @return Licensee[] List of Licensee
     */
    public function getAllForGroupe(Groupe $groupe, $builder=false) {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->where('l.groupe = :groupe_id')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC')
                   ->setParameter('groupe_id', $groupe->getId());

        if ($builder) return $qb;
        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Get all licensees that have a specific function
     *
     * @param int $fonction Fonction index to look for
     *
     * @return Licensee[] List of Licensee
     */
    public function getAllForFonction($fonction) {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l')
           ->where($qb->expr()->like('l.fonctions', ':fonction'))
           ->addOrderBy('l.nom',  'ASC')
           ->addOrderBy('l.prenom', 'ASC')
           ->setParameter('fonction', "%i:$fonction;%");

        // Search is approximative (Index or value ?)
        $licensees = [];
        foreach($qb->getQuery()->getResult() as $licensee) {
            if (in_array($fonction, $licensee->getFonctions())) $licensees[] = $licensee;
        }

        return $licensees;
    }

    /**
     * Select conditions to get licensees from a specific competition group
     *
     * @param QueryBuilder $qb: query builder
     * @param int $id: Index of the group
     *
     * @return $qb
     */
    protected function selCompetitionGroup(&$qb, $id) {
        $competitions = Groupe::competitionCategories();
        $cnames = array_keys($competitions);
        if (!array_key_exists($id, $cnames))
            return $qb;
        $competition = $competitions[$cnames[$id]];

        $qb->join('l.groupe', 'g')
           ->where($qb->expr()->orX(
               $qb->expr()->andX(
                 $qb->expr()->eq('l.sexe', Licensee::FEMME),
                 $qb->expr()->in('YEAR(l.naissance)', $competition["F"])
               ),
               $qb->expr()->andX(
                 $qb->expr()->eq('l.sexe', Licensee::HOMME),
                 $qb->expr()->in('YEAR(l.naissance)', $competition["H"])
               )
             )
           )
           ->andWhere('l.groupe IS NOT NULL')
           ->andWhere('g.categorie = :competition_group')
           ->addOrderBy('l.nom',  'ASC')
           ->addOrderBy('l.prenom', 'ASC')
           ->setParameter('competition_group', Groupe::COMPETITION);
  
        return $qb;
    }

    /**
     * Return a list of users for a given competition group
     *
     * @param int id: Competition group index
     *
     * @return Licensee[] List of licensees
     */
    public function getAllForCompetitionGroup($id) {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l');
        $qb = $this->selCompetitionGroup($qb, $id);

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Return true if the User for the given Licensee has any Licensee in the given
     * group
     *
     * @param Licensee $licensee Licensee to link the User
     * @param int $groupe_id Id of the groupe to look at for the licensees
     *
     * @return bool True if one of the licensees is in the Groupe
     */
    public function userHasInGroup(Licensee $licensee, $groupe_id) {
        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l)');
        $qb = $this->selCompetitionGroup($qb, $groupe_id);
        $qb->andWhere('l.user = :user_id')
           ->setParameter('user_id', $licensee->getUser()->getId());

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }


    /**
     * Return a list of all licensees which have incomplete inscription
     */
    public function getAllIncomplete() {
        $inscriptions = Licensee::getInscriptionNames();

        $full_str = sprintf("a:%d:", count($inscriptions));

        $qb = $this->createQueryBuilder('l');
        $qb->select('l')
           ->where('l.groupe IS NOT NULL')
           ->andWhere($qb->expr()->notLike('l.inscription', ':full_str'))
           ->addOrderBy('l.nom',  'ASC')
           ->addOrderBy('l.prenom', 'ASC')
           ->setParameter('full_str', $full_str . "%");

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Search licensees
     */
    public function searchLicensees($search) {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l')
           ->where('l.nom LIKE :search')
           ->orWhere('l.prenom LIKE :search')
           ->addOrderBy('l.nom',  'ASC')
           ->addOrderBy('l.prenom', 'ASC')
           ->setParameter('search', "%$search%");

        return $qb->getQuery()
                  ->getResult();

    }
}
