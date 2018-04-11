<?php
/**
 * @category Augustash FreightRandLCarrier
 * @package Augustash_FreightRandLCarrier
 * @copyright Copyright (c) 2017 Augustash
 * @author Augustash Team <changes@augustash.com>
 */

namespace Augustash\FreightRandLCarrier\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const USA_COUNTRY_ID = 'US';

    const PUERTORICO_COUNTRY_ID = 'PR';

    const GUAM_COUNTRY_ID = 'GU';

    const GUAM_REGION_CODE = 'GU';

    const ORIGIN_LIFTGATE = 'shipping/freight_base/origin/origin_liftgate';

    const ORIGIN_RESIDENTIAL = 'shipping/freight_base/origin/origin_residential';

    /**
    * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
    */
    protected $countryInfo;

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Checkout\Model\Session
    */
    protected $checkoutSession;

    /**
    * @var float
    */
    protected $fees = 0;

    /**
    * @param \Magento\Framework\App\Helper\Context,
    * @param \Magento\Directory\Api\CountryInformationAcquirerInterface,
    * @param \Magento\Framework\App\Config\ScopeConfigInterface,
    * @param \Magento\Checkout\Model\Session
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInfo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->countryInfo = $countryInfo;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function setBaseRequest($request)
    {
        $requestObject = new \Magento\Framework\DataObject();

        if ($request->getLimitMethod()) {
            $requestObject->setService($request->getLimitMethod());
        } else {
          $requestObject->setService('ALL');
        }

        // Limiting to specific R&L shipping methods? Unsure at this point.
        //$requestObject->setAllowedMethods($this->scopeConfig->getValue('allowed_methods'));

        // Charge liftgate only
        if($request->getChargeLiftgateOnly()) {
            $requestObject->setChargeLiftgateOnly($request->getChargeLiftgateOnly());    
        }

        /**
        * Shipping Origin Definitions
        */

        // Origin and destination country two letter codes
        // Used for looking up directory object information
        $originCountry = $request->getCountryId();

        // Set Origin Country two letter code
        $requestObject->setOrigCountry(
            $this->countryInfo->getCountryInfo($originCountry)->getId()
        );

        // Set Origin Country three letter code
        $requestObject->setOrigCountryIso3(
            $this->countryInfo->getCountryInfo($originCountry)->getThreeLetterAbbreviation()
        );

        // Set state/region code - example: 'CA' is code for California
        if ($request->getRegionId()) {
            $originRegion = $request->getRegionId();
            
            if (is_numeric($originRegion)) {
                // Fetch available states/regions within country
                $codes = $this->countryInfo->getCountryInfo($originCountry)->getAvailableRegions();

                // State codes are sequential id, however array starts at 0, offsetting array keys from id values by -1
                // This allows us not to loop, while providing accurate data
                /** @var \Magento\Directory\Api\Data\RegionInformationInterface */
                $originRegion = $codes[$originRegion - 1]->getCode();
            }
            $requestObject->setOrigRegionCode($originRegion);
        }

        // Origin postcode
        if ($request->getPostcode()) {
            $requestObject->setOrigPostal($request->getPostcode());
        }

        // Origin city
        if ($request->getCity()) {
            $requestObject->setOrigCity($request->getCity());
        }

        
        /**
        * Shipping Destination Definitions
        */
       
        // Destination country id
        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        }

        // For UPS, puero rico state for US will assume as puerto rico country
        if ($request->getDestRegionCode() && $request->getDestRegionCode() == self::PUERTORICO_COUNTRY_ID) {
            $destCountry = self::USA_COUNTRY_ID;
        }

        // For UPS, Guam state of the USA will be represented by Guam country
        if ($request->getDestRegionCode() && $request->getDestRegionCode() == self::GUAM_REGION_CODE) {
            $destCountry = self::GUAM_REGION_CODE;
        }

        // Destination country id
        $requestObject->setDestCountry(
            $this->countryInfo->getCountryInfo($destCountry)->getId()
        );

        // Destination country three alphanumeric
        $requestObject->setDestCountryIso3(
            $this->countryInfo->getCountryInfo($originCountry)->getThreeLetterAbbreviation()
        );

        // Destination region code
        if($request->getDestRegionCode()) {
            $requestObject->setDestRegionCode($request->getDestRegionCode());
        }

        // Destination city
        if($request->getDestCity()) {
            $requestObject->setDestCity($request->getDestCity());
        }

        // Destination postal code
        if ($request->getDestPostcode()) {
            $requestObject->setDestPostal('US' == $requestObject->getDestCountry() ? substr($request->getDestPostcode(), 0, 5) : $request->getDestPostcode());
        }

        // Package value
        if($request->getPackageValue()) {
            $requestObject->setValue($request->getPackageValue());
        }
        
        // Order base subtotal with tax
        if($request->getBaseSubtotalInclTax()) {
            $requestObject->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());
        }

        // Package value with discount
        if($request->getPackageValueWithDiscount()) {
            $requestObject->setValueWithDiscount($request->getPackageValueWithDiscount());
        }

        /**
        * Configuration values used for shipping fee calculations
        */

        /**
        * Flat fees
        */

        // Admin level accessories
        $accessories = [];

        if($this->scopeConfig->getValue(self::ORIGIN_LIFTGATE)) {
            $accessories[] = 'OriginLiftgate';
        }

        if($this->scopeConfig->getValue(self::ORIGIN_RESIDENTIAL)) {
            $accessories[] = 'ResidentialPickup';
        }

        $requestObject->setAccessories($accessories);

        return $requestObject;
    }

    /**
     * Add freight product line items
     * Meaning items that must ship freight only are requested from api
     * 
     * @param $quoteRequest
     * @param $request
     * @return array [items, declaredvalue]
     */
    public function addApiRequestBase()
    { 
        $items = $this->checkoutSession->getQuote()->getAllItems();
        $wrapper = [];
        $wrapper['Items'] = [];
        $wrapper['DeclaredValue'] = 0;

        foreach($items as $item => $data) {
            $product = $data->getProduct();

            // Only add to api request if product is freight shippable
            // Class is required in order to ship freight
            // 
            // ***
            // SPECS MISSING IN API REQUIREMENTS:
            // Weight MUST be greater than 1, or no results will be returned!
            // * Their system does not apply a default weight
            // ***
            if($product->getFreightClass()) {
                $wrapper['Items'][$item] = [
                    'Class' => $product->getFreightClass(),
                    'Weight' => empty($product->getWeight()) ? 1 : $product->getWeight(),
                    'Width' => empty($product->getFreightWidth()) ? 0 : $product->getFreightWidth(),
                    'Height' => empty($product->getFreightHeight()) ? 0 : $product->getFreightHeight(),
                    'Length' => empty($product->getFreightLength()) ? 0 : $product->getFreightLength()
                ];
            }

            // Return declared value
            // type: float
            $wrapper['DeclaredValue'] = $wrapper['DeclaredValue'] + $product->getDeclaredValue();
        }
        return [$wrapper['Items'], $wrapper['DeclaredValue']];
    }

    /**
     * Accessorial charges
     * @param $quoteRequest
     * @param $request
     */
    public function addAccessories($quoteRequest, $request)
    {
        // Configuration:
        //  - origin_liftgate
        //  - origin_residential
        $accSettings = $quoteRequest->getAccessories();

        // Product Level:
        //  - is_hazmat
        //  - is_freezable
        //  - is_not_freezable
        //  - is_sort_segregate
        //  - is_over_dimension
        $productAccessories = [];

        // This is where I'm at, fetching and passing product level freight shipping attributes
        // 
        // loop over get all items to capture values
        foreach($request->getAllItems() as $items) {
            $product = $items->getProduct();

            // All below values are api specific
            // Do not change capitalization
            // http://api.rlcarriers.com/1.0.1/RateQuoteService.asmx?WSDL
            if($product->getIsHazmat() == 1) {
                array_push($productAccessories, 'Hazmat');
            }

            if($product->getIsFreezable() == 1) {
                array_push($productAccessories, 'Freezable');
            }

            if($product->getIsNotFreezable() == 1) {
                array_push($productAccessories, 'KeepFromFreezing');   
            }

            if($product->getIsSortSegregate() == 1) {
                array_push($productAccessories, 'SortAndSegregate');
            }

            if($product->getIsOverDimensions() == 1) {
                array_push($productAccessories, 'OverDimension');
            }
        }

        // Remove any duplicates from the array since we're looping over possilby multiple products
        $productAccessories = array_unique($productAccessories);

        // Checkout Level:
        //  - InsideDelivery
        //  - ResidentialDelivery
        //  - DestinationLiftgate
        //  - DeliveryNotification
        //  - DockDelivery
        //  - AirportDelivery
        //  - LimitedAccessDelivery
        $checkoutAccessories = $quoteRequest->getAddressAccessories();

        if($checkoutAccessories['delivery_type'] != null) {
            $checkoutAccessories = explode(' ', $checkoutAccessories['delivery_type']);

            // Merge configuration/product/checkout/ arrays for submission to api
            $accSettings = array_merge($accSettings, $productAccessories, $checkoutAccessories);
        }

        return $accSettings;
    }
}