define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function(targetModule){

        var reloadPrice = targetModule.prototype._reloadPrice;
        var reloadPriceWrapper = wrapper.wrap(reloadPrice, function(original){
            var result = original();
            var simplePrice = this.options.spConfig.sprice[this.simpleProduct];

            if(simplePrice != '') {
                $('#opt-price').html(simplePrice);
            }
            return result;
        });
        targetModule.prototype._reloadPrice = reloadPriceWrapper;
        return targetModule;
    };
});
