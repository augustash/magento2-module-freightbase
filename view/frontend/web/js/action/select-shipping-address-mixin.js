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

            if (shippingAddress.customAttributes === undefined) {
                shippingAddress.customAttributes = {};
            }

            // If Delivery Details empty, set to null
            // If not removed causes a javascript error from empty value
            // 
            // If deliveryType values are set
            // Manipulate from array to comma seperated string, otherwise Magento errors
            // Other values are passed as arrays, so I have no idea why- just got it done.
            var values = ''

            if (shippingAddress.customAttributes.deliveryType
                && shippingAddress.customAttributes.deliveryType.length != 0)
            {
                var customAttributes = shippingAddress.customAttributes.deliveryType;

                if(customAttributes.length != 0) {
                    $.each(customAttributes, function(index, value) {
                        values += value;

                        if(customAttributes.length - 1 != index) {
                            values += ' ';
                        }
                    });

                    // Set new values to deliveryType
                    shippingAddress.customAttributes.deliveryType = values;
                }

                // See rebuildAddressObject() defined below for explination.
                if(window.customerData.id) {
                    var newAddress = rebuildAddressObject(shippingAddress);

                    newAddress.customAttributes.deliveryType = values;

                    return originalAction(newAddress);
                }
            } else {
                // React differently depending on if customer is logged in.
                // Must be done or errors are thrown... who knows.
                if (shippingAddress.customerAddressId) {
                    shippingAddress.customAttributes.deliveryType = '';
                } else {
                    shippingAddress.customAttributes.deliveryType = null;
                }
            }
            return originalAction(shippingAddress);
        });
    };

    /**
     * If a customer is logged in/
     * When shipping estimation is refired on Accessory change/
     * ShippingAddress object is reset to empty values/
     *
     * Why? I don't know.
     *
     * Recreate the object.
     * Set the deliveryType.
     * Pass to server side.
     */
    function rebuildAddressObject(shippingAddress) {
        var customerData = window.customerData,
            rebuild = shippingAddress;

        rebuild.city = customerData.addresses[0].city;
        rebuild.customerId = customerData.id;
        rebuild.email = customerData.email;
        rebuild.postcode = customerData.addresses[0].postcode;
        rebuild.region = customerData.addresses[0].region.region;
        rebuild.regionCode = customerData.addresses[0].region.region_code;
        rebuild.regionId = customerData.addresses[0].region_id;
        rebuild.street = customerData.addresses[0].street;
        rebuild.telephone = customerData.addresses[0].telephone;

        return rebuild;
    }
});
