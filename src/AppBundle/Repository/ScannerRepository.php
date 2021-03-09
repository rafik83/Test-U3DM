<?php

namespace AppBundle\Repository;

/**
 * ScannerRepository
 */
class ScannerRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find all the printers with the following conditions:
     * - the scanner minimum volume must be lower or equal to the provided volume
     * - the scanner must be available
     * - the scanner maker must be available
     * The aim of these conditions is not reduce the query result size.
     *
     * @param float $volume : object volume in mm3
     * @return mixed
     */
    public function findAvailableScannersAcceptingVolume($volume)
    {
        $query_builder = $this->createQueryBuilder('p')
            ->leftJoin('p.maker', 'maker')
            ->addSelect('maker')
            ->where('p.minVolume <= :volume')
            ->andWhere('p.available = 1')
            ->andWhere('maker.available = 1')
            ->setParameter('volume', $volume);
        return $query_builder->getQuery()->getResult();
    }
}