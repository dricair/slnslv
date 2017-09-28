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
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Entity\LicenseeSaison;

/**
 * Licensee Repository
 */
class LicenseeRepository extends EntityRepository {

    /**
     * Specify a saison for the query builder
     * 'l' needs to be the licensee in the query builder
     */
    protected function addSaison(&$qb, Saison $saison, $and_where=True) {
      $qb->leftjoin('l.saison_links', 's');
      if ($and_where)
        $qb->andWhere('s.licensee=l.id');
      else
        $qb->where('s.licensee=l.id');

      $qb->andWhere('s.saison=:saison_id')
         ->setParameter('saison_id', $saison->getId());
    }

    /**
     * Return the licensees for a specific user.
     *
     * @param int  $user_id Id of the User containing the Licensee
     * @param Saison $saison Licensees for the specific Saison; All if NULL
     *
     * @return Licensee[] List of Licensee
     *
     * @see User Licensees for a specific User
     */
    public function getLicenseesForUser($userId, $saison) {

        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->where('l.user = :user_id')
                   ->addOrderBy('l.naissance')
                   ->setParameter('user_id', $userId);

        if ($saison) {
            $this->addSaison($qb, $saison);
        }

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Get all licensees, additionally joining the Groupe
     *
     * @return Licensee[] List of licensee
     */
    public function getAll(Saison $saison) {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC');

        $this->addSaison($qb, $saison, false);

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Get all licensees, ordered by groups. Do not include licensees that have no group.
     *
     * @return Licensee[] List of Licensee
     */
    public function getAllByGroups(Saison $saison) {
        $qb = $this->createQueryBuilder('l')
                   ->select('l');

        $this->addSaison($qb, $saison, false);
        $qb->andWhere('s.groupe IS NOT NULL')
           ->join('s.groupe', 'g');

        $qb->addOrderBy('g.categorie',  'ASC')
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
    public function getAllNoGroups(Saison $saison, $builder=false) {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC')
                   ->andWhere('l.groupe IS NULL');

        $this->addSaison($qb, $saison);

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
    public function getAllForGroupe(Saison $saison, Groupe $groupe, $builder=false) {
        $qb = $this->createQueryBuilder('l')
                   ->select('l')
                   ->addOrderBy('l.nom',  'ASC')
                   ->addOrderBy('l.prenom', 'ASC');

        $this->addSaison($qb, $saison, false);
        $qb->andWhere('s.groupe = :groupe_id')
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
    public function getAllForFonction(Saison $saison, $fonction) {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l')
           ->where($qb->expr()->like('l.fonctions', ':fonction'))
           ->addOrderBy('l.nom',  'ASC')
           ->addOrderBy('l.prenom', 'ASC')
           ->setParameter('fonction', "%i:$fonction;%");

        $this->addSaison($qb, $saison);

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

        $qb->andWhere($qb->expr()->orX(
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
           ->andWhere('s.groupe IS NOT NULL')
           ->join('s.groupe', 'g')
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
    public function getAllForCompetitionGroup(Saison $saison, $id) {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l');
        $this->addSaison($qb, $saison, false);
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
    public function userHasInGroup(Licensee $licensee, Saison $saison, $groupe_id) {
        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l)');
        $qb->andWhere('l.user = :user_id')
           ->setParameter('user_id', $licensee->getUser()->getId());

        $this->addSaison($qb, $saison);
        $qb = $this->selCompetitionGroup($qb, $groupe_id);

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }


    /**
     * Return a list of all licensees which have incomplete inscription
     */
    public function getAllIncomplete(Saison $saison) {
        $inscriptions = LicenseeSaison::getInscriptionNames();

        $full_str = sprintf("a:%d:", count($inscriptions));

        $qb = $this->createQueryBuilder('l');
        $qb->select('l')
           ->where($qb->expr()->notLike('l.inscription', ':full_str'))
           ->addOrderBy('l.nom',  'ASC')
           ->addOrderBy('l.prenom', 'ASC')
           ->setParameter('full_str', $full_str . "%");

        $this->addSaison($qb, $saison);
        $qb->andWhere('s.groupe IS NOT NULL');

        return $qb->getQuery()
                  ->getResult();
    }

    /**
     * Search licensees
     */
    public function searchLicensees($saison, $search) {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l')
           ->where('l.nom LIKE :search')
           ->orWhere('l.prenom LIKE :search')
           ->addOrderBy('l.nom',  'ASC')
           ->addOrderBy('l.prenom', 'ASC')
           ->setParameter('search', "%$search%");

        $this->addSaison($qb, $saison);

        return $qb->getQuery()
                  ->getResult();

    }
}
