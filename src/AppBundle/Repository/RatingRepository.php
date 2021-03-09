<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Rating;
use Doctrine\ORM\EntityRepository;

/**
 * RatingRepository
 */
class RatingRepository extends EntityRepository
{
    public function findAllRatingByStatus($enabled)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->where('o.enabled = :enabled')
            ->setParameter('enabled', $enabled)
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

}