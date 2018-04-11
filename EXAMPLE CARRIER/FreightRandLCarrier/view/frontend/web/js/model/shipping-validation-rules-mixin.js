/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define(
    [
        'underscore',
        'mage/utils/wrapper'
    ],
    function (_, wrapper) {
        'use strict';

        var ratesRules = {},
            checkoutConfig = window.checkoutConfig;

        return function(target) {
            var rewrite = target.getObservableFields;
            var rewrite = wrapper.wrap(rewrite, function(original) {
                var result = original(),
                    quote = checkoutConfig.quoteItemData,
                    isFreight = false;

                // Check if quote contains a must_ship_freight product
                _.forEach(quote, function(value, key, list){
                    var product = value.product;

                    if (product.must_ship_freight) {
                        isFreight = true;
                        return false;
                    }
                });

                // Check if 'freight' shipping method is active
                if(_.contains(checkoutConfig.activeCarriers, 'freight')
                   && isFreight
                ) {
                    result.push('delivery_type');
                }
                return result;
            });

            target.getObservableFields = rewrite;

            return target;
        };
});
