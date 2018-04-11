<?php
/**
 * @category Augustash FreightBase
 * @package Augustash_FreightBase
 * @copyright Copyright (c) 2017 Augustash
 * @author Augustash Team <changes@augustash.com>
 */

namespace Augustash\FreightBase\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
    * @param \Magento\Framework\App\Helper\Context
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest
     * @return bool
     */
    public function isFreight($request) {
        foreach($request->getAllItems() as $item) {
            $product = $item->getProduct();

            if($product->getMustShipFreight()) {
                return true;
                break;
            }
        }
        return false;
    }
}