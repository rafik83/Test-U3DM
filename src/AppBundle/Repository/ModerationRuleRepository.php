<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ModeleRule;
use Doctrine\ORM\EntityRepository;


/**
 * ModerationRuleRepository
 */
class ModerationRule extends EntityRepository
{
    public function findAllOrderPriority()
    {
        $query_builder = $this->createQueryBuilder('r')
            ->orderBy('r.position', 'ASC');
        return $query_builder->getQuery()->getResult();
    }
}