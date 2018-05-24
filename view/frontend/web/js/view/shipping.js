/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/shipping'
],
function(Shipping){
    return Shipping.extend({
        /**
         * Check if freight items are in cart
         */
        isFreight: function () {
            var isFreight = false,
                rates = this.rates().length;

            // Must be done with variable because .each takes 'return true' as 'continue'
            $.each(quote.getItems(), function (index, item) {
                if (item.product.must_ship_freight == '1' && rates == 0) {
                    isFreight = true;

                    return false;
                }
            }, rates);
            return isFreight;
        }
    });
});
