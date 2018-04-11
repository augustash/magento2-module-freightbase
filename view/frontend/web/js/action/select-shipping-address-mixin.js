/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (selectShippingAddressAction) {
        return wrapper.wrap(selectShippingAddressAction, function (originalAction, shippingAddress) {
            
            // If Delivery Details empty, set to null
            // If not removed causes a javascript error from empty value
            // 
            // If delivery_type values are set
            // Manipulate from array to comma seperated string, otherwise Magento errors
            // Other values are passed as arrays, so I have no idea why- just got it done.
            if(shippingAddress.customAttributes
               && shippingAddress.customAttributes.delivery_type.length == 0
            ) {
                shippingAddress.customAttributes.delivery_type = null;
            } else if (shippingAddress.customAttributes) {
                var customAttributes = shippingAddress.customAttributes.delivery_type,
                    values = '';

                $.each(customAttributes, function(index, value) {
                    values += value;

                    if(customAttributes.length - 1 != index) {
                        values += ' ';
                    }
                });
                // Set new values to delivery_type
                shippingAddress.customAttributes.delivery_type = values;
            }
            return originalAction(shippingAddress);
        });
    };
});
