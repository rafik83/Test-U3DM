<?php

namespace AppBundle\Repository;

/**
 * PrinterRepository
 */
class PrinterRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find all the printers with the following conditions:
     * - the printer minimum volume must be lower or equal to the provided volume
     * - the printer must be available
     * - the printer maker must be available
     * The aim of these conditions is not reduce the query result size.
     *
     * @param float $volume : object volume in mm3
     * @return mixed
     */
    public function findAvailablePrintersAcceptingVolume($volume,$volumeBounding)
    {
        $query_builder = $this->createQueryBuilder('p')
            ->leftJoin('p.technology', 't')
            ->addSelect('t')
            ->leftJoin('p.maker', 'maker')
            ->addSelect('maker')
            ->leftJoin('p.products', 'pp')
            ->addSelect('pp')
            ->leftJoin('pp.material', 'm')
            ->addSelect('m')
            ->leftJoin('pp.colors', 'c')
            ->addSelect('c')
            ->leftJoin('pp.layer', 'l')
            ->addSelect('l')
            ->leftJoin('pp.finishings', 'f')
            ->addSelect('f')
            ->leftJoin('f.finishing', 'ref_f')
            ->addSelect('ref_f')
            ->where('p.minVolume <= :volume 
                AND p.volumeMethode = 0')
            ->orWhere('p.minVolume <= :volumeBounding 
            AND p.volumeMethode = 1')    
            ->andWhere('p.available = 1')
            ->andWhere('maker.available = 1')
            ->setParameter('volume', $volume)
            ->setParameter('volumeBounding', $volumeBounding);
        return $query_builder->getQuery()->getResult();
    }
}