var config = {
    config: {
        mixins: {
            "Magento_Checkout/js/model/shipping-rate-processor/customer-address": {
            	"Augustash_FreightRandLCarrier/js/model/shipping-rate-processor/customer-address-mixin": true
            },
            "Magento_Ui/js/form/element/checkbox-set": {
                "Augustash_FreightRandLCarrier/js/form/element/checkbox-set-mixin": true
            },
            "Magento_Checkout/js/model/shipping-save-processor/payload-extender": {
                "Augustash_FreightRandLCarrier/js/model/shipping-save-processor/payload-extender-mixin": true
            }
        }
    }
};
