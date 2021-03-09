<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderItemPrint;
use AppBundle\Entity\PrintFile;
use AppBundle\Entity\User;
use AppBundle\Event\OrderEvent;
use AppBundle\Service\OrderManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PrintFileController extends Controller
{
    /**
     * @Route("/printFile/{name}/download", name="print_file_download")
     *
     * @param PrintFile $file
     * @param ObjectManager $entityManager
     * @param OrderManager $orderManager
     * @return BinaryFileResponse
     */
    public function downloadAction(PrintFile $file, ObjectManager $entityManager, OrderManager $orderManager)
    {
        // TODO additional security checks on file access (not so simple to authorize both admin and user, because they use a different firewall)
        /*
        // get the file related order
        $orderItem = $entityManager->getRepository('AppBundle:OrderItemPrint')->findOneWithFile($file);
        if (null === $orderItem) {
            throw new AccessDeniedException();
        }
        $order = $orderItem->getOrder();

        // make sure the user is allowed to download that file
        if ($order->getCustomer() !== $this->getUser() && (null === $this->getUser()->getMaker() || $order->getMaker() !== $this->getUser()->getMaker())) {
            throw new AccessDeniedException();
        }
        */

        // if file is downloaded by a maker, move the order status to processing if it was new
        if ($this->getUser() instanceof User && null !== $this->getUser()->getMaker()) {
            // get related order
            $orderItem = $entityManager->getRepository('AppBundle:OrderItemPrint')->findOneWithFile($file);
            if (null !== $orderItem) {
                /** @var OrderItemPrint $orderItem */
                $order = $orderItem->getOrder();
                if ($order->getMaker()->getId() === $this->getUser()->getMaker()->getId()) {
                    if (Order::STATUS_NEW === $order->getStatus()) {
                        $orderManager->updateStatus($order, Order::STATUS_PROCESSING, OrderEvent::ORIGIN_MAKER);
                        
                    }
                }
            }
        }

        // get file from project directory
        $response = new BinaryFileResponse($this->get('kernel')->getProjectDir() . '/var/uploads/print/' . $file->getName());

        // prevent caching
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);
        

        // force file download
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getOriginalName());
        
        return $response;
        
    }
}