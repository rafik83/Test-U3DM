<?php
 
namespace AppBundle\Service;

use AppBundle\Entity\Quotation;
use AppBundle\Event\QuotationEvent;
use AppBundle\Event\QuotationEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class QuotationManager
{
    private $entityManager;

    private $eventDispatcher;


    /**
     * QuotationManager constructor
     *
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;

    }

    /**
     * Update the quotation status
     *
     * @param Quotation $quotation
     * @param int $newStatus
     * @param string $origin
     */
    public function updateStatus(Quotation $quotation, $newStatus, $origin)
    {
        // update quotation status
        $quotation->setStatus($newStatus);

        // dispatch an event
        $this->eventDispatcher->dispatch(QuotationEvents::PRE_STATUS_UPDATE, new QuotationEvent($quotation, $origin));

        // flush
        $this->entityManager->flush();

        // dispatch an event
        $this->eventDispatcher->dispatch(QuotationEvents::POST_STATUS_UPDATE, new QuotationEvent($quotation, $origin));
    }

}