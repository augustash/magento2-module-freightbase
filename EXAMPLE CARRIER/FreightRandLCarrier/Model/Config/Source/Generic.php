<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Augustash\FreightRandLCarrier\Model\Config\Source;

use Magento\Shipping\Model\Carrier\Source\GenericInterface;

/**
 * Generic source model
 */
class Generic implements GenericInterface
{
    /**
     * @var \Augustash\FreightRandLCarrier\Helper\Config
     */
    protected $carrierConfig;

    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = '';

    /**
     * @param \Augustash\FreightRandLCarrier\Helper\Config $carrierConfig
     */
    public function __construct(\Augustash\FreightRandLCarrier\Helper\Config $carrierConfig)
    {
        $this->carrierConfig = $carrierConfig;
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        $configData = $this->carrierConfig->getCode($this->_code);

        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => __($title)];
        }
        return $arr;
    }
}
