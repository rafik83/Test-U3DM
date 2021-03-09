<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Maker;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * MessageRepository
 */
class MessageRepository extends EntityRepository
{
    public function findLatestMessagesReceivedByUser(User $user)
    {
        $query_builder = $this->createQueryBuilder('m')
            ->leftJoin('m.order', 'o')
            ->addSelect('o')
            ->leftJoin('m.quotation', 'q')
            ->addSelect('q')
            ->leftJoin('q.project', 'p')
            ->addSelect('p')
            ->andWhere('o.customer = :user OR p.customer = :user')
            ->andWhere('m.needModerate = 0')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findLatestMessagesReceivedByMaker(Maker $maker)
    {
        $query_builder = $this->createQueryBuilder('m')
            ->leftJoin('m.order', 'o')
            ->addSelect('o')
            ->leftJoin('m.quotation', 'q')
            ->addSelect('q')
            ->andWhere('o.maker = :maker OR q.maker = :maker')
            ->andWhere('m.needModerate = 0')
            ->setParameter('maker', $maker)
            ->orderBy('m.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findLatestMessagesReceived()
    {
        $query_builder = $this->createQueryBuilder('m')
            ->leftJoin('m.order', 'o')
            ->addSelect('o')
            ->leftJoin('m.quotation', 'q')
            ->addSelect('q')
            ->orderBy('m.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }




}