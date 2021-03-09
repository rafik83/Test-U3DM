<?php

namespace AppBundle\Repository;

/**
 * ColorRepository
 */
class ColorRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find all the colors that are like the given parameter.
     *
     * @param $like string
     * @return mixed
     */
    public function findColorsLike($like)
    {
        $query_builder = $this->createQueryBuilder('c')
            ->where('c.name LIKE :like')
            ->setParameter('like', '%' . $like . '%')
            ->orderBy('c.name', 'ASC');
        return $query_builder->getQuery()->getResult();
    }
}