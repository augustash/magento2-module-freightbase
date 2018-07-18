/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
	'Magento_Checkout/js/model/quote',
	'mage/utils/wrapper'
], function (quote, wrapper) {
    'use strict';

    return function (payload) {
    	return wrapper.wrap(payload, function (originalAction) {
    		var method = quote.shippingMethod();
    		payload = originalAction();

    		// Set shipping price as custom attribute
    		payload.addressInformation.shipping_address.customAttributes = {"shipping_method_price": method['amount'], "shipping_method_title": method['method_title'], "shipping_method_code": method['method_code']};
    	});

        return originalAction();
    };
});