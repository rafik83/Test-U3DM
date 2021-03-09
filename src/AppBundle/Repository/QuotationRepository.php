<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Maker;
use AppBundle\Entity\Project;
use AppBundle\Entity\Quotation;
use Doctrine\ORM\EntityRepository;

/**
 * QuotationRepository
 */
class QuotationRepository extends EntityRepository
{
    public function findQuotationForMaker(Maker $maker)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->where('o.maker = :maker')
            ->setParameter('maker', $maker)
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findQuotationsForMakerOnDashboard(Maker $maker)
    {
        $query_builder = $this->createQueryBuilder('q')
            ->leftJoin('q.project', 'p')
            ->addSelect('p')
            ->leftJoin('p.quotations', 'pq')
            ->addSelect('pq')
            ->where('q.maker = :maker')
            ->andWhere('q.status != ' . Quotation::STATUS_REFUSED . '')
            ->andWhere('q.status != ' . Quotation::STATUS_ACCEPTED . '')
            ->andWhere('q.status != ' . Quotation::STATUS_DISCARDED . '')
            ->andWhere('q.status != ' . Quotation::STATUS_CLOSED . '')
            ->setParameter('maker', $maker)
            ->orderBy('q.updatedAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findQuotationForAdmin()
    {
        $query_builder = $this->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function countForProjectAndMaker(Project $project, Maker $maker)
    {
        $query_builder = $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.project = :project')
            ->andWhere('q.maker = :maker')
            ->setParameter('project', $project)
            ->setParameter('maker', $maker)
            ->orderBy('q.createdAt', 'DESC');
        return $query_builder->getQuery()->getSingleScalarResult();
    }

}