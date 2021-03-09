<?php

namespace AppBundle\Repository;

/**
 * ProspectRepository
 */
class ProspectRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Count the total number of Prospects
     *
     * @return int
     */
    public function countProspects()
    {
        $query_builder = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');
        return $query_builder->getQuery()->getSingleScalarResult();
    }
}