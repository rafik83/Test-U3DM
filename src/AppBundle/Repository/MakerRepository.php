<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Maker;
use AppBundle\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

/**
 * MakerRepository
 */
class MakerRepository extends EntityRepository
{

    public function findMakersForProject(Project $project)
    {

        $query_builder = $this->createQueryBuilder('o')
            ->where('o.designer = true')
            ->andWhere('o.available = true')
            //->andWhere('o.enabled = true')
            ->andWhere('o.blacklisted = false')
            ->andWhere(':projectTypes MEMBER OF o.designProjectTypes')
            ->setParameter('projectTypes', $project->getType())
            ;

        return $query_builder->getQuery()->getResult();
    }

    public function searchDesigner($keywords,$excludeMakers)
    {

        $query_builder = $this->createQueryBuilder('o')
            ->where('o.designer = true')
            ->andWhere('o.available = true')
            //->andWhere('o.enabled = true')
            ->andWhere('o.blacklisted = false')
            ->andWhere('o.company LIKE :keywords')
            ->andWhere('o.id NOT IN (:excludeMakers)')
            ->setParameter('keywords', '%'.$keywords.'%')
            ->setParameter('excludeMakers', $excludeMakers)
            ;

        return $query_builder->getQuery()->getResult();
    }

    public function findMakersWhereRepresentativeIsNull()
    {

        $query_builder = $this->createQueryBuilder('o')
            ->where('o.stripeId IS NOT Null')
            ->andWhere('o.stripeRepresentativeId IS Null');

        return $query_builder->getQuery()->getResult();
    }
}