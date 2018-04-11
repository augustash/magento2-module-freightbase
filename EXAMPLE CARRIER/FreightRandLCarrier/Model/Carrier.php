<?php
/**
 * @category Augustash FreightRandLCarrier
 * @package Augustash_FreightRandLCarrier
 * @copyright Copyright (c) 2017 Augustash
 * @author Augustash Team <changes@augustash.com>
 */

namespace Augustash\FreightRandLCarrier\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

/**
 * R&L shipping implementation
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const RATE_SERVICE_URL = 'http://api.rlcarriers.com/1.0.1/RateQuoteService.asmx?WSDL';

    /**
     * @var string
     */
    protected $_code = 'freight';

    /**
     * @var object
     */
    protected $quotes;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \Augustash\FreightBase\Helper\Data
     */
    protected $helper;

    /**
     * @var \Augustash\FreightRandLCarrier\Helper\Data
     */
    protected $randLHelper;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Webapi\Soap\ClientFactory
     */
    protected $clientFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface,
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory,
     * @param \Psr\Log\LoggerInterface,
     * @param \Magento\Shipping\Model\Rate\ResultFactory,
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory,
     * @param \Augustash\FreightBase\Helper\Data,
     * @param \Augustash\FreightRandLCarrier\Helper\Data,
     * @param \Augustash\Framework\Encryption\EncryptorInterface,
     * @param \Magento\Framework\Webapi\Soap\ClientFactory,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Augustash\FreightBase\Helper\Data $helper,
        \Augustash\FreightRandLCarrier\Helper\Data $randLHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->helper = $helper;
        $this->randLHelper = $randLHelper;
        $this->encryptor = $encryptor;
        $this->clientFactory = $clientFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Get allowed shipping methods
     *
     * @return  array
     */
    public function getAllowedMethods()
    {
        return ['freight' => $this->getConfigData('name')];
    }

    /**
     * Collect and get rates
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Framework\DataObject|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if(!$this->getConfigData('active')
           || !$this->helper->isFreight($request)) {
            return false;
        }

        // Setup api object
        $baseRequest = $this->randLHelper->setBaseRequest($request);

        $apiKey = $this->encryptor->decrypt($this->scopeConfig->getValue('carriers/freight_shipping/credentials_group/api_key'));
        
        // Set api key to object
        $baseRequest->setApiKey($apiKey);

        $this->quotes = $baseRequest;

        // Api quote request
        $shippingQuote = $this->getShippingQuote($request);

        if($shippingQuote) {
            $shippingMethods = $this->getShippingRates($shippingQuote);

            if($shippingMethods) {

                /** @var Result $result */
                $result = $this->_rateResultFactory->create();

                foreach($shippingMethods[0] as $shippingMethod) {
                    // Remove api provided '$' from string
                    $price = ltrim($shippingMethod->NetCharge, '$');

                    /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                    $method = $this->_rateMethodFactory->create();

                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));

                    $method->setMethod($shippingMethod->Code);

                    if(isset($shippingMethod->HourlyWindow)) {
                        $method->setMethodTitle($shippingMethod->Title
                        . ': Between ' .
                        $shippingMethod->HourlyWindow->Start
                        . ' - ' .
                        $shippingMethod->HourlyWindow->End);
                    } else {
                        $method->setMethodTitle($shippingMethod->Title);
                    }

                    $method->setPrice($price);
                    $method->setCost($price);

                    $result->append($method);
                }
                return $result;
            }
        }
        return;
    }

    protected function getShippingQuote($request)
    {
        $quoteRequest = $this->quotes;
        $quoteType = '';
        $specialStates = ['AK','HI'];
        $prCountry = 'PR';

        // Only runs after shipping information entered
        // Strings are capitalized specifically for api - do not change
        if($quoteRequest->getDestRegionCode() && $quoteRequest->getDestCountry()) {
            $destRegion = $quoteRequest->getDestRegionCode();
            $destCountry = $quoteRequest->getDestCountry();

            switch($destRegion) {
                case in_array($destRegion, $specialStates):
                    $quoteType = 'AlaskaHawaii';
                    break;

                case $prCountry == $destCountry:
                    $quoteType = 'International';
                    break;

                default:
                    $quoteType = 'Domestic';
                    break;
            }

            // Request with defaults
            // Reference: http://api.rlcarriers.com/1.0.1/RateQuoteService.asmx?WSDL
            $ratesRequest = [
                'APIKey'        => $quoteRequest->getApiKey(),
                'request'       => [
                    'QuoteType'         =>  $quoteType,
                    'CODAmount'         => 0.0, // decimal
                    'Origin'            =>  [
                        'City'              => $quoteRequest->getOrigCity(),
                        'StateOrProvince'   => $quoteRequest->getOrigRegionCode(),
                        'ZipOrPostalCode'   => $quoteRequest->getOrigPostal(),
                        'CountryCode'       => $quoteRequest->getOrigCountryIso3(),
                    ],
                    'Destination'       => [
                        'City'              => $quoteRequest->getDestCity(),
                        'StateOrProvince'   => $destRegion,
                        'ZipOrPostalCode'   => $quoteRequest->getDestPostal(),
                        'CountryCode'       => $quoteRequest->getDestCountryIso3(),
                    ],
                    'Items' => [],
                    'DeclaredValue' => 0.0, // decimal
                    'Accessorials' => [],
                    'OverDimensionPcs' => 0 // int
                ]
            ];

            $itemsApi = $this->randLHelper->addApiRequestBase();

            // Order items added to api request
            $ratesRequest['request']['Items'] = $itemsApi[0];
            $ratesRequest['request']['DeclaredValue'] = $itemsApi[1];

            // Get Augustash\FreightBase extension attributes
            // With additional accessorials information
            $addressAttributes = json_decode(file_get_contents('php://input'), true);

            // Accessorials added to request
            if($quoteRequest->getAccessories()
               || $addressAttributes['address']['custom_attributes']
            )
            {
                $freightAttributes = $addressAttributes['address']['custom_attributes'];

                // Set address accessories to quote request
                $quoteRequest->setAddressAccessories($freightAttributes);

                if(!empty($this->randLHelper->addAccessories($quoteRequest, $request))
                   || !empty($quoteRequest->getAddressAccessories())
                  ){
                    $accessorialsApi = $this->randLHelper->addAccessories($quoteRequest, $request);

                    $ratesRequest['request']['Accessorials'] = $accessorialsApi;
                }
            }

            $response = '';

            // Api request
            try {
                // Create request
                $client = $this->clientFactory->create(self::RATE_SERVICE_URL);

                return $client->GetRateQuote($ratesRequest);
            } catch (Exception $e) {
                $this->logger->debug('R&L Api failure: ', [$e]);
            }
        }
    }

    /**
     *
     * Capture shipping method options
     * 
     * @param object $shippingMethods
     * @return array 
     */
    protected function getShippingRates($shippingRates)
    {
        if(!$shippingRates->GetRateQuoteResult->WasSuccess) {
            return false;
        }

        $messages = $shippingRates->GetRateQuoteResult->Result->Messages;
        $shippingRates = $shippingRates->GetRateQuoteResult->Result->ServiceLevels->ServiceLevel;


        return [$shippingRates, $messages];
    }
}
