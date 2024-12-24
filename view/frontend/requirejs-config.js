var config = {
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'Cetelem_Payment/js/model/priceswitch': true
            },
        }
    },
    map: {
        '*': {
            'Magento_Swatches/js/swatch-renderer' : 'Cetelem_Payment/js/custom-swatch-renderer',
            'magento-swatch.renderer' : 'Magento_Swatches/js/swatch-renderer'
        }
    }
};
