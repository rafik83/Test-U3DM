<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Coupon;
use AppBundle\Entity\Maker;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Entity\FollowUpUser;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OrderModelUp;

/**
 * OrderRepository
 */
class OrderRepository extends EntityRepository
{
    public function findOrdersForCustomer(User $user)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftJoin('o.maker', 'm')
            ->addSelect('m')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->where('o.customer = :user')
            ->andWhere('o.status != ' . Order::STATUS_AWAITING_PAYMENT . '') // exclude awaiting payments orders
            ->andWhere('o.status != ' . Order::STATUS_MODEL_NOT_PAID . '') // exclude awaiting payments orders
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findOrdersForCustomerOnDashboard(User $user)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftJoin('o.maker', 'm')
            ->addSelect('m')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->where('o.customer = :user')
            ->andWhere('o.status != ' . Order::STATUS_AWAITING_PAYMENT . '') // exclude awaiting payments orders
            ->andWhere('o.status != ' . Order::STATUS_REFUNDED . '') // exclude refunded orders
            ->andWhere('o.status != ' . Order::STATUS_DELIVERED . '') // exclude delivered orders
            ->andWhere('o.status != ' . Order::STATUS_CLOSED . '') // exclude closed orders
            ->andWhere('o.status != ' . Order::STATUS_MODEL_NOT_PAID . '') // exclude awaiting payments orders
            ->setParameter('user', $user)
            ->orderBy('o.updatedAt', 'DESC'); // order by updated date
        return $query_builder->getQuery()->getResult();
    }

    public function findOrdersAwaitingReviewForCustomerOnDashboard(User $user)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftJoin('o.maker', 'm')
            ->addSelect('m')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->leftJoin('o.statusUpdates', 'u')
            ->addSelect('u')
            ->where('o.customer = :user')
            ->andWhere('o.status = ' . Order::STATUS_DELIVERED . '')
            ->setParameter('user', $user)
            ->orderBy('o.updatedAt', 'DESC'); // order by updated date
        return $query_builder->getQuery()->getResult();
    }

    public function findOrdersForMaker(Maker $maker)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->addSelect('c')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->where('o.maker = :maker')
            ->andWhere('o.status != ' . Order::STATUS_AWAITING_PAYMENT . '') // exclude awaiting payments orders
            ->andWhere('o.status != ' . Order::STATUS_AWAITING_SEPA . '')    // exclude awaiting sepa orders
            ->andWhere('o.status != ' . Order::STATUS_REFUSED_SEPA . '')     // exclude refused sepa orders
            ->andWhere('o.status != ' . Order::STATUS_MODEL_NOT_PAID . '') // exclude awaiting payments orders
            ->setParameter('maker', $maker)
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findOrdersForMakerOnDashboard(Maker $maker)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->addSelect('c')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->where('o.maker = :maker')
            ->andWhere('o.status != ' . Order::STATUS_AWAITING_PAYMENT . '') // exclude awaiting payments orders
            ->andWhere('o.status != ' . Order::STATUS_AWAITING_SEPA . '')    // exclude awaiting sepa orders
            ->andWhere('o.status != ' . Order::STATUS_REFUSED_SEPA . '')     // exclude refused sepa orders
            ->andWhere('o.status != ' . Order::STATUS_REFUNDED . '')         // exclude refunded orders
            ->andWhere('o.status != ' . Order::STATUS_TRANSIT . '')          // exclude shipped orders
            ->andWhere('o.status != ' . Order::STATUS_DELIVERED . '')        // exclude shipped orders
            ->andWhere('o.status != ' . Order::STATUS_PND . '')              // exclude shipped orders
            ->andWhere('o.status != ' . Order::STATUS_CLOSED . '')           // exclude shipped orders
            ->andWhere('o.status != ' . Order::STATUS_MODEL_NOT_PAID . '') // exclude awaiting payments orders
            ->setParameter('maker', $maker)
            ->orderBy('o.updatedAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findAllOrders()
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->addSelect('c')
            ->leftJoin('o.maker', 'm')
            ->addSelect('m')
            ->leftJoin('o.file', 'f')
            ->addSelect('f')
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findOrdersToTrack()
    {
        $query_builder = $this->createQueryBuilder('o')
            ->where('o.status = ' . Order::STATUS_LABELED)
            ->orWhere('o.status = ' . Order::STATUS_TRANSIT)
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }

    public function findOrdersToRateOld(string $event)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftjoin(FollowUpUser::class, 'follow' , 'WITH', 'follow.user = o.customer and follow.typeRef = \'' .FollowUpUser::TYPE_ORDER .'\' and follow.refId = o.id  and follow.event = :event '  )
            ->where('o.status = ' .Order::STATUS_DELIVERED)
            ->AndWhere ('follow.id is NULL ' )
            ->setParameter('event', $event)
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }
    public function findOrdersToRate()
    {
        $query_builder = $this->createQueryBuilder('o')
            ->where('o.status = ' .Order::STATUS_DELIVERED)
            ->orderBy('o.createdAt', 'DESC');
        return $query_builder->getQuery()->getResult();
    }



    public function countOrdersWithCustomerAndCoupon(User $customer, Coupon $coupon)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->where('o.customer = :customer')
            ->andWhere('o.coupon = :coupon')
            ->andWhere('o.status != ' . Order::STATUS_CANCELED)
            ->andWhere('o.status != ' . Order::STATUS_REFUNDED)
            ->setParameter('customer', $customer)
            ->setParameter('coupon', $coupon);
        return $query_builder->getQuery()->getSingleScalarResult();
    }

    public function findOrdersForProjectWithSepaAwaiting($project)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftJoin('o.quotation', 'c')
            ->addSelect('c')
            ->leftJoin('c.project', 'm')
            ->addSelect('m')
            ->where('o.status =' . Order::STATUS_AWAITING_SEPA . '')
            ->andWhere('c.project = :project')
            ->setParameter('project',$project);
            
        return $query_builder->getQuery()->getResult();
    }

    public function findOrderForProject($project)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->leftJoin('o.quotation', 'c')
            ->addSelect('c')
            ->leftJoin('c.project', 'm')
            ->addSelect('m')
            ->where('c.project = :project')
            ->setParameter('project', $project);
            
        return $query_builder->getQuery()->getOneOrNullResult();
    }

    public function findOrderFromUp(string $word)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->where('o.reference LIKE :word')
            ->setParameter('word', $word.'%');
        return $query_builder->getQuery()->getResult();
    }

    public function findOrderFromUpAndMaker(string $word, Maker $makerFind)
    {
        $query_builder = $this->createQueryBuilder('o')
            ->where('o.reference LIKE :word')
            ->andWhere('o.maker = :makerFind')
            ->setParameter('word', $word.'%')
            ->setParameter('makerFind', $makerFind);
        return $query_builder->getQuery()->getResult();
    }
}