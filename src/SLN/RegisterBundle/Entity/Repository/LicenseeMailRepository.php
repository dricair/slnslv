<?php

namespace SLN\RegisterBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * LicenseeMailRepository
 *
 */
class LicenseeMailRepository extends EntityRepository {
    /**
     * Get all mails with pagination support
     *
     * @param int $user_id Id of the user to filter
     * @param int $page Index of the page, 1 for first one
     * @param int 
     *
     * @return Paginator List of mails
     */
     public function paginateMails($id=0, $page=1, $maxperpage=10) {
         if ($page < 1) {
            throw new \InvalidArgumentException("L'argument \$page ne peut être inférieur à 1 (valeur : '$page')");
         }

         $qb = $this->createQueryBuilder('l')
                    ->select('l')
                    // TODO Check ID
                    ->addOrderBy('l.updated', 'DESC');

         $qb->setFirstResult(($page-1) * $maxperpage)
            ->setMaxResults($maxperpage);

         return new Paginator($qb);
     }
}
