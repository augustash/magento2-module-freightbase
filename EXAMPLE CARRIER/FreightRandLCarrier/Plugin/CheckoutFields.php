<?php
/**
 * @category Augustash FreightRandLCarrier
 * @package Augustash_FreightRandLCarrier
 * @copyright Copyright (c) 2018 Augustash
 * @author Augustash Team <changes@augustash.com>
 */

namespace Augustash\FreightRandLCarrier\Plugin;

class CheckoutFields
{
	/**
     * Process js Layout of block
     * \Magento\Checkout\Block\Checkout\LayoutProcessor
     *
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
    	\Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    )
    {
    	$customAttributeCode = 'delivery_type';

		$customField = [
		    'component' => 'Magento_Ui/js/form/element/checkbox-set',
		    'displayArea' => 'additional-fieldsets',
		    'config' => [
		        'customScope' => 'shippingAddress',
		        'template' => 'ui/form/field',
		        'elementTmpl' => 'ui/form/element/checkbox-set'
		    ],
		    'dataScope' => 'shippingAddress.custom_attributes.'. $customAttributeCode,
		    'multiple' => true,
		    'label' => 'Freight Specifics',
		    'provider' => 'checkoutProvider',
		    'deps' => 'checkoutProvider',
		    'sortOrder' => 150,
		    'options' => [
		    	[
		    		'value' => 'InsideDelivery',
		    		'label' => 'Inside Delivery'
		    	],
		    	[
		    		'value' => 'ResidentialDelivery',
		    		'label' => 'Residential Delivery'
		    	],
		    	[
		    		'value' => 'DestinationLiftgate',
		    		'label' => 'Liftgate Required'
		    	],
		    	[
		    		'value' => 'DeliveryNotification',
		    		'label' => 'Delivery Notification Required'
		    	],
		    	[
		    		'value' => 'DockDelivery',
		    		'label' => 'Dock Delivery'
		    	],
		    	[
		    		'value' => 'AirportDelivery',
		    		'label' => 'Airport Delivery'
		    	],
		    	[
		    		'value' => 'LimitedAccessDelivery',
		    		'label' => 'Scheduled Delivery Required'
		    	]
		    ],
		    'visible' => true
		];

		// This is dirty but you can't use __construct in plugins
		// Allows us to check if a freight item is in the cart in order to display field
    	$helper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Augustash\FreightBase\Helper\Data::class);
    	$session = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Checkout\Model\Session::class)->getQuote();

		// If cart contains freight items, add field
		if($helper->isFreight($session)) {
			$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $customField;
		}

    	return $jsLayout;
    }
}