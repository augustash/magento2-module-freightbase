/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-registry'
], function (_, utils, Abstract, quote, rateRegistry) {
    'use strict';

    return function (hasChanged) {
        return hasChanged.extend({
            initialize: function () {
                this._super();
            },
            /**
             * @inheritdoc
             */
            hasChanged: function () {
                var value = this.value(),
                    initial = this.initialValue,
                    shippingAddress = quote.shippingAddress();

                if (this.inputName == "custom_attributes[delivery_type]") {
                    // Clear caches
                    // Causes shipping pricing to be re-checked
                    rateRegistry.set(shippingAddress.getKey(), null);
                    rateRegistry.set(shippingAddress.getCacheKey(), null);

                    // Set values to our custom and extension attributes
                    shippingAddress.customAttributes.delivery_type = {
                        attribute_code: "delivery_type",
                        value: value.join()
                    }

                    // Update shipping address with new value(s)
                    // This will trigger recalculation of shippping pricing against api
                    quote.shippingAddress(shippingAddress);
                }

                return this.multiple ?
                    !utils.equalArrays(value, initial) :
                    this._super();
            }
        });
    }
});