<?php

namespace AppBundle\Repository;

use AppBundle\Entity\PrintFile;
use Doctrine\ORM\EntityRepository;

/**
 * OrderItemPrintRepository
 */
class OrderItemPrintRepository extends EntityRepository
{
    public function findOneWithFile(PrintFile $file)
    {
        $query_builder = $this->createQueryBuilder('i')
            ->where('i.file = :file')
            ->setParameter('file', $file);
        return $query_builder->getQuery()->getOneOrNullResult();
    }
}