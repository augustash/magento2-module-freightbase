/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global alert*/
define([
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/error-processor'
],
function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor) {
    'use strict';

    // Overwrite original method in order to pass additional information with address
    // Added 'deliveryType' pass within storage.post() call.
    var mixin = {
        /**
         * @param {Object} address
         */
        getRates: function (address) {
            var cache,
                deliveryType = address.customAttributes.delivery_type;

            shippingService.isLoading(true);
            cache = rateRegistry.get(address.getKey());

            if (cache) {
                shippingService.setShippingRates(cache);
                shippingService.isLoading(false);
            } else {
                storage.post(
                    resourceUrlManager.getUrlForEstimationShippingMethodsByAddressId(),
                    JSON.stringify({
                        addressId: address.customerAddressId,
                        deliveryType: (deliveryType != undefined ? deliveryType.value : "")
                    }),
                    false
                ).done(function (result) {
                    rateRegistry.set(address.getKey(), result);
                    shippingService.setShippingRates(result);
                }).fail(function (response) {
                    shippingService.setShippingRates([]);
                    errorProcessor.process(response);
                }).always(function () {
                    shippingService.isLoading(false);
                }
                );
            }
        }
    };

    return function (target) {

        target = mixin;

        return target;
    };
});