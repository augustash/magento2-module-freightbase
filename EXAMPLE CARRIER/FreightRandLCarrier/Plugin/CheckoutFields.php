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
	) {
		$customAttributeCode = 'delivery_type';

		$customFieldAuthenticated = [
			'component' => 'Magento_Ui/js/form/element/checkbox-set',
			'config' => [
				'customScope' => 'shippingAddress.custom_attributes',
				'template' => 'ui/form/field',
				'elementTmpl' => 'ui/form/element/checkbox-set',
				'customEntry' => null
			],
			'dataScope' => 'shippingAddress.custom_attributes.' . $customAttributeCode,
			'multiple' => true,
			'label' => 'Freight Specifics',
			'provider' => 'checkoutProvider',
			'sortOrder' => 1000,
			'comment' => 'Shipping prices will update on changes, after a slight pause.',
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
			'visible' => true,
			'customEntry' => null,
			'filterBy' => null
		];

		// This is dirty but you can't use __construct in plugins
		// Allows us to check if a freight item is in the cart in order to display field
		$helper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Augustash\FreightBase\Helper\Data::class);
		$session = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Checkout\Model\Session::class)->getQuote();
		
		if ($helper->isEnabled() && $helper->isFreight($session) && $helper->isCustomer()) {
				$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['before-shipping-method-form']['children'][$customAttributeCode] = $customFieldAuthenticated;
		}
		return $jsLayout;
	}
}
