<?php

namespace AppBundle\Repository;

/**
 * SearchRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SearchRepository extends \Doctrine\ORM\EntityRepository
{
    public function getNumberSearch()
    {
        $q = $this->createQueryBuilder('s')
            ->select('SUM(s.count) as nbSearch');

        return $q->getQuery()->getOneOrNullResult();
    }
}
