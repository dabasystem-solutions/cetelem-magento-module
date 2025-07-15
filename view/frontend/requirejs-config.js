var config = {
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'Cetelem_Payment/js/model/priceswitch': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Cetelem_Payment/js/custom-swatch-renderer': true
            },
        }
    }
};
