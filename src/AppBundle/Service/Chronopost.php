<?php

namespace AppBundle\Service;

use AppBundle\Entity\Address;
use AppBundle\Entity\Setting;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Client;

/**
 * Chronopost Service
 */
class Chronopost
{
    /**
     * Constants
     */
    const WS_RELAY_BASE_URL    = 'https://ws.chronopost.fr/recherchebt-ws-cxf/PointRelaisServiceWS/recherchePointChronopost';
    const WS_LABEL_BASE_URL    = 'https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS/shippingWithReservationAndESDWithRefClientPC';
    const WS_TRACKING_BASE_URL = 'https://ws.chronopost.fr/tracking-cxf/TrackingServiceWS/trackSkybillV2';
    const PRODUCT_CHRONO13       = '01';
    const PRODUCT_CHRONORELAIS13 = '86';

    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var string
     */
    private $password;

    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var Client
     */
    private $httpClient;


    /**
     * Colissimo constructor
     *
     * @param string $accountNumber
     * @param string $password
     * @param ObjectManager $entityManager
     */
    public function __construct($accountNumber, $password, ObjectManager $entityManager)
    {
        $this->accountNumber = $accountNumber;
        $this->password = $password;
        $this->entityManager = $entityManager;
        $this->httpClient = new Client();
    }

    /**
     * Call web service to get relay points list
     *
     * GET Request example:
     * https://ws.chronopost.fr/recherchebt-ws-cxf/PointRelaisServiceWS/recherchePointChronopost?accountNumber=XXXXXXXX&password=XXXXXX&address=10%20rue%20de%20la%20paix&zipCode=75001&city=Paris%20&countryCode=FR&type=P&productCode=86&service=L&weight=1000&shippingDate=25/05/2018&%20maxPointChronopost=10&maxDistanceSearch=40&holidayTolerant=1&language=FR
     *
     * @see Chronopost Documentation v2.5.14 (November 2017), §3.4.4 (page 38)
     *
     * @param Address $address
     * @return string JSON
     */
    public function getRelays(Address $address)
    {
        $relays = array();

        // get default weight setting and convert to grams
        $defaultWeight = 1000 * $this->entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_SHIPPING_WEIGHT)->getValue();

        // create GET request
        $request  = self::WS_RELAY_BASE_URL;
        $request .= '?accountNumber=' . $this->accountNumber;
        $request .= '&password=' . $this->password;
        $request .= '&address=' . urlencode($address->getStreet1());
        $request .= '&zipCode=' . urlencode($address->getZipcode());
        $request .= '&city=' . urlencode($address->getCity());
        $request .= '&countryCode=' . urlencode($address->getCountry());
        $request .= '&type=P';
        $request .= '&productCode=' . self::PRODUCT_CHRONORELAIS13;
        $request .= '&service=L';
        $request .= '&weight=' . $defaultWeight;// in grams
        $request .= '&shippingDate=' . date('d/m/Y');
        $request .= '&maxPointChronopost=10';// max value: 25
        $request .= '&maxDistanceSearch=10';// max value: 40 (in km)
        $request .= '&holidayTolerant=1';
        $request .= '&language=FR';

        // execute request
        $response = $this->httpClient->get($request);

        // parse XML
        $xml = new \SimpleXMLElement($response->getBody()->getContents());
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns1', 'http://cxf.rechercheBt.soap.chronopost.fr/');
        $responseContent = $xml->xpath('//ns1:recherchePointChronopostResponse')[0];

        // make sure there is no error
        $errorCode = $responseContent->xpath('//errorCode')[0];
        if ('0' === $errorCode->__toString()) {
            $relayNumber = 1;
            // get all relays
            foreach ($responseContent->xpath('//listePointRelais') as $relayXml) {
                // we have to call __toString() method on each SimpleXMLElement object to get a proper string
                $relay = array();
                $relay['name'] = $relayXml->nom->__toString();
                $relay['number'] = (string) $relayNumber;
                $relay['street1'] = $relayXml->adresse1->__toString();
                $relay['street2'] = $relayXml->adresse2->__toString();
                $relay['zipcode'] = $relayXml->codePostal->__toString();
                $relay['city'] = $relayXml->localite->__toString();
                $relay['distance'] = $relayXml->distanceEnMetre->__toString();// in meters
                $relay['identifier'] = $relayXml->identifiant->__toString();// will be useful when generating a label
                $relay['coordinates'] = array('lat' => floatval($relayXml->coordGeolocalisationLatitude->__toString()), 'lng' => floatval($relayXml->coordGeolocalisationLongitude->__toString()));
                $relayOpening = array();
                foreach ($relayXml->listeHoraireOuverture as $opening) {
                    $openingDayCode = $opening->jour->__toString();
                    $relayOpening[$openingDayCode] = $this->getReadableRelayOpeningDay($openingDayCode) . ' : ' . $opening->horairesAsString->__toString();
                }
                ksort($relayOpening);// sort by ascending day
                $relay['opening'] = array_values($relayOpening);// remove unnecessary array keys
                $relays[] = $relay;
                $relayNumber++;
            }
        }

        // return JSON
        return json_encode($relays);
    }

    /**
     * @param string $dayCode
     * @return string
     */
    private function getReadableRelayOpeningDay($dayCode)
    {
        $result = '';
        switch ($dayCode) {
            case '1':
                $result = 'Lundi';
                break;
            case '2':
                $result = 'Mardi';
                break;
            case '3':
                $result = 'Mercredi';
                break;
            case '4':
                $result = 'Jeudi';
                break;
            case '5':
                $result = 'Vendredi';
                break;
            case '6':
                $result = 'Samedi';
                break;
            case '7':
                $result = 'Dimanche';
                break;
        }
        return $result;
    }

    /**
     * Call web service to generate a label, and return the parcel number and the label PDF URL
     *
     * @see Chronopost Documentation v2.5.14 (November 2017), §2.7.4 (page 22) for a call example
     * @see Chronopost Documentation v2.5.14 (November 2017), §3.12.4 (page 60) for request format
     *
     * @param Address $address
     * @param bool $relay
     * @param string $relayIdentifier
     * @return array('parcelNumber' => xxx, 'pdfUrl' => xxx)
     */
    public function generateLabel(Address $address, $relay = false, $relayIdentifier = null) {

        // create GET request
        $request  = self::WS_LABEL_BASE_URL;
        $request .= '?accountNumber=' . $this->accountNumber;
        $request .= '&password=' . $this->password;

        // shipper address
        $request .= '&shipperCivility=M';
        $request .= '&shipperName=' . urlencode('United 3D Makers');
        $request .= '&shipperAdress1=' . urlencode('78 rue du Sergent Bobillot');
        $request .= '&shipperZipCode=93100';
        $request .= '&shipperCity=Montreuil';
        $request .= '&shipperCountry=FR';

        // recipient address (may be a relay)
        if (null !== $address->getCompany()) {
            $request .= '&recipientName=' . urlencode(strtoupper($address->getCompany()));
            $request .= '&recipientName2=' . urlencode(strtoupper($address->getLastname()) . ' ' . $address->getFirstname());
        } else {
            $request .= '&recipientName=' . urlencode(strtoupper($address->getLastname()) . ' ' . $address->getFirstname());
        }
        $request .= '&recipientAdress1=' . urlencode($address->getStreet1());
        if (null !== $address->getStreet2() && '' !== $address->getStreet2()) {
            $request .= '&recipientAdress2=' . urlencode($address->getStreet2());
        }
        $request .= '&recipientZipCode=' . urlencode($address->getZipcode());
        $request .= '&recipientCity=' . urlencode($address->getCity());
        $request .= '&recipientCountry=' . urlencode($address->getCountry());

        // handle relay
        if (true === $relay) {
            $request .= '&productCode=' . self::PRODUCT_CHRONORELAIS13;
            if (null !== $relayIdentifier) {
                $request .= '&recipientRef=' . urlencode($relayIdentifier);
            }
        } else {
            $request .= '&productCode=' . self::PRODUCT_CHRONO13;
        }

        // technical fields
        $request .= '&weight=' . $this->entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_SHIPPING_WEIGHT)->getValue();
        $request .= '&shipDate=' . urlencode(date('d/m/Y H:i:s'));
        $request .= '&shipHour=' . date('H');
        $request .= '&service=0';
        $request .= '&objectType=MAR';
        $request .= '&modeRetour=2';

        // execute request
        $response = $this->httpClient->get($request);

        // parse XML
        $xml = new \SimpleXMLElement($response->getBody()->getContents());
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns1', 'http://cxf.shipping.soap.chronopost.fr/');
        $responseContent = $xml->xpath('//ns1:shippingWithReservationAndESDWithRefClientPCResponse')[0];

        // make sure there is no error
        $errorCode = $responseContent->xpath('//errorCode')[0];
        $parcelNumber = 'Erreur';
        $reservationNumber = 'error';
        if ('0' === $errorCode->__toString()) {
            $parcelNumber = $responseContent->xpath('//skybillNumber')[0];
            $reservationNumber = $responseContent->xpath('//reservationNumber')[0];
        }

        return array(
            'parcelNumber' => $parcelNumber,
            'pdfUrl'       => 'https://ws.chronopost.fr/shipping-cxf/getReservedSkybill?reservationNumber=' . $reservationNumber
        );
    }

    /**
     * Get all the event codes the shipment encountered.
     *
     * @param string $parcelNumber
     * @return string[] : array of Chronopost event codes (from the list of codes provided by Chronopost docs)
     */
    public function track($parcelNumber)
    {
        $trackingCodes = array();

        // create GET request
        $request  = self::WS_TRACKING_BASE_URL;
        $request .= '?language=fr_FR';
        $request .= '&skybillNumber=' . $parcelNumber;

        // execute request
        $response = $this->httpClient->get($request);

        // parse XML
        $xml = new \SimpleXMLElement($response->getBody()->getContents());
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns1', 'http://cxf.tracking.soap.chronopost.fr/');
        $responseContent = $xml->xpath('//ns1:trackSkybillV2Response')[0];

        // make sure there is no error
        $errorCode = $responseContent->xpath('//errorCode')[0];
        if ('0' === $errorCode->__toString()) {
            $codes = $xml->xpath('//code');
            foreach($codes as $code) {
                $trackingCodes[] = trim($code->__toString());
            }
        }

        return $trackingCodes;
    }
}