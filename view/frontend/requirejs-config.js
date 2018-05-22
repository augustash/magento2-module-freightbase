var config = {
    map: {
        '*': {
        	'Magento_Checkout/template/shipping.html': 'Augustash_FreightBase/template/shipping.html',
        	'Magento_Checkout/js/view/shipping': 'Augustash_FreightBase/js/view/shipping'
        }
  	},
  	config: {
        mixins: {
            'Magento_Checkout/js/action/select-shipping-address': {
                'Augustash_FreightBase/js/action/select-shipping-address-mixin': true
            }
        }
    }
};
