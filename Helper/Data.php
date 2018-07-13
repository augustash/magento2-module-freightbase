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
     * Customer Session
     *
     * @var session
     */
    protected $session;

    /**
    * @param \Magento\Framework\App\Helper\Context
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $session
    )
    {
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled() {
        return $this->scopeConfig->getValue('carriers/freight/active');
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

    public function isCustomer() {
        return $this->session->isLoggedIn();
    }
}