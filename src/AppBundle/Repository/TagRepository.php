<?php

namespace AppBundle\Repository;

/**
 * TagRepository
 */
class TagRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find all the enabled tags with the given type and that are like the given parameter.
     *
     * @param $type string
     * @param $like string
     * @return mixed
     */
    public function findTagsForTypeAndLike($type, $like)
    {
        $query_builder = $this->createQueryBuilder('t')
            ->where('t.type = :type')
            ->andWhere('t.name LIKE :like')
            ->andWhere('t.enabled = true')
            ->setParameter('type', $type)
            ->setParameter('like', '%' . $like . '%')
            ->orderBy('t.name', 'ASC');
        return $query_builder->getQuery()->getResult();
    }
}