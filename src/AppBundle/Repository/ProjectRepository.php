<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * ProjectRepository
 */
class ProjectRepository extends EntityRepository
{
    public function findProjectsForAdmin()
    {
        $query_builder = $this->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->addSelect('c')
            ->leftJoin('p.quotations', 'q')
            ->addSelect('q')
            ->leftJoin('q.orders', 'o')
            ->addSelect('o')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->where('p.status != ' . Project::STATUS_CREATED . '')
            ->orderBy('p.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findProjectsForCustomer(User $user)
    {
        $query_builder = $this->createQueryBuilder('p')
            ->leftJoin('p.quotations', 'q')
            ->addSelect('q')
            ->leftJoin('q.orders', 'o')
            ->addSelect('o')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->where('p.customer = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findProjectsForCustomerOnDashboard(User $user)
    {
        $query_builder = $this->createQueryBuilder('p')
            ->leftJoin('p.quotations', 'q')
            ->addSelect('q')
            ->leftJoin('q.orders', 'o')
            ->addSelect('o')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->where('p.customer = :user')
            ->andWhere('p.status != ' . Project::STATUS_DELETED . '')
            ->andWhere('p.status != ' . Project::STATUS_ORDERED . '')
            ->andWhere('p.status != ' . Project::STATUS_CANCEL . '')
            ->setParameter('user', $user)
            ->orderBy('p.updatedAt', 'DESC'); // order by updated date
        return $query_builder->getQuery()->getResult();
    }

}