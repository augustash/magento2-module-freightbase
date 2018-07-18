<?php

/**
 * @category Augustash FreightRandLCarrier
 * @package Augustash_FreightRandLCarrier
 * @copyright Copyright (c) 2017 Augustash
 * @author Augustash Team <changes@augustash.com>
 */

namespace Augustash\FreightRandLCarrier\Model;
 
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Simplexml\Element;
use Augustash\FreightRandLCarrier\Helper\Config;
use Magento\Framework\Xml\Security;
 
 
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    const RATE_SERVICE_URL = 'http://api.rlcarriers.com/1.0.2/RateQuoteService.asmx?WSDL';

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'freight';

    /**
     * Array of quotes
     *
     * @var array
     */
    protected static $_quotesCache = [];

    /**
    *
    * @var bool
    */
    protected $_isFixed = false;

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var string
     */
    protected $_localeFormat;

    /**
     * @var string
     */
    protected $configHelper;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var object
     */
    protected $quotes;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

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
     * @param \Magento\Customer\Api\AddressRepositoryInterface,
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface,
     * @param \Magento\Checkout\Model\Session,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Augustash\FreightBase\Helper\Data $helper,
        \Augustash\FreightRandLCarrier\Helper\Data $randLHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Checkout\Model\Session $session,
        Config $configHelper,
        array $data = []
    ) {
        $this->_localeFormat = $localeFormat;
        $this->configHelper = $configHelper;
        $this->scopeConfig = $scopeConfig;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->helper = $helper;
        $this->randLHelper = $randLHelper;
        $this->encryptor = $encryptor;
        $this->clientFactory = $clientFactory;
        $this->addressRepository = $addressRepository;
        $this->session = $session;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateResultFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }
 
    /**
     * Get allowed shipping methods
     *
     * @return  array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
     
    /**
     * Collect and get rates
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Framework\DataObject|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigData('active')
            || !$this->helper->isFreight($request)) {
            return false;
        }

        if (!$request->getLimitCarrier()) {
            // Setup api object
            $baseRequest = $this->randLHelper->setBaseRequest($request);

            // $apiKey = $this->encryptor->decrypt($this->scopeConfig->getValue('carriers/freight_shipping/credentials_group/api_key'));
            // SWITCH BACK

            $apiKey = 'gtNmYjgTQ4MlU4MTgtN2M5ZC00OGMwLWJhYTMWFiJzZmMzVWC';

            // Set api key to object
            $baseRequest->setApiKey($apiKey);

            $this->quotes = $baseRequest;

            // Api quote request
            $shippingQuote = $this->getShippingQuote($request);

            if ($shippingQuote) {
                $shippingMethods = $this->getShippingRates($shippingQuote);

                if ($shippingMethods) {

                    /** @var Result $result */
                    $result = $this->_rateResultFactory->create();

                    foreach ($shippingMethods[0] as $shippingMethod) {

                        // Remove api provided '$' from string
                        $price = ltrim($shippingMethod->NetCharge, '$');

                        // * @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method 
                        $method = $this->_rateMethodFactory->create();

                        $method->setCarrier($this->_code);
                        $method->setCarrierTitle($this->getConfigData('title'));

                        $method->setMethod($shippingMethod->Code);

                        if (isset($shippingMethod->HourlyWindow)) {
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
        } 
        else {
            // I can't think this is right
            // But for now it works
            // I couldn't figure out how to capture chosen shipping options
            // So I get the post data below, set values to session and move on

            // Runs when next button is clicked in shipping method step
            $post = json_decode(file_get_contents('php://input'), true);

            // Retreive values and set to session
            // If customer changes methods they will update
            // Session will clear on cart success
            // 
            // On payment step, addressInformation is not set
            // So this only runs after shipping step, before payment activity
            if (isset($post['addressInformation'])) {
                $customAttributes = $post['addressInformation']['shipping_address']['customAttributes'];

                $this->session->setShippingPrice($customAttributes['shipping_method_price']);
                $this->session->setShippingTitle($customAttributes['shipping_method_title']);
                $this->session->setShippingMethod($customAttributes['shipping_method_code']);
            }

            $result = $this->_rateResultFactory->create();
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->session->getShippingMethod());
            $method->setMethodTitle($this->session->getShippingTitle());

            $method->setPrice($this->session->getShippingPrice());
            $method->setCost($this->session->getShippingPrice());

            $result->append($method);

            return $result;
        }
    }

    protected function getShippingQuote($request)
    {
        $quoteRequest = $this->quotes;
        $quoteType = '';
        $specialStates = ['AK', 'HI'];
        $prCountry = 'PR';

        // Only runs after shipping information entered
        // Strings are capitalized specifically for api - do not change
        if ($quoteRequest->getDestRegionCode() && $quoteRequest->getDestCountry()) {
            $destRegion = $quoteRequest->getDestRegionCode();
            $destCountry = $quoteRequest->getDestCountry();

            switch ($destRegion) {
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

            // Get Augustash\FreightBase extension attributes
            // With additional accessorials information
            $addressAttributes = json_decode(file_get_contents('php://input'), true);

            // Request with defaults
            // Reference: http://api.rlcarriers.com/1.0.2/RateQuoteService.asmx?WSDL
            if (isset($addressAttributes['addressId'])){
                $address = $this->addressRepository->getById($addressAttributes['addressId']);

                $ratesRequest = [
                    'APIKey' => $quoteRequest->getApiKey(),
                    'request' => [
                        'QuoteType' => $quoteType,
                        'CODAmount' => 0.0, // decimal
                        'Origin' => [
                            'City' => $quoteRequest->getOrigCity(),
                            'StateOrProvince' => $quoteRequest->getOrigRegionCode(),
                            'ZipOrPostalCode' => $quoteRequest->getOrigPostal(),
                            'CountryCode' => $quoteRequest->getOrigCountryIso3(),
                        ],
                        'Destination' => [
                            'City' => $address->getCity(),
                            'StateOrProvince' => $address->getRegion()->getRegionCode(),
                            'ZipOrPostalCode' => $address->getPostcode(),
                            'CountryCode' => $address->getCountryId(),
                        ],
                        'Items' => [],
                        'DeclaredValue' => 0.0, // decimal
                        'Accessorials' => [],
                        'OverDimensionPcs' => 0, // int
                        'Pallets' => []
                    ]
                ];

                $itemsApi = $this->randLHelper->addApiRequestBase();

                // Order items added to api request
                $ratesRequest['request']['Items'] = $itemsApi[0];
                $ratesRequest['request']['DeclaredValue'] = $itemsApi[1];

                // Accessorials added to request
                if ($quoteRequest->getAccessories()
                    || (isset($addressAttributes['deliveryType'])
                    && !empty($addressAttributes['deliveryType']))
                )
                {

                    $freightAttributes = explode(',', $addressAttributes['deliveryType']);

                    // Set address accessories to quote request
                    $quoteRequest->setAddressAccessories($freightAttributes);

                    if (!empty($this->randLHelper->addAccessories($quoteRequest, $request))
                        || !empty($quoteRequest->getAddressAccessories()))
                    {
                        $accessorialsApi = $this->randLHelper->addAccessories($quoteRequest, $request);

                        $ratesRequest['request']['Accessorials'] = $accessorialsApi;
                    }
                }

                // Api request
                // Create request
                $client = $this->clientFactory->create(self::RATE_SERVICE_URL);

                return $client->GetRateQuote($ratesRequest);
            }
        }
    }

    /**
     *
     * Capture shipping method options
     *
     * @param object $shippingMethods
     * @return array|bool
     */
    protected function getShippingRates($shippingRates)
    {
        if (!$shippingRates->GetRateQuoteResult->WasSuccess) {
            return false;
        }

        $messages = $shippingRates->GetRateQuoteResult->Result->Messages;
        $shippingRates = $shippingRates->GetRateQuoteResult->Result->ServiceLevels->ServiceLevel;

        return [$shippingRates, $messages];
    }
     
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request) {
        return true;
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request) {}
}