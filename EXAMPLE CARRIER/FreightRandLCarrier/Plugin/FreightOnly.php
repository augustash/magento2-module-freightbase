<?php
/**
 * @category Augustash FreightBase
 * @package Augustash_FreightBase
 * @copyright Copyright (c) 2018 Augustash
 * @author Augustash Team <changes@augustash.com>
 */

namespace Augustash\FreightRandLCarrier\Plugin;

class FreightOnly
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     *  Checks when methods are collected
     *  Removed specified shipping options
     * 
     * @param  Magento\Shipping\Model\Rate\Result
     * @return string|array
     */
    public function afterGetAllRates($subject, $methods)
    {
        // Load current cart items
        $items = $this->checkoutSession->getQuote()->getAllItems();
        $itemsWithFreight = false;

        // Check cart items for a freight item
        foreach($items as $item) {
            $product = $item->getProduct();

            if($product->getData('must_ship_freight')) {
                $itemsWithFreight = true;
            }
        }

        // Remove non-freight shipping methods
        if($itemsWithFreight) {
            if (count($methods) > 1) {
                foreach($methods as $key => $method) {
                    if($method->getData('carrier') != 'freight') {
                        unset($methods[$key]);
                    }
                }
            }
        }
        return $methods;
    }
}
