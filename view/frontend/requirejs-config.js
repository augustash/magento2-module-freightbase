var config = {
    map: {
        '*': {
        	'Magento_Checkout/template/shipping.html': 'Augustash_FreightBase/template/shipping.html'
        }
  	},
  	config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Augustash_FreightBase/js/view/shipping-mixin': true
            }
        }
    }
};