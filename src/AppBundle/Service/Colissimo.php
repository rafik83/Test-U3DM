<?php

namespace AppBundle\Service;

use AppBundle\Entity\Address;
use AppBundle\Entity\Setting;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Colissimo Service
 *
 * @see https://www.colissimo.entreprise.laposte.fr/fr/edito-doc-technique
 */
class Colissimo
{
    /**
     * Constants
     */
    const WS_SLS_BASE_URL = 'https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/';
    const WS_TRACKING_BASE_URL = 'https://www.coliposte.fr/tracking-chargeur-cxf/TrackingServiceWS/track';

    /**
     * @var string
     */
    private $contractNumber;

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
     * @param string $contractNumber
     * @param string $password
     * @param ObjectManager $entityManager
     */
    public function __construct($contractNumber, $password, ObjectManager $entityManager)
    {
        $this->contractNumber = $contractNumber;
        $this->password = $password;
        $this->entityManager = $entityManager;
        $this->httpClient = new Client();
    }

    /**
     * Call generateLabel SLS method
     *
     * @param Address $address
     * @return mixed
     * @throws \Exception
     */
    public function generateLabel(Address $address)
    {
        $response = null;
        $request = new Request(
            'POST',
            self::WS_SLS_BASE_URL . 'generateLabel',
            array('Content-Type' => 'application/json'),
            $this->createGenerateLabelJsonBody($address)
        );
        try {
            $response = $this->httpClient->send($request);
        } catch (\Exception $exception) {
            throw $exception;
        }
        return $response;
    }

    /**
     * Get the generated label parcel number
     *
     * @param ResponseInterface $generateLabelResponse
     * @return string|null
     */
    public function getGenerateLabelParcelNumber(ResponseInterface $generateLabelResponse)
    {
        $result = null;
        $parsedResponse = $this->parseGenerateLabelResponse($generateLabelResponse);
        if (isset($parsedResponse['labelResponse']['parcelNumber'])) {
            $result = $parsedResponse['labelResponse']['parcelNumber'];
        }
        return $result;
    }

    /**
     * Get the generated label PDF URL
     *
     * @param ResponseInterface $generateLabelResponse
     * @return string|null
     */
    public function getGenerateLabelPdfUrl(ResponseInterface $generateLabelResponse)
    {
        $result = null;
        $parsedResponse = $this->parseGenerateLabelResponse($generateLabelResponse);
        if (isset($parsedResponse['labelResponse']['pdfUrl'])) {
            $result = $parsedResponse['labelResponse']['pdfUrl'];
        }
        return $result;
    }

    /**
     * Create the generateLabel SLS method JSON request body
     *
     * @param Address $address
     * @return array
     */
    private function createGenerateLabelJsonBody(Address $address)
    {
        $body = array();

        $body['contractNumber'] = $this->contractNumber;
        $body['password'] = $this->password;

        $body['outputFormat']['x'] = 0;
        $body['outputFormat']['y'] = 0;
        $body['outputFormat']['outputPrintingType'] = 'PDF_10x15_300dpi';

        // product info
        $body['letter']['service']['productCode'] = 'DOS';
        $body['letter']['service']['depositDate'] = date('Y-m-d');
        $body['letter']['parcel']['weight'] = $this->entityManager->getRepository('AppBundle:Setting')->findOneByKey(Setting::DEFAULT_SHIPPING_WEIGHT)->getValue();

        // sender address
        $body['letter']['sender']['address']['companyName'] = 'United 3D Makers';
        $body['letter']['sender']['address']['line2'] = '78 rue du Sergent Bobillot';
        $body['letter']['sender']['address']['city'] = 'Montreuil';
        $body['letter']['sender']['address']['zipCode'] = '93100';
        $body['letter']['sender']['address']['countryCode'] = 'FR';

        // recipient address
        if (null !== $address->getCompany()) {
            $body['letter']['addressee']['address']['companyName'] = $address->getCompany();
        }
        $body['letter']['addressee']['address']['lastName'] = $address->getLastname();
        $body['letter']['addressee']['address']['firstName'] = $address->getFirstname();
        $body['letter']['addressee']['address']['line2'] = $address->getStreet1();
        if (null !== $address->getStreet2()) {
            $body['letter']['addressee']['address']['line3'] = $address->getStreet2();
        }
        $body['letter']['addressee']['address']['city'] = $address->getCity();
        $body['letter']['addressee']['address']['zipCode'] = $address->getZipcode();
        $body['letter']['addressee']['address']['countryCode'] = $address->getCountry();

        return json_encode($body, JSON_FORCE_OBJECT);
    }

    /**
     * Parse the generateLabel response.
     * The response contains several parts, separated by "--uuid:" lines.
     * The first part contains the JSON response, the second contains the PDF as binary ; each has headers.
     * We need to get the first part and ignore its headers by just looking for the JSON content.
     *
     * Example of response:
     *
     * ------------
     *
     * --uuid:0d4830d7-b16b-44b1-97c2-108859dc1858
     * Content-Type: application/json;charset=UTF-8
     * Content-Transfer-Encoding: binary
     * Content-ID: <jsonInfos>
     *
     * {"messages":[{"id":"0","type":"INFOS","messageContent":"La requête a été traitée avec succès","replacementValues":[]}],"labelXmlReponse":null,"labelResponse":{"parcelNumber":"6C13534659422","parcelNumberPartner":"0075001116C1353465942802250T","pdfUrl":"https://ws.colissimo.fr/sls-ws/GetLabel?parcelNumber=6C13534659422&includeCustomsDeclarations=false&x=0&y=0&signature=7e4159b40d6fb8e0433aab46dc95754d5fc6d6f7f6da7b1d3dd47637c0a3768e&preuveDepot="}}
     * --uuid:0d4830d7-b16b-44b1-97c2-108859dc1858
     * Content-Type: application/octet-stream
     * Content-Transfer-Encoding: binary
     * Content-ID: <label>
     *
     * <the binary file>
     *
     * ------------
     *
     * What we need is only:
     *
     * {"messages":[{"id":"0","type":"INFOS","messageContent":"La requête a été traitée avec succès","replacementValues":[]}],"labelXmlReponse":null,"labelResponse":{"parcelNumber":"6C13534659422","parcelNumberPartner":"0075001116C1353465942802250T","pdfUrl":"https://ws.colissimo.fr/sls-ws/GetLabel?parcelNumber=6C13534659422&includeCustomsDeclarations=false&x=0&y=0&signature=7e4159b40d6fb8e0433aab46dc95754d5fc6d6f7f6da7b1d3dd47637c0a3768e&preuveDepot="}}
     *
     * ------------
     *
     * @param ResponseInterface $generateLabelResponse
     * @return array : decoded JSON as an associative array
     */
    private function parseGenerateLabelResponse(ResponseInterface $generateLabelResponse)
    {
        // explode the response and keep the first part (index 0 is empty)
        $parts = explode('--uuid:', $generateLabelResponse->getBody());
        $jsonPart = $parts[1];

        // look for the JSON contained in the first part
        // @see https://stackoverflow.com/questions/21994677/find-json-strings-in-a-string
        $jsonPattern = '
            /
            \{              # { character
                (?:         # non-capturing group
                    [^{}]   # anything that is not a { or }
                    |       # OR
                    (?R)    # recurses the entire pattern
                )*          # previous group zero or more times
            \}              # } character
            /x
            '
        ;
        preg_match_all($jsonPattern, $jsonPart, $jsons);
        $json = $jsons[0][0];

        // return the decoded JSON as an associative array
        return json_decode($json, true);
    }

    /**
     * Track a parcel, and return its current tracking event code.
     *
     * Response example:
     * <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
     *   <soap:Body>
     *     <ns1:trackResponse xmlns:ns1="http://chargeur.tracking.geopost.com/">
     *       <return>
     *         <errorCode>0</errorCode>
     *         <eventCode>COMCFM</eventCode>
     *         <eventDate>2018-06-11T17:26:46+02:00</eventDate>
     *         <eventLibelle>Votre colis est prêt à être expédié, il va être remis à La Poste.</eventLibelle>
     *         <eventSite></eventSite>
     *         <recipientCity>Paris</recipientCity>
     *         <recipientCountryCode>FR</recipientCountryCode>
     *         <recipientZipCode>75008</recipientZipCode>
     *         <skybillNumber>6C13534660442</skybillNumber>
     *       </return>
     *     </ns1:trackResponse>
     *   </soap:Body>
     * </soap:Envelope>
     *
     * @param string $parcelNumber
     * @return bool|string : false if any error occured, else the current tracking event code
     */
    public function track($parcelNumber)
    {
        // create GET request
        $request  = self::WS_TRACKING_BASE_URL;
        $request .= '?accountNumber='.$this->contractNumber;
        $request .= '&password='.$this->password;
        $request .= '&skybillNumber='.$parcelNumber;

        // execute request
        $response = $this->httpClient->get($request);

        // parse XML
        $xml = new \SimpleXMLElement($response->getBody()->getContents());
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns1', 'http://chargeur.tracking.geopost.com/');
        $responseContent = $xml->xpath('//ns1:trackResponse')[0];

        // make sure there is no error
        $errorCode = $responseContent->xpath('//errorCode')[0];
        if ('0' !== $errorCode->__toString()) {
            return false;
        }

        // return eventCode
        $eventCode = $responseContent->xpath('//eventCode')[0];
        return $eventCode->__toString();
    }
}