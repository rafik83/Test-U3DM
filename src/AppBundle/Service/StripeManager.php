<?php
 
namespace AppBundle\Service;
 
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderModelUp;
use AppBundle\Entity\Maker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\Account;
use Stripe\Transfer;
use Stripe\Payout;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Error\Base;
use Stripe\Stripe;
use Stripe\Card;
 
class StripeManager
{
    private $config;
    private $em;
    private $logger;

    public function __construct($secretKey,$stripeVersion, array $config, EntityManagerInterface $em, LoggerInterface $logger)
    {
        Stripe::setApiKey($secretKey);
        Stripe::setApiVersion($stripeVersion);
        $this->config = $config;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @see https://stripe.com/docs/api/php#create_charge
     *
     * @param Order $order
     * @param $token
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createOrderCharge(Order $order, $token, $customer = null)
    {
        try {
            $charge = Charge::create([
                'amount'      => $order->getTotalAmountTaxIncl(),
                'currency'    => $this->config['currency'],
                'description' => $order->getReference(),
                'source'      => $token,
                'customer' => $customer,
            ]);
        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating a Stripe charge: "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $charge;
    }

    /**
     * @see https://stripe.com/docs/api/php#create_charge
     *
     * @param OrderModelUp $order
     * @param $token
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createOrderUpCharge(OrderModelUp $order, $token = null, $customer = null)
    {
        try {
            $charge = Charge::create([
                'amount'      => $order->getTotalAmountTaxIncl(),
                'currency'    => $this->config['currency'],
                'description' => $order->getReference(),
                'source'      => $token,
                'customer' => $customer,
            ]);
        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating a Stripe charge: "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $charge;
    }

    /**
     * @see https://stripe.com/docs/api/php#create_refund
     *
     * @param $chargeId
     * @param $amount
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createRefundCharge($chargeId, $amount)
    {
        try {
            $refund = Refund::create([
                'charge'      => $chargeId,
                'amount'    => $amount//
            ]);
        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when refunding client : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $refund;
    }

   /**
     * @see https://stripe.com/docs/api#create_account
     *
     * @param Maker $maker
     * @param $currency
     * @param $iban
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createAccount(Maker $maker,$currency,$iban)
    {
        try {
            $account = Account::create([
                'type' => "custom",
                'country' => substr($iban, 0, 2),
                'email' => $maker->getUser()->getEmail(),
                'business_profile' => array(
                    'url' => $maker->getWebSite()
                ),
                'requested_capabilities' => ['transfers'],
                /*'business_type' => $maker->getUser()->getType(),*/
                'business_type' => 'company',
                'company' => array(
                    'address' => array(
                        'city' => $maker->getAddress()->getCity(),
                        'country' => $maker->getAddress()->getCountry(),
                        'line1' => $maker->getAddress()->getStreet1(),
                        'line2' => $maker->getAddress()->getStreet2(),
                        'postal_code' => $maker->getAddress()->getZipcode()
                    ),
                    'name' => $maker->getAddress()->getCompany(),
                    'phone' => $maker->getAddress()->getTelephone(),
                    'tax_id' => $maker->getSiren(),
                    'vat_id' => $maker->getVatNumber()
                ),
                'tos_acceptance' => array(
                    'date' => time(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ),
                'external_account' => array(
                    "object" => "bank_account",
                    "country" => substr($iban, 0, 2),
                    "currency" => $currency,
                    "account_number" => $iban
                ),
                "settings" => array (
                    "payouts" => array(
                        "schedule" => array (
                            "interval" => "monthly",
                            "monthly_anchor" => 5
                        )
                    )
                )   
            ]);
        } catch (Base $e) {
            $this->logger->error(sprintf('%s Erreur rencontré lors de la création du compte maker dans Stripe : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $account;
    }
   /**
     * @see https://stripe.com/docs/api#create_account
     *
     * @param Maker $maker
     * @param $currency
     * @param $iban
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function updateAccountForStripeEvol(Maker $maker)
    {
        try {
            $account = Account::update($maker->getStripeId(),[
                'email' => $maker->getUser()->getEmail(),
                'requested_capabilities' => ['transfers'],
                'business_type' => 'company',
                'company' => array(
                    'address' => array(
                        'city' => $maker->getAddress()->getCity(),
                        'country' => $maker->getAddress()->getCountry(),
                        'line1' => $maker->getAddress()->getStreet1(),
                        'line2' => $maker->getAddress()->getStreet2(),
                        'postal_code' => $maker->getAddress()->getZipcode()
                    ),
                    'name' => $maker->getAddress()->getCompany(),
                    'phone' => $maker->getAddress()->getTelephone(),
                    'tax_id' => $maker->getSiren(),
                    'vat_id' => $maker->getVatNumber()
                ) 
            ]);
        } catch (Base $e) {
            $this->logger->error(sprintf('%s Erreur rencontré lors de la mise à jour du compte maker dans Stripe : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            return false;
        }
        return true;
    }
    /**
     * @see https://stripe.com/docs/api#retrieve_account
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function getAccount($stripeId)
    {
        try {
            $account = Account::retrieve($stripeId);
        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when getting account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $account;
    }

   /**
     * @see https://stripe.com/docs/api/persons
     *
     * @param Maker $maker
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createPersonRepresentative(Maker $maker)
    {
        try {

            $dateTime = $maker->getBirthDate();

            $day = $dateTime->format('d');
            $month = $dateTime->format('m');
            $year = $dateTime->format('Y');
            $accountId = $maker->getStripeId();

            $this->logger->info(sprintf('API STRIPE CREATE PERSON - avant Liste existant : %s',$accountId));
            // Verify if a representant already exist.¨
            $listPerson = Account::allPersons($accountId);
            //$listPerson = this.getListPerson($accountId);
            $this->logger->info(sprintf('API STRIPE CREATE PERSON - Liste existant : : "%s"', $listPerson));
            
            if ($listPerson->data == null) {
                $person = Account::createPerson($accountId,[
                    'address' => array(
                        'city' => $maker->getAddress()->getCity(),
                        'country' => $maker->getAddress()->getCountry(),
                        'line1' => $maker->getAddress()->getStreet1(),
                        'line2' => $maker->getAddress()->getStreet2(),
                        'postal_code' => $maker->getAddress()->getZipcode()
                    ),
                    'dob' => array(
                        'day' => $day,
                        'month' => $month,
                        'year' => $year
                    ),
                    'email' => $maker->getUser()->getEmail(),
                    'first_name' => $maker->getFirstname(),
                    'last_name' => $maker->getLastname(),
                    //'phone' => $maker->getAddress()->getTelephone(),
                    'relationship' => array(
                        'owner' => true,
                        'percent_ownership' => '25',
                        'representative' => true
                    )
                ]);
            }else {
                // Retrieve the person
                $person = Account::retrievePerson($accountId,$listPerson->data[0]['id']);
            }
        } catch (Base $e) {
            $this->logger->error(sprintf('%s erreur rencontré lors de la creation du representant : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }

        return $person;
    }

        /**
     * @see https://stripe.com/docs/api/persons/retrieve
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function getPersonRepresentative($stripeId,$StripePersonId)
    {
        try {
            $person = Account::retrievePerson($stripeId,$StripePersonId);
        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when getting representative maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $person;
    }

        /**
     * @see https://stripe.com/docs/api/persons/retrieve
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function getListPerson($stripeId)
    {
        try {
            $listPerson = Account::allPersons($stripeId);
        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when getting list of person of account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $listPerson;
    }



    /**
     * @see https://stripe.com/docs/api/php#update_account
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function updateAccountWithIdentity($stripeId,$stripePersonId,$documentId, $documentVersoId)
    {
        try {
            $person = Account::retrievePerson($stripeId,$stripePersonId);
            $person->verification->document->front = $documentId;
            $person->verification->document->back = $documentVersoId;
            $person->verification->additional_document->front = $documentVersoId;
            $person->save();


        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when update account maker with Identity Paper : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $person;
    }

    /**
     * @see https://stripe.com/docs/api#account_retrieve_bank_account
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createBankAccount($stripeId,$iban,$currency)
    {
        try {
            $account = Account::retrieve($stripeId);
            $account->external_accounts->create(
                array(
                        "external_account" => array(
                        "object" => "bank_account",
                        "account_number" => $iban,
                        "country" => substr($iban, 0, 2),
                        "currency" => $currency,
                        "default_for_currency" => true,
                        )
                    )
            );

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating bank account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $account;
    }

    /**
     * @see https://stripe.com/docs/api#update_account
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function updateBankAccount($stripeId,$bankAccountId,$iban,$currency)
    {
        try {
            $account = Account::retrieve($stripeId);
            $account->external_accounts->create(
                array(
                        "external_account" => array(
                        "object" => "bank_account",
                        "account_number" => $iban,
                        "country" => substr($iban, 0, 2),
                        "currency" => $currency,
                        )
                    )
            );

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating bank account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $account;
    }

    /**
     * @see https://stripe.com/docs/api#account_retrieve_bank_account
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function getBankAccount($stripeId,$stripeBankAccountId)
    {
        try {
            $account = Account::retrieve($stripeId);
            $bank_account = $account->external_accounts->retrieve($stripeBankAccountId);

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when getting bank account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $bank_account;
    }

    /**
     * @see https://stripe.com/docs/api#account_delete_bank_account
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function deleteBankAccount($stripeId,$stripeBankAccountId)
    {
        try {
            $account = Account::retrieve($stripeId);
            $account->external_accounts->retrieve($stripeBankAccountId)->delete();

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when deleting bank account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $account;
    }

    /**
     * @see https://stripe.com/docs/api#create_transfer
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createTransfer($amount,$currency,$makerStripeId,$orderId)
    {
        try {
            $transfer = Transfer::create(
                array(
                    "amount" => $amount,
                    "currency" => $currency,
                    "destination" => $makerStripeId,
                    "description" => $orderId,
                    "transfer_group" => $orderId
                )
            );

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when transfer to account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $transfer;
    }


    /**
     * @see https://stripe.com/docs/api#create_transfer
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createPayoutBankAccount($amount,$currency,$makerStripeId,$makerStripeBankId,$orderId)
    {
        try {
            $payout=\Stripe\Payout::create(
                array(
                  "amount" => $amount,
                  "currency" => $currency,
                  "destination"=> $makerStripeBankId,
                  "description" => $orderId,
                  "source_type"=>'bank_account'
                ),
                array(
                    "stripe_account" => $makerStripeId
                )
            );

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when transfer to account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $payout;
    }

    /**
     * @see https://stripe.com/docs/api/files/create
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     * Possible values are business_logo, customer_signature, dispute_evidence, identity_document, pci_document, or tax_document_user_upload
     */
    public function createDocument($filePath, $documentType = 'identity_document')
    {
        try {

            $fp = fopen($filePath, 'r');

            $document = \Stripe\File::create(
                array(
                  'purpose' => $documentType,
                  'file' => $fp
                )
            );

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating document for maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $document;
    }

    /**
     * @see https://stripe.com/docs/api/php#create_customer
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createCustomer($user,$tokenStripe,$paymentMethod = null)
    {
        try {

            $customer = \Stripe\Customer::create(array(
              'description' => $user->getFirstname().' '.$user->getLastname(),
              'email' => $user->getemail(),
              //'source' => $tokenStripe, // obtained with Stripe.js
              //'payment_method' => $paymentMethod,
              'shipping' => array(
                'address' => array(
                    'city' => $user->getDefaultShippingAddress()->getCity(),
                    'country' => $user->getDefaultShippingAddress()->getCountry(),
                    'line1' => $user->getDefaultShippingAddress()->getStreet1(),
                    'line2' => $user->getDefaultShippingAddress()->getStreet2(),
                    'postal_code' => $user->getDefaultShippingAddress()->getZipcode()
                ),
                'name' => $user->getFirstname().' '.$user->getLastname(),
              ),
            ));

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating Customer : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $customer;
    }

    /**
     * @see https://stripe.com/docs/api/php#create_card
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function AddCustomerCard($user,$tokenStripe)
    {
        try {

            $customer = \Stripe\Customer::retrieve($user->getStripeCustomerId());
            $card = $customer->sources->create(array('source' => $tokenStripe));


        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when adding credit card : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $card;
    }


    /**
     * @see https://stripe.com/docs/payments/payment-intents/creating-payment-intents#creating-for-automatic
     *
     * @param $amount
     * @param $payment_method_id
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function createPaymentIntent($amount,$payment_method_id = null,$customer)
    {
        try {
            $intent = \Stripe\PaymentIntent::create([
                'payment_method' => $payment_method_id,
                'amount' => round($amount,0),
                'currency' => 'eur',
                'customer' => $customer,
                'save_payment_method'=>true,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when Payment Intent Create : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $intent;
    }

    /**
     * @see https://stripe.com/docs/payments/payment-intents/creating-payment-intents#creating-for-automatic
     *
     * @param $paymentIntentId
     * @param $amount
     * @param $description
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function updatePaymentIntent($paymentIntentId,$description = null,$customer = null)
    {
        try {

            $intent = \Stripe\PaymentIntent::update(
                $paymentIntentId,
                [
                    'description' => $description,
                    //'save_payment_method' => true
                    //'customer' => $customer
                ]
            );

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when Payment Intent Update : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        return $intent;
    }

    /**
     * @see https://stripe.com/docs/payments/payment-intents/creating-payment-intents#creating-for-automatic
     *
     * @param $paymentIntentId
     * @param $amount
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function confirmPaymentIntent($paymentIntentId)
    {
        try {

            $intent = \Stripe\PaymentIntent::retrieve(
                $paymentIntentId
            );
            // In case of 3d Secure, we must confirm payment.
            if ($intent->status == 'requires_confirmation') {
                $intent->confirm();
            }

        } catch (Base $e) {
            /*$this->logger->error(sprintf('%s exception encountered when Payment Intent Update : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;*/
        }
        return $intent;
    }

    /**
     * @see https://stripe.com/docs/payments/payment-intents/creating-payment-intents#creating-for-automatic
     *
     * @param $paymentIntentId
     * @param $amount
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function savingCardPaymentIntent($intent,$customer)
    {
        try {

            $paymentMethod = \Stripe\PaymentMethod::retrieve($intent->payment_method);
            $paymentMethod->attach(['customer' => $customer]);

        } catch (Base $e) {
            /*$this->logger->error(sprintf('%s exception encountered when Saving Card Payment : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;*/
        }
        return $paymentMethod;
    }

    /**
     * @see https://stripe.com/docs/payments/payment-intents/creating-payment-intents#creating-for-automatic
     *
     * @param $paymentIntentId
     * @param $amount
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function cancelPaymentIntent($paymentIntentId)
    {
        try {
            $this->logger->info(sprintf('StripeManager : CancelPayment N°:%s',$paymentIntentId));
            $intent = \Stripe\PaymentIntent::retrieve(
                $paymentIntentId
            );
            // In case of 3d Secure, we must confirm payment. 
            $intent->cancel(['cancellation_reason' => 'abandoned']);
            

        } catch (Base $e) {
            /*$this->logger->error(sprintf('%s exception encountered when Payment Intent Update : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;*/
        }
        return $intent;
    }

 /**
     * @see Specific U3DM
     *
     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function getRequirements($stripeId)
    {
        try {
            $account = Account::retrieve($stripeId);
            $required = $account->requirements ;
            if ($required != null) {
                if  ($account->requirements->error != null) {
                    $required->status = 'Stripe.Account.Status.Error';
                    $required->colorAlert = 'red';
                }else if  ($account->requirements->disabled_reason != null) {
                    $required->status = "Stripe.Account.Status.Limited";
                    $required->colorAlert = 'orange';
                } else {
                    $required->status = "Stripe.Account.Status.Active";
                    $required->colorAlert = 'green';
                }
                $required->dataDue = $account->requirements->currently_due;

            } else {
                $required = new \stdClass();
                $required->status = "Stripe.Account.Status.None";
                $required->colorAlert = 'gray';
                $required->dataDue = null;
                
            }
            if ($account->company != null and $account->company->directors_provided != null) {
                $required->director  = $account->company->directors_provided ;
            }else {
                $required->director  = false;
            }
            if ($account->company != null and $account->company->executives_provided != null) {
                $required->executive  = $account->company->executives_provided ;
            }else {
                $required->executive  = false;
            }

        } catch (Base $e) {
            $this->logger->error(sprintf('%s exception encountered when getting account maker : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }


        return $required ;
    }

   /**
     * @see https://stripe.com/docs/api#create_account
     *
     * @param  $accountStripeId
     * @param $typeRelationShip

     * @return \Stripe\ApiResource
     * @throws \Stripe\Error\Base
     */
    public function updateRelationShip( $stripeId, $typeRelationShip)
    {
        try {
            if ($typeRelationShip == "Director" ){
                $account = Account::update($stripeId, ['company' =>  array('directors_provided' => true)]);
            }
            if ($typeRelationShip == "Executive" ){
                $account = Account::update($stripeId,['company' =>  array('executives_provided' => true)]);
            }
        } catch (Base $e) {
            $this->logger->error(sprintf('%s Erreur rencontré lors de la mise à jour du compte maker dans Stripe : "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            return false;
        }
        return true;
    }

}
