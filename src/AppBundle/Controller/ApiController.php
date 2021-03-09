<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
use AppBundle\Entity\Coupon;
use AppBundle\Entity\Embeddable\Dimensions;
use AppBundle\Entity\Maker;
use AppBundle\Entity\User;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderItemDesign;
use AppBundle\Entity\OrderItemPrint;
use AppBundle\Entity\OrderItemPrintFinishing;
use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectFile;
use AppBundle\Entity\QuotationLine;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Setting;
use AppBundle\Entity\Shipping;
use AppBundle\Entity\OrderModelBasket;
use AppBundle\Entity\OrderModelBasketItem;
use AppBundle\Entity\OrderModelUp;
use AppBundle\Entity\ModelBuy;
use AppBundle\Entity\Model;
use AppBundle\Event\OrderEvent;
use AppBundle\Event\OrderEvents;
use AppBundle\Event\ProjectEvent;
use AppBundle\Event\ProjectEvents;
use AppBundle\Event\PaymentEvent;
use AppBundle\Event\PaymentEvents;
use AppBundle\Service\Chronopost;
use AppBundle\Service\PrintEngine;
use AppBundle\Service\StripeManager;
use AppBundle\Service\ProjectManager;
use AppBundle\Service\OrderManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Charge;
use Stripe\Card;
use Stripe\PaymentIntent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

use Psr\Log\LoggerInterface;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    private $logger;

    public function __construct(  LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @Route("/print", name="api_print_ajax")
     *
     * @param Request     $request
     * @param PrintEngine $engine
     * @return JsonResponse
     */
    public function printAjaxAction(Request $request, PrintEngine $engine)
    {
        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // quick request content format check
        if (!isset($requestContent['dimensions']['x']) || !isset($requestContent['dimensions']['y']) || !isset($requestContent['dimensions']['z']) || !isset($requestContent['volume'])) {
            return new JsonResponse(json_encode(array('Error' => 'Missing arguments')));
        }

        // get dimensions from request
        $dimensions = new Dimensions((float)$requestContent['dimensions']['x'], (float)$requestContent['dimensions']['y'], (float)$requestContent['dimensions']['z']);

        // get volume from request
        $volume = $requestContent['volume'];

        // get optional quantity from request
        $quantity = 1;
        if (array_key_exists('quantity', $requestContent)) {
            $quantity = (int)$requestContent['quantity'];
        }

        // get optional allowed makers list from request
        $allowedMakers = array();
        if (array_key_exists('makers', $requestContent)) {
            $allowedMakers = $requestContent['makers'];
        }

        // perform the search and return its response in a JsonResponse
        $jsonResult = $engine->search($volume, $dimensions, $quantity, $allowedMakers);

        // return JSON
        return new JsonResponse($jsonResult);
    }

    /**
     * @Route("/shipping", name="api_shipping_ajax")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return JsonResponse
     */
    public function shippingAjaxAction(Request $request, ObjectManager $entityManager)
    {
        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // get maker id from request
        $makerId = $requestContent['makerId'];

        // look for that maker
        /** @var Maker $maker */
        $maker = $entityManager->getRepository('AppBundle:Maker')->find($makerId);
        if (null === $maker) {
            return new JsonResponse();
        }

        // initialize result array
        $jsonResult = array();

        // get global shipping settings
        foreach (array(Shipping::TYPE_HOME_STANDARD => Setting::SHIPPING_HOME_STANDARD, Shipping::TYPE_HOME_EXPRESS => Setting::SHIPPING_HOME_EXPRESS, Shipping::TYPE_RELAY => Setting::SHIPPING_RELAY) as $shippingType => $settingKey) {
            /** @var Setting $setting */
            $setting = $entityManager->getRepository('AppBundle:Setting')->findOneByKey($settingKey);
            if (null !== $setting) {
                $jsonResult[$shippingType] = $setting->getValue();
            }
        }

        // get maker pickup setting
        $jsonResult[Shipping::TYPE_PICKUP] = array('available' => $maker->hasPickup());
        if ($maker->hasPickup()) {
            $address = $maker->getPickupAddress();
            if (null !== $address) {
                $jsonResult[Shipping::TYPE_PICKUP]['address'] = $address->getFlatAddress();
            }
        }

        // return JSON
        return new JsonResponse(json_encode($jsonResult, JSON_FORCE_OBJECT));
    }

    /**
     * @Route("/shipping/relays", name="api_shipping_relays_ajax")
     *
     * @param Request $request
     * @param Chronopost $chronopost
     * @return JsonResponse
     */
    public function shippingRelaysAjaxAction(Request $request, Chronopost $chronopost)
    {
        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // get address data from request
        $address = new Address();
        $address->setStreet1($requestContent['street']);
        $address->setZipcode($requestContent['zipcode']);
        $address->setCity($requestContent['city']);
        $address->setCountry('FR');

        // get relays as a JSON string
        $jsonRelays = $chronopost->getRelays($address);

        // return JSON
        return new JsonResponse($jsonRelays, Response::HTTP_OK);
    }

    /**
     * @Route("/fee", name="api_fee_ajax")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return JsonResponse
     */
    public function feeAjaxAction(Request $request, ObjectManager $entityManager)
    {
        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // get production amount from request
        $amount = (int)$requestContent['amount'];
        $this->logger->info('Fee AJAX : avant CreatePayment');
        $this->logger->info($requestContent['amount']);
        $this->logger->info($amount);
        $tagSpec = "";
        if (isset ($requestContent['taggSpec'])) {$tagSpec = $requestContent['taggSpec'];}

        $this->logger->info("TagSpec:") ;
        $this->logger->info( $tagSpec);
        if ($tagSpec != "") {
            $this->logger->info('Fee AJAX : COVID');
            $feeTaxExcl = 0;

        } else {
            // get global fee settings
            if ($amount < $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::FEE_THRESHOLD)->getValue()) {
                // if amount is below threshold, get the flat amount
                $feeTaxExcl = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::FEE_AMOUNT)->getValue();
            } else {
                // else get the fee rate
                $rate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::FEE_PERCENT)->getValue();
                $feeTaxExcl = (int)round($amount * $rate / 100);
            }
        }
        // add tax
        $taxRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_TAX_RATE)->getValue();
        $feeTaxIncl = (int)round($feeTaxExcl * (100.0 + $taxRate) / 100);

        // return JSON
        return new JsonResponse(json_encode(array('fee_tax_excl' => $feeTaxExcl, 'fee_tax_incl' => $feeTaxIncl), JSON_FORCE_OBJECT));
    }

    /**
     * @Route("/placeOrder", name="api_place_order_ajax")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @return JsonResponse
     */
    public function placeOrderAjaxAction(Request $request, ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // create order
        $order = new Order();

        // get order type
        $orderType = $requestContent['type'];
        if (Order::TYPE_PRINT !== $orderType && Order::TYPE_DESIGN !== $orderType) {
            return new JsonResponse(json_encode(array('message' => 'Unknown order type'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $order->setType($orderType);

        // get customer
        $customerId = $requestContent['customer_id'];
        $customer = $entityManager->getRepository('AppBundle:User')->find($customerId);
        if (null === $customer) {
            return new JsonResponse(json_encode(array('message' => 'Unknown customer'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if (null !== $this->getUser()) {
            if ($this->getUser()->getId() !== $customer->getId()) {
                return new JsonResponse(json_encode(array('message' => 'The customer does not match the logged user'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        $order->setCustomer($customer);

        // get maker
        $makerId = $requestContent['maker_id'];
        $maker = $entityManager->getRepository('AppBundle:Maker')->find($makerId);
        if (null === $maker) {
            return new JsonResponse(json_encode(array('message' => 'Unknown maker'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $order->setMaker($maker);

        // get billing and shipping addresses
        $this->logger->info('Recupere l adresse de facturation');
        if ($requestContent['billing_address'] != null ) {
            $billing_address = $this->getAddressFromRequest($requestContent, 'billing_address');
            $order->setBillingAddress($billing_address);
        }else

        
        // if order need shipment
        $this->logger->info('Recupere l adresse de shipping');
        if ($requestContent['shipping_address'] != null ) {
            $shipping_address = $this->getAddressFromRequest($requestContent, 'shipping_address');
            $order->setShippingAddress($shipping_address);
        }

        // update customer default addresses if not already set
        if (null === $customer->getDefaultShippingAddress()) {
            $customer->setDefaultShippingAddress(clone $shipping_address);
        }
        if (null === $customer->getDefaultBillingAddress()) {
            $customer->setDefaultBillingAddress(clone $billing_address);
        }

        // get shipping type
        $shipping_type = $requestContent['shipping_type'];
        $order->setShippingType($shipping_type);

        // get shipping relay identifier
        if (isset($requestContent['shipping_relay_identifier'])) {
            $shipping_relay_identifier = $requestContent['shipping_relay_identifier'];
            $order->setShippingRelayIdentifier($shipping_relay_identifier);
        }

        // get instructions
        if (isset($requestContent['instructions'])) {
            $instructions = $requestContent['instructions'];
            if ('' !== trim($instructions)) {
                $order->setInstructions($instructions);
            }
        }

        // get amounts
        $amounts = $requestContent['amounts'];
        $order->setTotalAmountTaxIncl($amounts['total_amount_tax_incl']);
        $order->setTotalAmountTaxExcl($amounts['total_amount_tax_excl']);
        $order->setProductionAmountTaxIncl($amounts['production_amount_tax_incl']);
        $order->setProductionAmountTaxExcl($amounts['production_amount_tax_excl']);
        $order->setShippingAmountTaxIncl($amounts['shipping_amount_tax_incl']);
        $order->setShippingAmountTaxExcl($amounts['shipping_amount_tax_excl']);
        $order->setFeeAmountTaxIncl($amounts['fee_amount_tax_incl']);
        $order->setFeeAmountTaxExcl($amounts['fee_amount_tax_excl']);

        // handle coupon
        if (isset($requestContent['coupon']) && $amounts['discount_amount_tax_incl'] && $amounts['discount_amount_tax_excl']) {
            $couponCode = $requestContent['coupon'];
            if (null !== $couponCode) {
                /** @var Coupon $coupon */
                $coupon = $entityManager->getRepository('AppBundle:Coupon')->findOneByCode($couponCode);
                if (null !== $coupon) {
                    $order->setCoupon($coupon);
                    $order->setDiscountAmountTaxIncl($amounts['discount_amount_tax_incl']);
                    $order->setDiscountAmountTaxExcl($amounts['discount_amount_tax_excl']);
                    // update remaining stock if relevant
                    if (null !== $coupon->getRemainingStock()) {
                        $remainingStock = $coupon->getRemainingStock() - 1;
                        $coupon->setRemainingStock($remainingStock);
                    }
                }
            }
        }

        // get commission rate
        $commissionRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_COMMISSION_RATE)->getValue();
        if (null !== $maker->getCustomCommissionRate()) {
            $commissionRate = $maker->getCustomCommissionRate();
        }
        $order->setCommissionRate($commissionRate);

        // set default tax rate
        $defaultTaxRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_TAX_RATE)->getValue();
        $order->setDefaultTaxRate($defaultTaxRate);

        // handle items depending on order type
        if (Order::TYPE_PRINT === $orderType) {

            // get print items
            $items = $requestContent['items'];
            foreach ($items as $item) {

                $orderItem = new OrderItemPrint();

                // get file
                $fileId = $item['file_id'];
                $file = $entityManager->getRepository('AppBundle:PrintFile')->find($fileId);
                if (null === $file) {
                    return new JsonResponse(json_encode(array('message' => 'Unknown file'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                // get file original name
                if (isset($item['file_name'])) {
                    $fileOriginalName = $item['file_name'];
                    $file->setOriginalName($fileOriginalName);
                }

                // set order item file
                $orderItem->setPrintFile($file);

                // get amount
                $amountTaxIncl = $item['amount_tax_incl'];
                $amountTaxExcl = $item['amount_tax_excl'];
                $quantity = $item['quantity'];
                $orderItem->setUnitAmountTaxIncl((int)round($amountTaxIncl / $quantity));
                $orderItem->setUnitAmountTaxExcl((int)round($amountTaxExcl / $quantity));
                $orderItem->setQuantity($quantity);

                // get tax rate
                $taxRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_TAX_RATE)->getValue();
                $orderItem->setTaxRate($taxRate);

                // get dimensions
                $dimensions = new Dimensions();
                $dimensions->setX($item['dimensions']['x'])->setY($item['dimensions']['y'])->setZ($item['dimensions']['z']);
                $orderItem->setDimensions($dimensions);

                // get volume
                $volume = $item['volume'];
                $orderItem->setVolume($volume);

                // get technology
                $technologyId = $item['technology'];
                $technology = $entityManager->getRepository('AppBundle:Technology')->find($technologyId);
                if (null === $technology) {
                    return new JsonResponse(json_encode(array('message' => 'Unknown technology'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $orderItem->setTechnology($technology);

                // get material
                $materialId = $item['material'];
                $material = $entityManager->getRepository('AppBundle:Material')->find($materialId);
                if (null === $material) {
                    return new JsonResponse(json_encode(array('message' => 'Unknown material'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $orderItem->setMaterial($material);

                // get color
                $colorId = $item['color'];
                $color = $entityManager->getRepository('AppBundle:Color')->find($colorId);
                if (null === $color) {
                    return new JsonResponse(json_encode(array('message' => 'Unknown color'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $orderItem->setColor($color);

                // get layer
                $layerId = $item['layer'];
                $layer = $entityManager->getRepository('AppBundle:Layer')->find($layerId);
                if (null === $layer) {
                    return new JsonResponse(json_encode(array('message' => 'Unknown layer'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $orderItem->setLayer($layer);

                // get filling rate
                $fillingRate = $item['fillingRate'];
                $orderItem->setFillingRate($fillingRate);

                // get finishings
                foreach ($item['finishings'] as $fin) {

                    $finishingItem = new OrderItemPrintFinishing();

                    // get finishing
                    $finishingId = $fin['finishing'];
                    $finishing = $entityManager->getRepository('AppBundle:Finishing')->find($finishingId);
                    if (null === $finishing) {
                        return new JsonResponse(json_encode(array('message' => 'Unknown finishing'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    $finishingItem->setFinishing($finishing);

                    // get amount
                    $amountTaxIncl = $fin['amount_tax_incl'];
                    $amountTaxExcl = $fin['amount_tax_excl'];
                    $quantity = $orderItem->getQuantity();
                    $finishingItem->setUnitAmountTaxIncl((int)round($amountTaxIncl / $quantity));
                    $finishingItem->setUnitAmountTaxExcl((int)round($amountTaxExcl / $quantity));
                    $finishingItem->setQuantity($quantity);

                    // get tax rate
                    $finishingItem->setTaxRate($taxRate);

                    // add finishing item to order item
                    $orderItem->addItemFinishing($finishingItem);

                    // add finishing item to order
                    $order->addItem($finishingItem);
                }

                // add item
                $order->addItem($orderItem);

            }

        } elseif (Order::TYPE_DESIGN === $orderType) {

            // get quotation
            $quotationId = $requestContent['quotation_id'];
            $quotation = $entityManager->getRepository('AppBundle:Quotation')->find($quotationId);
            if (null === $quotation) {
                return new JsonResponse(json_encode(array('message' => 'Unknown quotation'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $order->setQuotation($quotation);

            // get quotation lines and add each one to the order as an OrderItemDesign instance
            $quotationLines = $quotation->getLines();
            foreach ($quotationLines as $line) {
                /** @var QuotationLine $line */
                $item = new OrderItemDesign();
                $item->setUnitAmountTaxExcl($line->getPrice());
                $item->setUnitAmountTaxIncl($line->getPrice() * (100 + $defaultTaxRate) / 100);
                $item->setQuantity($line->getQuantity());
                $item->setTaxRate($defaultTaxRate);
                $item->setDescription($line->getDescription());
                $order->addItem($item);
            }
        }

        // dispatch an event
        $eventDispatcher->dispatch(OrderEvents::PRE_PERSIST, new OrderEvent($order));

        // persist
        $entityManager->persist($order);
        $entityManager->flush();

        // dispatch an event
        $eventDispatcher->dispatch(OrderEvents::POST_PERSIST, new OrderEvent($order));

        // return JSON
        return new JsonResponse(json_encode(array('message' => 'Order successfully created', 'order_id' => $order->getId(), 'order_reference' => $order->getReference()), JSON_FORCE_OBJECT), Response::HTTP_OK);
    }

    /**
     * Get an Address entity from the request content
     *
     * Expected format:
     * "<addressKey>": {
     *      "lastname":  "<nom>",
     *      "firstname": "<prénom>",
     *      "company":   "<société>",
     *      "street1":   "<numéro et rue, ligne 1>",
     *      "street2":   "<numéro et rue, ligne 2>",
     *      "zipcode":   "<code postal>",
     *      "city":      "<ville>",
     *      "country":   "<code pays>",
     *      "telephone": "<téléphone>"
     *  }
     *
     * @param array $requestContent : request as an associative array
     * @param string $addressKey : address key in the request content
     * @return Address
     */
    private function getAddressFromRequest($requestContent, $addressKey)
    {
        $addressData = $requestContent[$addressKey];

        $address = new Address();

        $address->setLastname($addressData['lastname']);
        $address->setFirstname($addressData['firstname']);
        if (isset($addressData['company'])) {
            $address->setCompany($addressData['company']);
        }
        $address->setStreet1($addressData['street1']);
        if (isset($addressData['street2']) && '' !== $addressData['street2']) {
            $address->setStreet2($addressData['street2']);
        }
        $address->setZipcode($addressData['zipcode']);
        $address->setCity($addressData['city']);
        $address->setCountry($addressData['country']);
        $address->setTelephone($addressData['telephone']);

        return $address;
    }

    /**
     * @Route("/third-3-D-secure", name="api_third_d_secure_ajax")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param StripeManager $stripeManager
     * @return JsonResponse
     */
    public function thirdDSecureAjaxAction(Request $request, ObjectManager $entityManager, StripeManager $stripeManager)
    {

        $requestContent = json_decode($request->getContent(), true);

        $orderType = $requestContent['orderType'];
        if($orderType === 'basket') {
            /** @var User $maker */
            $user = $this->getUser();
        } else {
            $user = $customer = $entityManager->getRepository('AppBundle:User')->find($requestContent['user_id']);
        }

        

        // create customer account 
        if(!$user->getStripeCustomerId()){

            try {
               $token = null;

               $customerStripeId = $stripeManager->createCustomer($user,$token,$requestContent['thirdDSecure']);
               
               $customerStripeId = $customerStripeId->id; 

               $user->setStripeCustomerId($customerStripeId);

               $entityManager->persist($user);
               $entityManager->flush();

               $initStripeUser = true;
                
            } catch (Exception $e) {

                $customerStripeId = null;
                
            }


        } else {

            $customerStripeId = $user->getStripeCustomerId();

        }
        //$this->logger->info('API THIRD SECURE : avant CreatePayment');
        try {
            $intent = $stripeManager->createPaymentIntent(
                $requestContent['amount'],
                $requestContent['thirdDSecure'],
                $customerStripeId
            );
            //$this->logger->info('API THIRD SECURE : après CreatePayment OK');

            } catch (\Exception $exception) {
                //$this->logger->info('API THIRD SECURE : après CreatePayment KO');
                //return new JsonResponse(json_encode(array('message' => 'Exception while creating charge: ' . $exception->getMessage()), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
                return new JsonResponse(json_encode(array('error' => $exception->getMessage()), JSON_FORCE_OBJECT), Response::HTTP_OK);
            }





        if (($intent->status == 'requires_action' || $intent->status == 'requires_source_action' ) &&
            $intent->next_action->type == 'use_stripe_sdk') {

            # Tell the client to handle the action
            return new JsonResponse(json_encode(array(
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
                'payment_method_id' => $requestContent['thirdDSecure']
            ), JSON_FORCE_OBJECT), Response::HTTP_OK);

        } else if ($intent->status == 'succeeded') {

            return new JsonResponse(json_encode(array(
                'success' => true,
                'payment_intent_client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id
            ), JSON_FORCE_OBJECT), Response::HTTP_OK);

        } else {
            # Invalid status
            return new JsonResponse(json_encode(array('error' => 'Invalid PaymentIntent status'), JSON_FORCE_OBJECT), Response::HTTP_OK);

        }

    }

    /**
     * @Route("/payment", name="api_payment_ajax")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param StripeManager $stripeManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param OrderManager $orderManager
     * @return JsonResponse
     */
    public function paymentAjaxAction(Request $request, ObjectManager $entityManager, StripeManager $stripeManager, EventDispatcherInterface $eventDispatcher,  OrderManager $orderManager)
    {
        
        $initStripeUser = false;
        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse(json_encode('Coucou'));
        }



        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // If The Payment is cancel
        
        if (isset($requestContent['cancel_intent'] ) &&  $requestContent['cancel_intent'] )  {
            $this->logger->info('API PAYMENT : Cancel Payment');

            $stripeManager->cancelPaymentIntent($requestContent['payment_id']);
            $this->logger->info('API PAYMENT : Cancel Payment Done');
            return new JsonResponse(json_encode(array('message' => 'Cancel Payment succeeded'), JSON_FORCE_OBJECT), Response::HTTP_OK);

            
        }


        // get order
        $orderId = $requestContent['order_id'];
        $orderType = $requestContent['orderType'];
        
        if($orderType === 'basket') {
            $order = $entityManager->getRepository('AppBundle:OrderModelUp')->find($orderId);
        } else {
            $order = $entityManager->getRepository('AppBundle:Order')->find($orderId);
        }
        if (null === $order) {
            return new JsonResponse(json_encode(array('message' => 'Unknown order'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        

        // get payment type, if provided
        $type = Payment::TYPE_CARD;

        if (isset($requestContent['type'])) {
            $type = $requestContent['type'];
        }
        
        $user = $order->getCustomer();
        
        // get token
        $token = $requestContent['token'];
        
        // create the charge
        try {
            
            if($requestContent['type'] == Payment::TYPE_CARD) {
               
                //Add card to USER
                /*$card = $stripeManager->AddCustomerCard($user,$token);

                if($card instanceof Card){

                   $token = $card->id;

                }*/
                
                $stripeManager->updatePaymentIntent($token,$order->getReference());
                
                //Add card 
                /*if($requestContent['payment_method_id'] != null){

                    //update customer with card
                    $stripeManager->updateCustomer($user,$requestContent['payment_method_id']);

                }*/
                
                try {
                    // In fact there is no confirm payment with stripe; because it is already done. Here Just retrieve from stripe the payment intent
                    $paymentIntent = $stripeManager->confirmPaymentIntent($token);
                } catch(Exception $e){

                }
                

                if($paymentIntent instanceof PaymentIntent){
                    // create the payment
                    $payment = new Payment();
                    $payment->setAmount($order->getTotalAmountTaxIncl());
                    $payment->setChargeId($paymentIntent['charges']['data']['0']['id']);
                    $payment->setChargeStatus($paymentIntent['charges']['data']['0']['status']);
                    $payment->setType($type);
                    $chargeId = $paymentIntent['charges']['data']['0']['id'];
                    if($orderType !== 'basket') {
                        // add the payment to the order
                        $order->addPayment($payment);
                    }
                    $paymentStatus = $paymentIntent['charges']['data']['0']['status'];
                } else {

                    return new JsonResponse(json_encode(array('message' => 'Stripe did not return a PaymentIntent object'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);

                }


            } elseif ($requestContent['type'] == Payment::TYPE_SEPA) {

                $token = $requestContent['token'];

                if(!$user->getStripeCustomerId()){

                    if($orderType === 'basket') {
                        $charge = $stripeManager->createOrderUpCharge($order, $token, $customerStripeId);
                    } else {
                        $charge = $stripeManager->createOrderCharge($order, $token, $customerStripeId);
                    }
                    try {
                       $token = null;

                       $customerStripeId = $stripeManager->createCustomer($user,$token,null);
                       
                       $customerStripeId = $customerStripeId->id; 
                       $user->setStripeCustomerId($customerStripeId);

                       $entityManager->persist($user);
                       $entityManager->flush();

                       $initStripeUser = true;
                        
                    } catch (Exception $e) {

                        $customerStripeId = null;
                        
                    }


                } else {

                    $customerStripeId = $user->getStripeCustomerId();

                }
                
                $charge = $stripeManager->createOrderCharge($order, $token, $customerStripeId);
                if ($charge instanceof Charge) {
                    // create the payment
                    $payment = new Payment();
                    $payment->setAmount($order->getTotalAmountTaxIncl());
                    $payment->setChargeId($charge->id);
                    $payment->setChargeStatus($charge->status);
                    $payment->setType($type);
                    // add the payment to the order
                    $order->addPayment($payment);

                    $paymentStatus = $charge->status;

                    $chargeId = $charge->id;

                } else {
                    return new JsonResponse(json_encode(array('message' => 'Stripe did not return a Charge object'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
                }

            } else {
                // create the payment TYPE == VIREMENT
                $paymentStatus = Payment::CHARGE_STATUS_PENDING;
                $chargeId ="ch_u3dm_virement"; 

                $payment = new Payment();
                $payment->setAmount($order->getTotalAmountTaxIncl());
                $payment->setChargeId($chargeId);
                $payment->setChargeStatus($paymentStatus);
                $payment->setType($type);
                // add the payment to the order
                $order->addPayment($payment);

         
            }

            if($orderType === 'basket') {
                $orders = $entityManager->getRepository('AppBundle:Order')->findOrderFromUp($order->getReference());
                
                foreach ($orders as $orderUnder) {
                    $payment = new Payment();
                    $payment->setAmount($orderUnder->getTotalAmountTaxIncl());
                    $payment->setChargeId($chargeId);
                    $payment->setChargeStatus($paymentStatus);
                    $payment->setType($type);

                    // add the payment to the order
                    $orderUnder->addPayment($payment);

                    // dispatch events
                    $eventDispatcher->dispatch(PaymentEvents::PRE_PERSIST, new PaymentEvent($payment));
                    $eventDispatcher->dispatch(OrderEvents::PRE_STATUS_UPDATE, new OrderEvent($orderUnder, OrderEvent::ORIGIN_CUSTOMER));

                    // flush to cascade persisting
                    $entityManager->flush();

                    // dispatch events
                    $eventDispatcher->dispatch(OrderEvents::POST_STATUS_UPDATE, new OrderEvent($orderUnder, OrderEvent::ORIGIN_CUSTOMER));
                    $eventDispatcher->dispatch(PaymentEvents::POST_PERSIST, new PaymentEvent($payment));
                }
                
            } else {
                /*
                // create the payment
                $payment = new Payment();
                $payment->setAmount($order->getTotalAmountTaxIncl());
                $payment->setChargeId($charge->id);
                $payment->setChargeStatus($charge->status);
                $payment->setType($type);

                // add the payment to the order
                $order->addPayment($payment);
                */
                
                // dispatch events
                $eventDispatcher->dispatch(PaymentEvents::PRE_PERSIST, new PaymentEvent($payment));
                
                $eventDispatcher->dispatch(OrderEvents::PRE_STATUS_UPDATE, new OrderEvent($order, OrderEvent::ORIGIN_CUSTOMER));
                
                // flush to cascade persisting
                $entityManager->flush();
                
                // dispatch events
                $eventDispatcher->dispatch(OrderEvents::POST_STATUS_UPDATE, new OrderEvent($order, OrderEvent::ORIGIN_CUSTOMER));
                
                $eventDispatcher->dispatch(PaymentEvents::POST_PERSIST, new PaymentEvent($payment));
                
            }

        } catch(\Exception $exception) {
            return new JsonResponse(json_encode(array('message' => 'Exception while creating charge: ' . $exception->getMessage()), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // return JSON
        if (
            (Payment::CHARGE_STATUS_SUCCEEDED !== $paymentStatus && $type == Payment::TYPE_CARD) || 
            (Payment::CHARGE_STATUS_PENDING !== $paymentStatus && $type == Payment::TYPE_SEPA) ||
            (Payment::CHARGE_STATUS_PENDING !== $paymentStatus && $type == Payment::TYPE_VIREMENT) 
        ) {
            return new JsonResponse(json_encode(array('message' => 'Payment did not succeed (status: '.$paymentStatus.')'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if($orderType === 'basket') {
            /** @var User $maker */
            $user = $this->getUser();
            
            $baskets = $entityManager->getRepository('AppBundle:OrderModelBasket')->findBasketForUser($user);
            $basket = $baskets[0];

            $items = $entityManager->getRepository('AppBundle:OrderModelBasketItem')->findItemsInBasket($basket);
            foreach ($items as $basketItem){
                $model = $basketItem->getModel();
                $model->setNbDownload($model->getNbDownload() + 1);
                $entityManager->persist($model);
                $entityManager->remove($basketItem);
            }
            $entityManager->remove($basket);

            $orders = $entityManager->getRepository('AppBundle:Order')->findOrderFromUp($order->getReference());
            foreach($orders as $orderInf) {
                //$entityManager->remove($orderInf);
                //$entityManager->flush();
                //$orderInf->setStatus(Order::STATUS_MODEL_BUY);

                // update the order status
                $orderManager->updateStatus($orderInf, Order::STATUS_MODEL_BUY, OrderEvent::ORIGIN_CUSTOMER);

                $entityManager->persist($orderInf);
            }
            
            $order->setStatus(Order::STATUS_MODEL_BUY);
            $entityManager->persist($order);
            $entityManager->flush();
        }
        return new JsonResponse(json_encode(array('message' => 'Payment succeeded'), JSON_FORCE_OBJECT), Response::HTTP_OK);
    }

    /**
     * @Route("/coupon/apply", name="api_coupon_apply_ajax")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @return JsonResponse
     */
    public function couponApplyAjaxAction(Request $request, ObjectManager $entityManager)
    {
        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // get data from request
        $couponCode = $requestContent['coupon'];
        $totalAmountTaxIncl = $requestContent['total_amount_tax_incl'];
        $totalAmountTaxExcl = $requestContent['total_amount_tax_excl'];
        $productionAmountTaxIncl = $requestContent['production_amount_tax_incl'];
        $productionAmountTaxExcl = $requestContent['production_amount_tax_excl'];
        $shippingAmountTaxIncl = $requestContent['shipping_amount_tax_incl'];
        $shippingAmountTaxExcl = $requestContent['shipping_amount_tax_excl'];
        $feeAmountTaxIncl = $requestContent['fee_amount_tax_incl'];
        $feeAmountTaxExcl = $requestContent['fee_amount_tax_excl'];

        // look for the coupon
        $responseContent = array();
        $responseStatus = Response::HTTP_NOT_FOUND;

        /** @var Coupon $coupon */
        $coupon = $entityManager->getRepository('AppBundle:Coupon')->findOneByCode($couponCode);
        if (null !== $coupon) {

            $valid = true;

            // get customer
            $customerId = $requestContent['customer_id'];
            $customer = $entityManager->getRepository('AppBundle:User')->find($customerId);
            if (null === $customer) {
                $valid = false;
            }
            if (null !== $this->getUser()) {
                if ($this->getUser()->getId() !== $customer->getId()) {
                    $valid = false;
                }
            }

            // check coupon validity
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            if (!$coupon->isEnabled()) {
                $valid = false;
            }
            if ($coupon->getStartDate() > $now || $coupon->getEndDate() < $now) {
                $valid = false;
            }
            if (($productionAmountTaxExcl + $feeAmountTaxExcl) < $coupon->getMinOrderAmount()) {
                $valid = false;
            }
            if (null !== $coupon->getInitialStock() && 0 >= $coupon->getRemainingStock()) {
                $valid = false;
            }
            if (0 !== $coupon->getCustomers()->count()) {
                if (!$coupon->getCustomers()->contains($customer)) {
                    $valid = false;
                }
            }
            if (null !== $coupon->getMaxUsagePerCustomer()) {
                $customerUsage = $entityManager->getRepository('AppBundle:Order')->countOrdersWithCustomerAndCoupon($customer, $coupon);
                if ($customerUsage >= $coupon->getMaxUsagePerCustomer()) {
                    $valid = false;
                }
            }

            // if coupon is valid, get the discount
            if ($valid) {

                // get coupon label
                $responseContent['coupon_label'] = $coupon->getLabel();

                // get discount amounts
                $discount_amount_tax_excl = $coupon->getDiscountAmount();
                if (null !== $coupon->getDiscountPercent()) {
                    $discount_amount_tax_excl = round(($productionAmountTaxExcl + $feeAmountTaxExcl) * $coupon->getDiscountPercent() / 100);
                }

                // get tax rate to get discount with tax
                $taxRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_TAX_RATE)->getValue();
                $discount_amount_tax_incl = round($discount_amount_tax_excl * (100 + $taxRate) / 100);

                $responseContent['discount_amount_tax_incl'] = $discount_amount_tax_incl;
                $responseContent['discount_amount_tax_excl'] = $discount_amount_tax_excl;

                $responseStatus = Response::HTTP_OK;
            }

        }

        // return JSON
        return new JsonResponse(json_encode($responseContent, JSON_FORCE_OBJECT), $responseStatus);
    }



    /**
     * @Route("/project/save", name="api_save_project_ajax")
     *
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param ProjectManager $projectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @return JsonResponse
     */
    public function saveProjectAjaxAction(Request $request, ObjectManager $entityManager, EventDispatcherInterface $eventDispatcher,ProjectManager $projectManager)
    {

        //Init State : create or update
        $state = null;

        // make sure this is an Ajax Request
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        // decode the request JSON data
        $postCustomerId = json_decode($request->request->get('customer_id'), true);
        $postProject = json_decode($request->request->get('project'), true);
        $postProjectId = json_decode($request->request->get('project_id'), true);
        $postStatus = json_decode($request->request->get('status'), true);
        $postFiles = $request->files;

        //var_dump(count($request->files));
        //die();

        $customer = $entityManager->getRepository('AppBundle:User')->find($postCustomerId);
        if (null === $customer) {
            return new JsonResponse(json_encode(array('message' => 'Unknown customer'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if (null !== $this->getUser()) {
            if ($this->getUser()->getId() !== $customer->getId()) {
                return new JsonResponse(json_encode(array('message' => 'The customer does not match the logged user'), JSON_FORCE_OBJECT), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // create or update project
        if($postProjectId){

            $project = $entityManager->getRepository('AppBundle:Project')->findOneBy([
                'id' => $postProjectId,
                'customer' => $customer
            ]);

            $state = 'update';

        } else {

            $project = new Project();

            $state = 'create';

        }

        if($postStatus == 1){

            $project->setStatus(Project::STATUS_CREATED);

            if ('create' === $state) {
                // dispatch an event if project was just created
                $eventDispatcher->dispatch(ProjectEvents::PRE_STATUS_UPDATE, new ProjectEvent($project));
            }

        } else if($postStatus == 2){

            $project->setStatus(Project::STATUS_SENT);
            $project->setReturnReason(null);

            // dispatch an event
            $eventDispatcher->dispatch(ProjectEvents::PRE_STATUS_UPDATE, new ProjectEvent($project));

        }

        $project->setCustomer($customer);
        $project->setName($postProject['name']);
        $project->setDescription($postProject['description']);
        $project->setScanOnSite($postProject['scanOnSite']);

        // DATE & DELIVERY
        $project->setDeliveryTime($postProject['deliveryTime']);
        $endDate = $postProject['deliveryTime'];
        $closeDate = new \DateTime('now noon', new \DateTimeZone('UTC'));

        if($endDate == Project::DELIVERY_ONE_WEEK){

            $closureQuotation = 'P'.$entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::ONE_WEEK_PROJECT_CLOSURE_TIME)->getValue().'D';
            $closeDate->add(new \DateInterval($closureQuotation));

        } else if ($endDate == Project::DELIVERY_FIFTEEN_DAYS) {

            $closureQuotation = 'P'.$entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::FIFTEEN_DAYS_PROJECT_CLOSURE_TIME)->getValue().'D';
            $closeDate->add(new \DateInterval($closureQuotation));
            
        } else if ($endDate == Project::DELIVERY_ONE_MONTH) {

            $closureQuotation = 'P'.$entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::ONE_MONTH_PROJECT_CLOSURE_TIME)->getValue().'D';
            $closeDate->add(new \DateInterval($closureQuotation));
            
        } else if ($endDate == Project::DELIVERY_THREE_MONTHS) {

            $closureQuotation = 'P'.$entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::THREE_MONTHS_PROJECT_CLOSURE_TIME)->getValue().'D';
            $closeDate->add(new \DateInterval($closureQuotation));
            
        } else if ($endDate == Project::DELIVERY_MORE_THAN_THREE_MONTHS) {

            $closureQuotation = 'P'.$entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::MORE_THAN_THREE_MONTHS_PROJECT_CLOSURE_TIME)->getValue().'D';
            $closeDate->add(new \DateInterval($closureQuotation));
            
        }

        $project->setClosedAt($closeDate);

        //TYPE
        $projectTypeId = $postProject['projectType'];
        $projectType = $entityManager->getRepository('AppBundle:ProjectType')->find($projectTypeId);
        $project->setType($projectType);

        //FIELDS
        foreach ($project->getFields() as $field) {
            $project->removeField($field);
        }
        $fields = $postProject['fields'];
        foreach ($fields as $field) {

            $fieldObject = $entityManager->getRepository('AppBundle:Field')->find($field);
            $project->addField($fieldObject);

        }

        //SKILLS
        foreach ($project->getSkills() as $skill) {
            $project->removeSkill($skill);
        }
        $skills = $postProject['skills'];
        foreach ($skills as $skill) {

            $skillObject = $entityManager->getRepository('AppBundle:Skill')->find($skill);
            $project->addSkill($skillObject);

        }

        //SOFTWARE
        foreach ($project->getSoftwares() as $software) {
            $project->removeSoftware($software);
        }
        $softwares = $postProject['softwares'];
        foreach ($softwares as $software) {

            $softwareObject = $entityManager->getRepository('AppBundle:Software')->find($software);
            $project->addSoftware($softwareObject);

        }


        //DIMENSIONS
        if($postProject['dim']['x'] > 0 && $postProject['dim']['y'] > 0 && $postProject['dim']['z'] > 0){

            $dimensions = new Dimensions();
            $dimensions->setX($postProject['dim']['x'])->setY($postProject['dim']['y'])->setZ($postProject['dim']['z']);
            $project->setDimensions($dimensions);

        }


        // ADDRESS
        if($projectType->isAddressProject()){

            $addressRequest = $postProject['address'];

            if($project->getScanAddress()){

                $address = $project->getScanAddress();

            } else {

                $address = new Address();

            }
            
            $address->setStreet1($addressRequest['street1']);
            if (isset($addressRequest['street2'])) {
                $address->setStreet2($addressRequest['street2']);
            }
            $address->setFirstname($addressRequest['firstname']);
            $address->setLastname($addressRequest['lastname']);
            $address->setZipcode($addressRequest['zipcode']);
            $address->setCity($addressRequest['city']);
            $address->setCountry('FR');
            $address->setTelephone($addressRequest['telephone']);

            $project->setScanAddress($address);

        }

        //File 
        if(count($postFiles) > 0){

            foreach ($postFiles as $file) {
                //File manipulation
                $fileName = md5 ( uniqid () ) . '.' . $file->guessExtension ();
                $originalName = $file->getClientOriginalName ();

                $newFile = new ProjectFile();
                $newFile->setName($fileName);
                $newFile->setProject($project);
                $newFile->setOriginalName($originalName);

                $file->move($this->get('kernel')->getRootDir().'/../var/uploads/project',$fileName);

                $project->addFile($newFile);
            }

        }
        

        // dispatch an event
        $pre_event = ProjectEvents::PRE_PERSIST;
        if ('update' === $state) {
            $pre_event = ProjectEvents::PRE_UPDATE;
        }
        $eventDispatcher->dispatch($pre_event, new ProjectEvent($project));

        // persist
        $entityManager->persist($project);
        $entityManager->flush();

        // dispatch an event
        $post_event = ProjectEvents::POST_PERSIST;
        if ('update' === $state) {
            $post_event = ProjectEvents::POST_UPDATE;
        }
        $eventDispatcher->dispatch($post_event, new ProjectEvent($project));

        if (Project::STATUS_SENT === $project->getStatus()) {
            // dispatch an event (not needed if project has not been sent yet)
            $eventDispatcher->dispatch(ProjectEvents::POST_STATUS_UPDATE, new ProjectEvent($project));
        }

        $files = $projectManager->filesToJson($project);

        // return JSON
        return new JsonResponse(json_encode(array('message' => 'Project successfully created', 'project_id' => $project->getId(), 'project_reference' => $project->getReference(), 'files' => $files), JSON_FORCE_OBJECT), Response::HTTP_OK);
    }


    /**
     * @Route("/model/order", name="api_model_order")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function modelOrderAjaxAction(Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {

        /* on récupère la valeur envoyée par la vue */
        $order = $request->request->get('order');
        $url = $request->request->get('url');

        $data_list = explode("/", $url);
        //$data_list = explode("/", 'http://localhost:8000/fr/modele/categorySup/1');
        $data_list2 = explode("?form%", $data_list[5]);
        $models_data_null = true;
    

        if(sizeof($data_list) > 4) {
            switch ($data_list2[0]){
                case "":
                    switch ($order){
                        case 'Prix croissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.priceTaxIncl', 'ASC');
                            break;
                        case 'Prix décroissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.priceTaxIncl', 'DESC');
                            break;
                        case 'Remarqués':
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.love', 'DESC');
                            break;
                        case 'Populaires':
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.nb_download', 'DESC');
                            break;
                        default:
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.createdAt', 'DESC');
                    }
                    break;
                case "category":
                    //echo("<script>console.log('PHP: ".$data_list[7]."');</script>");
                    switch ($order){
                        case 'Prix croissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'ASC');
                            break;
                        case 'Prix décroissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'DESC');
                            break;
                        case 'Remarqués':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.love', 'DESC');
                            break;
                        case 'Populaires':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.nb_download', 'DESC');
                            break;
                        default:
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.createdAt', 'DESC');
                    }
                    break;
                case "Upcategory":
                    //echo("<script>console.log('PHP: ".$data_list[7]."');</script>");
                    switch ($order){
                        case 'Prix croissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'ASC');
                            break;
                        case 'Prix décroissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'DESC');
                            break;
                        case 'Remarqués':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.love', 'DESC');
                            break;
                        case 'Populaires':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.nb_download', 'DESC');
                            break;
                        default:
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.createdAt', 'DESC');
                    }
                    break;
                case "maker":
                    switch ($order){
                        case 'Prix croissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'ASC');
                            break;
                        case 'Prix décroissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'DESC');
                            break;
                        case 'Remarqués':
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.love', 'DESC');
                            break;
                        case 'Populaires':
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.nb_download', 'DESC');
                            break;
                        default:
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.createdAt', 'DESC');
                    }
                    break;
                case "search":
                    $data_search_list = preg_split("/search%5D=|&form/", $url);
                    $search_words = $data_search_list[1];
                    $each_word = explode("+", $search_words);

                    $models = array();

                    foreach ($each_word as $word){
                        if(strtoupper($word) == 'GRATUIT') {
                            $models_find = $entityManager->getRepository('AppBundle:Model')->findModelsFreeFromWord($word);
                        } else {
                            $models_find = $entityManager->getRepository('AppBundle:Model')->findModelsFromWord($word);    
                        }
                        
                        $models_with_duplicate = array_merge($models,$models_find);

                        //this line is just to avoid duplicate of model
                        $models = array_unique($models_with_duplicate, SORT_REGULAR);
                    }

                    $models_data = array();
                    
                    foreach ($models as $model){
                        $data = array(
                            'id' => $model->getId(),
                            'name' => $model->getName(),
                            'makerName' => $model->getMaker()->getCompany(),
                            'priceTaxIncl' => $model->getPriceTaxIncl() / 100,
                            'love' => $model->getLove(),
                            'download' => $model->getNbDownload(),
                            'image' => $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'),
                            'date' => $model->getCreatedAt(),
                        );
                        array_push($models_data, $data);
                    }

                    foreach ($models_data as $key => $row) {
                        $id[$key]  = $row['id'];
                        $name[$key] = $row['name'];
                        $makerName[$key]  = $row['makerName'];
                        $priceTaxIncl[$key] = $row['priceTaxIncl'];
                        $love[$key]  = $row['love'];
                        $download[$key] = $row['download'];
                        $image[$key] = $row['image'];
                        $date[$key] = $row['date'];
                    }

                    $id  = array_column($models_data, 'id');
                    $name = array_column($models_data, 'name');
                    $makerName = array_column($models_data, 'makerName');
                    $priceTaxIncl = array_column($models_data, 'priceTaxIncl');
                    $love = array_column($models_data, 'love');
                    $download = array_column($models_data, 'download');
                    $image = array_column($models_data, 'image');
                    $date = array_column($models_data, 'date');

                    switch ($order){
                        case 'Prix croissant':
                            array_multisort($priceTaxIncl, SORT_ASC, $date, SORT_DESC, $models_data);
                            break;
                        case 'Prix décroissant':
                            array_multisort($priceTaxIncl, SORT_DESC, $date, SORT_DESC, $models_data);
                            break;
                        case 'Remarqués':
                            array_multisort($love, SORT_DESC, $date, SORT_DESC, $models_data);
                            break;
                        case 'Populaires':
                            array_multisort($download, SORT_DESC, $date, SORT_DESC, $models_data);
                            break;
                        default:
                            array_multisort($date, SORT_DESC, $models_data);
                    }

                    $models_data_null = false;
                    break;
            }
        }

        //$models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder("12", 'model.updatedAt', 'ASC');
        
        //$models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder(6, 'model.createdAt', 'DESC');

        if($models_data_null == true) {
            $models_data = array();

            foreach ($models as $model){
                $data = array(
                    'id' => $model->getId(),
                    'name' => $model->getName(),
                    'makerName' => $model->getMaker()->getCompany(),
                    'priceTaxIncl' => $model->getPriceTaxIncl() / 100,
                    'love' => $model->getLove(),
                    'download' => $model->getNbDownload(),
                    'image' => $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'),
                    'date' => $model->getUpdatedAt(),
                );
                array_push($models_data, $data);
            }
        }
        
        $response = new Response(json_encode($models_data));
        $response->headers->set('Content-Type', 'application/json');

 


        return $response;
    }

    /**
     * @Route("/model/order2", name="api_model_order2")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function modelOrder2AjaxAction(Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {

        /* on récupère la valeur envoyée par la vue */
        $order = $request->request->get('order');
        //$order = "Prix croissant";
        $url = $request->request->get('url');

        $data_list = explode("/", $url);
        //$data_list = explode("/", 'http://localhost:8000/fr/modele/');
        $data_list2 = explode("?form%", $data_list[5]);
        $models_data_null = true;
    

        if(sizeof($data_list) > 4) {
            switch ($data_list2[0]){
                case "":
                    switch ($order){
                        case 'Prix croissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.priceTaxIncl', 'ASC');
                            break;
                        case 'Prix décroissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.priceTaxIncl', 'DESC');
                            break;
                        case 'Remarqués':
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.love', 'DESC');
                            break;
                        case 'Populaires':
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.nb_download', 'DESC');
                            break;
                        default:
                            $models = $entityManager->getRepository('AppBundle:Model')->findModelsValideOrder('model.createdAt', 'DESC');
                    }
                    break;
                case "category":
                    //echo("<script>console.log('PHP: ".$data_list[7]."');</script>");
                    switch ($order){
                        case 'Prix croissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'ASC');
                            break;
                        case 'Prix décroissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'DESC');
                            break;
                        case 'Remarqués':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.love', 'DESC');
                            break;
                        case 'Populaires':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.nb_download', 'DESC');
                            break;
                        default:
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder($data_list[7], 'model.createdAt', 'DESC');
                    }
                    break;
                case "Upcategory":
                    //echo("<script>console.log('PHP: ".$data_list[7]."');</script>");
                    switch ($order){
                        case 'Prix croissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'ASC');
                            break;
                        case 'Prix décroissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'DESC');
                            break;
                        case 'Remarqués':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.love', 'DESC');
                            break;
                        case 'Populaires':
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.nb_download', 'DESC');
                            break;
                        default:
                            $models = $entityManager->getRepository('AppBundle:Model')->findCategoryAllModelsValideOrder($data_list[7], 'model.createdAt', 'DESC');
                    }
                    break;
                case "maker":
                    switch ($order){
                        case 'Prix croissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'ASC');
                            break;
                        case 'Prix décroissant':
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.priceTaxIncl', 'DESC');
                            break;
                        case 'Remarqués':
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.love', 'DESC');
                            break;
                        case 'Populaires':
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.nb_download', 'DESC');
                            break;
                        default:
                            $models = $entityManager->getRepository('AppBundle:Model')->findMakerModelsValideOrder($data_list[7], 'model.createdAt', 'DESC');
                    }
                    break;
                case "search":
                    $data_search_list = preg_split("/search%5D=|&form/", $url);
                    $search_words = $data_search_list[1];
                    $each_word = explode("+", $search_words);

                    $models = array();

                    foreach ($each_word as $word){
                        if(strtoupper($word) == 'GRATUIT') {
                            $models_find = $entityManager->getRepository('AppBundle:Model')->findModelsFreeFromWord($word);
                        } else {
                            $models_find = $entityManager->getRepository('AppBundle:Model')->findModelsFromWord($word);    
                        }
                        
                        $models_with_duplicate = array_merge($models,$models_find);

                        //this line is just to avoid duplicate of model
                        $models = array_unique($models_with_duplicate, SORT_REGULAR);
                    }

                    $models_data = [];
                    
                    foreach ($models as $model){
                        /*
                        $data = array(
                            'id' => $model->getId(),
                            'name' => $model->getName(),
                            'makerName' => $model->getMaker()->getCompany(),
                            'priceTaxIncl' => $model->getPriceTaxIncl() / 100,
                            'love' => $model->getLove(),
                            'download' => $model->getNbDownload(),
                            'image' => $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'),
                            'date' => $model->getCreatedAt(),
                        );
                        */
                        $data = [];
                        $data['id'] = $model->getId();
                        $data['name'] = $model->getName();
                        $data['makerName'] = $model->getMaker()->getCompany();
                        $data['priceTaxIncl'] = $model->getPriceTaxIncl() / 100;
                        $data['love'] = $model->getLove();
                        $data['download'] = $model->getNbDownload();
                        $data['image'] = $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small');
                        $data['date'] = $model->getCreatedAt();
                        array_push($models_data, $data);
                    }

                    foreach ($models_data as $key => $row) {
                        $id[$key]  = $row['id'];
                        $name[$key] = $row['name'];
                        $makerName[$key]  = $row['makerName'];
                        $priceTaxIncl[$key] = $row['priceTaxIncl'];
                        $love[$key]  = $row['love'];
                        $download[$key] = $row['download'];
                        $image[$key] = $row['image'];
                        $date[$key] = $row['date'];
                    }

                    $id  = array_column($models_data, 'id');
                    $name = array_column($models_data, 'name');
                    $makerName = array_column($models_data, 'makerName');
                    $priceTaxIncl = array_column($models_data, 'priceTaxIncl');
                    $love = array_column($models_data, 'love');
                    $download = array_column($models_data, 'download');
                    $image = array_column($models_data, 'image');
                    $date = array_column($models_data, 'date');

                    switch ($order){
                        case 'Prix croissant':
                            array_multisort($priceTaxIncl, SORT_ASC, $date, SORT_DESC, $models_data);
                            break;
                        case 'Prix décroissant':
                            array_multisort($priceTaxIncl, SORT_DESC, $date, SORT_DESC, $models_data);
                            break;
                        case 'Remarqués':
                            array_multisort($love, SORT_DESC, $date, SORT_DESC, $models_data);
                            break;
                        case 'Populaires':
                            array_multisort($download, SORT_DESC, $date, SORT_DESC, $models_data);
                            break;
                        default:
                            array_multisort($date, SORT_DESC, $models_data);
                    }

                    $models_data_null = false;
                    break;
            }
        }

        //$models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder("12", 'model.updatedAt', 'ASC');
        
        //$models = $entityManager->getRepository('AppBundle:Model')->findCategoryModelsValideOrder(6, 'model.createdAt', 'DESC');

        if($models_data_null == true) {
            //$models_data = array();
            $models_data = [];

            foreach ($models as $model){
                /*
                $data = array(
                    'id' => $model->getId(),
                    'name' => $model->getName(),
                    'makerName' => $model->getMaker()->getCompany(),
                    'priceTaxIncl' => $model->getPriceTaxIncl() / 100,
                    'love' => $model->getLove(),
                    'download' => $model->getNbDownload(),
                    'image' => $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'),
                    'date' => $model->getUpdatedAt(),
                );
                array_push($models_data, $data);
                */
                $data = [];
                $data['id'] = $model->getId();
                $data['name'] = $model->getName();
                $data['makerName'] = $model->getMaker()->getCompany();
                $data['priceTaxIncl'] = $model->getPriceTaxIncl() / 100;
                $data['love'] = $model->getLove();
                $data['download'] = $model->getNbDownload();
                $data['image'] = $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small');
                $data['date'] = $model->getCreatedAt();
                array_push($models_data, $data);
            }
        }
        
        $response = new Response(json_encode($models_data));
        $response->headers->set('Content-Type', 'application/json');

 


        return $response;
    }


    /**
     * @Route("/model/basket/add", name="api_model_basket_add")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function basketAddAjaxAction(Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {

        /* on récupère la valeur envoyée par la vue */
        $model_id = $request->request->get('model_id');
        //$model_id = 61;
        
        /** @var Maker $maker */
        $user = $this->getUser();

        $models = $entityManager->getRepository('AppBundle:Model')->findModelsById($model_id);
        $model = $models[0];
        $baskets = $entityManager->getRepository('AppBundle:OrderModelBasket')->findBasketForUser($user);
        
        if (sizeof($baskets) === 0) {
            //$item = 'caca';
            $basket = new OrderModelBasket();
            $letters = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
            $numbers = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $reference = strtoupper($letters) . $numbers;
            $basket->setReference($reference);
            $basket->setCustomer($user);
            // flush
            $em = $this->getDoctrine()->getManager();
            $em->persist($basket);
            $em->flush();
            //$item = $basket->getOrderModelBasketItem()[0]->getModel()->getName();
        } else {
            $basket = $baskets[0];
            //$item = $basket[0]->getOrderModelBasketItem()[0]->getModel()->getName();
        }

        //$items = $entityManager->getRepository('AppBundle:OrderModelBasketItem')->findModelInBasket($model);
        $allItems = $entityManager->getRepository('AppBundle:OrderModelBasketItem')->findAll();

        //if (sizeof($items) === 0) {
            //$item = 'caca';
        $item = new OrderModelBasketItem();
        $item->setModel($model);
        $item->setOrderModelBasket($basket);
        $allreadyExist = false;
        foreach ($allItems as $oneItem) {
            if($oneItem->getModel() === $model && $oneItem->getOrderModelBasket() === $basket) {
                $allreadyExist = true;
            }
        }

        if($allreadyExist === false) {
            // flush
            $em = $this->getDoctrine()->getManager();
            $em->persist($item);
            $em->flush();
        }
        
            //$item = $basket->getOrderModelBasketItem()[0]->getModel()->getName();
        //}

        
        $models_data = array();

        foreach ($models as $model){
            $data = array(
                'user' => $user->getFullname(),
                'basket' => $basket->getReference(),
                'id' => $model->getId(),
                'name' => $model->getName(),
                'makerName' => $model->getMaker()->getCompany(),
                'priceTaxIncl' => $model->getPriceTaxIncl() / 100,
                'love' => $model->getLove(),
                'download' => $model->getNbDownload(),
                'image' => $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'),
                'date' => $model->getUpdatedAt(),
            );
            array_push($models_data, $data);
        }

        $response = new Response(json_encode($models_data));
        $response->headers->set('Content-Type', 'application/json');

 


        return $response;
    }

    /**
     * @Route("/model/basket", name="api_model_basket")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function basketAjaxAction(Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {

        /* on récupère la valeur envoyée par la vue */
        //$model_id = $request->request->get('model_id');
        //$model_id = 61;
        
        $models_data = [];

        /** @var Maker $maker */
        $user = $this->getUser();
        if ($user != null) {
            $baskets = $entityManager->getRepository('AppBundle:OrderModelBasket')->findBasketForUser($user);
            if (sizeof($baskets) > 0) {
                $basket = $baskets[0];
                $items = $entityManager->getRepository('AppBundle:Model')->findAllModelsInBasketForUser($user);
                if (sizeof($items) > 0) {

                    foreach ($items as $model){
                        $data = [];
                        $data['id_model'] = $basket->getId();
                        $data['url'] = str_replace(" ", "%20", $model->getName()).'/'.$model->getId();
                        $data['id'] = $model->getId();
                        $data['name'] = $model->getName();
                        $data['caracteristique'] = $model->getCaracteristique();
                        $data['shortCaracteristique'] = substr($model->getCaracteristique(),0,150);
                        $data['makerName'] = $model->getMaker()->getCompany();
                        $data['priceTaxIncl'] = $model->getPriceTaxIncl() / 100;
                        $data['priceTaxExcl'] = $model->getPriceTaxExcl() / 100;
                        $data['love'] = $model->getLove();
                        $data['download'] = $model->getNbDownload();
                        $data['image'] = $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small');
                        $data['date'] = $model->getUpdatedAt();
                        array_push($models_data, $data);
                        /*
                        $data = array(
                            'id' => $model->getId(),
                            'name' => $model->getName(),
                            'makerName' => $model->getMaker()->getCompany(),
                            'priceTaxIncl' => $model->getPriceTaxIncl() / 100,
                            'love' => $model->getLove(),
                            'download' => $model->getNbDownload(),
                            'image' => $liipImagineCacheManager->getBrowserPath($model->getPortfolioImages()[0]->getPictureName(), 'model_portfolio_small'),
                            'date' => $model->getUpdatedAt(),
                        );
                        array_push($models_data, $data);
                        */
                    }
                }
            }
        }
        //$response = new Response(json_encode($data));
        //$response->headers->set('Content-Type', 'application/json');
        return new JsonResponse(['data' => $models_data ],200);

 


        return $response;
    }

    /**
     * @Route("/model/basket/remove", name="api_model_basket_remove")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function basketRemoveAjaxAction(Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {

        /* on récupère la valeur envoyée par la vue */
        $model_id = $request->request->get('model_id');
        //$model_id = 61;
        
        /** @var Maker $maker */
        $user = $this->getUser();

        $models = $entityManager->getRepository('AppBundle:Model')->findModelsById($model_id);
        $model = $models[0];
        $baskets = $entityManager->getRepository('AppBundle:OrderModelBasket')->findBasketForUser($user);
        $basket = $baskets[0];

        $allItems = $entityManager->getRepository('AppBundle:OrderModelBasketItem')->findAllModelsInBasketForUser($user);
        
        $items = $entityManager->getRepository('AppBundle:OrderModelBasketItem')->findModelInBasket($model);
        $item = $items[0];

        $entityManager->remove($item);
        $entityManager->flush();

        if (sizeof($allItems) === 1) {
            $entityManager->remove($basket);
            $entityManager->flush();
        }
        
        $models_data = array();

        $response = new Response(json_encode($models_data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

     /**
     * @Route("/model/basket/order", name="api_model_basket_order")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function basketOrderAjaxAction(Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {
        // make sure this is an Ajax Request
        
        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse();
        }
        
        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        // get order type
        $basket_id = $requestContent['basket_id'];
        $total_amount_tax_incl = $requestContent['total_amount_tax_incl']*100;
        $total_amount_tax_excl = $requestContent['total_amount_tax_excl']*100;

        //echo("<script>console.log('PHP: ');</script>");
        /** @var User $maker */
        $user = $this->getUser();

        $orderUp = new OrderModelUp();
        /*
        $basket_id = $request->request->get('basket_id');
        $total_amount_tax_incl = $request->request->get('total_amount_tax_incl')*100;
        $total_amount_tax_excl = $request->request->get('total_amount_tax_excl')*100;
        *
        $basket_id = 16;
        $total_amount_tax_incl = 6;
        $total_amount_tax_excl = 5;
        */
        $baskets = $entityManager->getRepository('AppBundle:OrderModelBasket')->findBasketForUser($user);
        $basket = $baskets[0];

        $letters = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $numbers = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        $reference = strtoupper($letters) . $numbers;
        $basket->setReference($reference);

        $orderUp->setCustomer($user);
        $orderUp->setBillingAddress($user->getDefaultBillingAddress());
        $orderUp->setStatus(OrderModelUp::STATUS_MODEL_NOT_PAID);
        $orderUp->setTotalAmountTaxIncl($total_amount_tax_incl);
        $orderUp->setTotalAmountTaxExcl($total_amount_tax_excl);
        $orderUp->setReference($basket->getReference());
        $orderUp->setCommissionRate(0);
        $models_data = array();

        $em = $this->getDoctrine()->getManager();
        $em->persist($orderUp);
        $em->flush();

        if (sizeof($baskets) > 0) {
            $models = $entityManager->getRepository('AppBundle:Model')->findAllModelsInBasketForUser($user);
            if (sizeof($models) > 0) {
                $index = 0;
                foreach ($models as $model) {
                    $ordersForMaker = $entityManager->getRepository('AppBundle:Order')->findOrderFromUpAndMaker($orderUp->getReference(), $model->getMaker());
                    
                    if (sizeof($ordersForMaker) > 0) {
                        $order = $ordersForMaker[0];
                        $order->setTotalAmountTaxIncl($order->getTotalAmountTaxIncl() + $model->getPriceTaxIncl());
                        $order->setTotalAmountTaxExcl($order->getTotalAmountTaxExcl() + $model->getPriceTaxExcl());
                        $order->setProductionAmountTaxIncl($order->getProductionAmountTaxIncl() + $model->getPriceTaxIncl());
                        $order->setProductionAmountTaxExcl($order->getProductionAmountTaxExcl() + $model->getPriceTaxExcl());
                    } else {
                        $index = $index + 1;

                        $order = new Order();
                        $order->setCustomer($user);
                        $order->setMaker($model->getMaker());
                        $order->setStatus(Order::STATUS_MODEL_NOT_PAID);
                        $order->setBillingAddress($user->getDefaultBillingAddress());
                        $order->setShippingAddress($user->getDefaultShippingAddress());
                        $order->setShippingType(Shipping::TYPE_NOT_SHIPPED);
                        $order->setTotalAmountTaxIncl($model->getPriceTaxIncl());
                        $order->setTotalAmountTaxExcl($model->getPriceTaxExcl());
                        $order->setProductionAmountTaxIncl($model->getPriceTaxIncl());
                        $order->setProductionAmountTaxExcl($model->getPriceTaxExcl());
                        $order->setShippingAmountTaxIncl(0);
                        $order->setShippingAmountTaxExcl(0);
                        $order->setFeeAmountTaxIncl(0);
                        $order->setFeeAmountTaxExcl(0);
                        $order->setDiscountAmountTaxIncl(0);
                        $order->setDiscountAmountTaxExcl(0);
                        $order->setReference($basket->getReference()."-".$index);
                        
                        // get commission rate
                        $commissionRate = $entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_COMMISSION_RATE)->getValue();
                        if (null !== $model->getMaker()->getCustomCommissionRate()) {
                            $commissionRate = $model->getMaker()->getCustomCommissionRate();
                        }
                        $order->setCommissionRate($commissionRate);

                        $order->setType('model');
                    }

                    /*
                    $model->setNbDownload($model->getNbDownload() + 1);
                    $em->persist($model);
                    $em->flush();
                    */

                    $em->persist($order);
                    $em->flush();

                    $modelBuy = new ModelBuy();
                    $modelBuy->setCustomer($user);
                    $modelBuy->setModel($model);
                    $modelBuy->setOrder($order);
                    $modelBuy->setOrderModelUp($orderUp);
                    $em->persist($modelBuy);
                    $em->flush();
                }
            }
            /*
            $items = $entityManager->getRepository('AppBundle:OrderModelBasketItem')->findItemsInBasket($basket);
            foreach ($items as $basketItem){
                $entityManager->remove($basketItem);
                $entityManager->flush();
            }
            $entityManager->remove($basket);
            $entityManager->flush();
            */
        }
        //echo($orderUp->getReference());
        $orderUpCreates = $entityManager->getRepository('AppBundle:OrderModelUp')->findOrderFromRef($orderUp->getReference());

        $orderUpCreate = $orderUpCreates[0];

        $response = new Response($orderUpCreate->getId());
        $response->headers->set('Content-Type', 'application/json');
        //return $response;
        return new JsonResponse(json_encode(array('message' => 'Order successfully created', 'order_id' => $orderUpCreate->getId(), 'order_reference' => $orderUpCreate->getReference()), JSON_FORCE_OBJECT), Response::HTTP_OK);
        //return new JsonResponse(array('order_id' => $orderUpCreate->getId()));
        //return new JsonResponse(json_encode($orderUpCreate->getId(), JSON_FORCE_OBJECT), Response::HTTP_OK);
    }


    /**
     * @Route("/model/comment", name="api_model_comment")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function commandAjaxAction(Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {
        /** @var User $maker */
        $user = $this->getUser();
        if($user == null) {
            $UserConnect = false;
        } else {
            $UserConnect = true;
        }
        
        /* on récupère la valeur envoyée par la vue */
        $model_id = $request->request->get('model_id');
        //$model_id = 3;

        $models = $entityManager->getRepository('AppBundle:Model')->findModelsById($model_id);
        $model = $models[0];
        $maker = $model->getMaker();

        $comments = $entityManager->getRepository('AppBundle:ModelComments')->findAllUpCommentsForModel($model);
        
        //$models_data = array();
        $models_data = [];
        
        if (sizeof($comments) > 0) {
            foreach ($comments as $comment){
                $responses = $entityManager->getRepository('AppBundle:ModelComments')->findResponseForModel($comment);
                $comment_response = [];
                foreach ($responses as $response){
                    $allResponse = [];
                    $allResponse['id'] = $response->getId();
                    if($maker == $response->getCustomer()->getMaker()) {
                        $allResponse['customer'] = $maker->getCompany();
                    } else {
                        $allResponse['customer'] = $response->getCustomer()->getFirstname();
                    }
                    $allResponse['description'] = $response->getDescription();
                    $allResponse['createdAt'] = $response->getCreatedAt()->format('d/m/Y');
                    $allResponse['updatedAt'] = $response->getupdatedAt();
                    array_push($comment_response, $allResponse);
                }
                $commentPortfolio = $comment->getPortfolioImages();
                $comment_imgs = [];
                foreach ($commentPortfolio as $img) {
                    $allImg = [];
                    $allImg['linkImage'] = $liipImagineCacheManager->getBrowserPath($img->getPictureName(), 'comment_portfolio');
                    array_push($comment_imgs, $allImg);
                }
                $data = [];
                $data['user'] = $UserConnect;
                $data['id'] = $comment->getId();
                if($maker == $comment->getCustomer()->getMaker()) {
                    $data['customer'] = $maker->getCompany();
                } else {
                    $data['customer'] = $response->getCustomer()->getFirstname();
                }
                $data['description'] = $comment->getDescription();
                $data['createdAt'] = $comment->getCreatedAt()->format('d/m/Y');
                $data['updatedAt'] = $comment->getupdatedAt();
                $data['response'] = $comment_response;
                $data['images'] = $comment_imgs;
                array_push($models_data, $data);
            }
        }

        //$response = new Response(json_encode($data));
        //$response->headers->set('Content-Type', 'application/json');
        return new JsonResponse(['data' => $models_data ],200);

 


        return $response;
    }

    /**
     * @Route("/payment/cancel", name="api_payment_cancel")
     *
     * @param ObjectManager $entityManager
     * @param Request $request
     * @return JsonResponse
     */
    public function modelPaymentCancelAjaxAction(Request $request, ObjectManager $entityManager, CacheManager $liipImagineCacheManager)
    {
        // decode the request JSON data
        $requestContent = json_decode($request->getContent(), true);

        //$orderId = 56;
        $orderId = $requestContent['order_id'];
        $order = $entityManager->getRepository('AppBundle:OrderModelUp')->find($orderId);
        /*
        $orders = $entityManager->getRepository('AppBundle:Order')->findOrderFromUp($order->getReference());
        foreach($orders as $orderInf) {
            //$entityManager->remove($orderInf);
            //$entityManager->flush();
            $orderInf->setStatus(Order::STATUS_CANCELED);
            $entityManager->persist($orderInf);
            $entityManager->flush();
        }
        */
        
        $modelBuy = $entityManager->getRepository('AppBundle:ModelBuy')->findModelFromOrderUp($order);
        foreach($modelBuy as $model_buy) {
            $entityManager->remove($model_buy);
            $entityManager->flush();
        }
        //$entityManager->remove($order);
        //$entityManager->flush();
        
        /*
        $order->setStatus(Order::STATUS_CANCELED);
        $entityManager->persist($order);
        $entityManager->flush();
        */

        /** @var User $maker */
        /*
        $user = $this->getUser();
        $baskets = $entityManager->getRepository('AppBundle:OrderModelBasket')->findBasketForUser($user);
        $basket = $baskets[0];

        $letters = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $numbers = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        $reference = strtoupper($letters) . $numbers;
        
        $basket->setReference($reference);
        $entityManager->persist($basket);
        $entityManager->flush();
        */
            
    

        //$response = new Response(json_encode($data));
        //$response->headers->set('Content-Type', 'application/json');
        return new JsonResponse(['data' => $orderId ]);

    }
}
