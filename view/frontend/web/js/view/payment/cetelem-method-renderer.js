define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'cetelempayment',
                component: 'Cetelem_Payment/js/view/payment/method-renderer/cetelempayment'
            },
            {
                type: 'encuotaspayment',
                component: 'Cetelem_Payment/js/view/payment/method-renderer/encuotaspayment'
            }
        );
        return Component.extend({});
    }
);
