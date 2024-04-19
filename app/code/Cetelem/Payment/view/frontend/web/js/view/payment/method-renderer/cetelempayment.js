/*browser:true*/
/*global define*/

define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Cetelem_Payment/js/action/set-payment-method',
        'Magento_Customer/js/customer-data',
        'mage/url',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, $, Component, placeOrderAction,
              selectPaymentMethodAction,
              customer,
              checkoutData,
              additionalValidators,
              setPaymentMethodAction,
              customerData,
              url,
              orders) {
        'use strict';
        return Component.extend({

            currentGrandTotal: 0,
            totals: quote.totals,
            cart: customerData.get('cart'),
            calculatorConfig: window.checkoutConfig.cetelem.calculator,

            defaults: {
                template: 'Cetelem_Payment/payment/cetelempayment'
            },

            initialize: function () {
                let self = this;
                this.totals.subscribe(function () {
                    self.setCalculatorPrice();
                });
                this.cart.subscribe(function () {
                    self.setCalculatorPrice();
                });
                return this._super();
            },

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = setPaymentMethodAction(this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },

            selectPaymentMethod: function() {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },

            afterPlaceOrder: function () {
                customerData.invalidate(['cart']);
                window.location.replace(url.build('cetelem/cetelem/redirect/'));
            },
            /** Returns send check to info */
            getCetelemMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getCetelemInfoData: function () {
                return window.checkoutConfig.cetelem.title;
            },
            getCetelemImage: function(){
                return window.checkoutConfig.cetelem.image;
            },

            getGrandTotal: function () {
                return this.totals().grand_total;
            },

            setCalculatorPrice: function () {
                const price = this.getGrandTotal();
                if (this.currentGrandTotal != price) {
                    this.currentGrandTotal = price;
                    $("#opt-price").text(price);
                    return true;
                }
                return false;
            },

            initCalculator: function () {
                if (this.calculatorConfig.enabled && this.calculatorConfig.min_amount <= this.getGrandTotal()) {
                    this.setCalculatorPrice();
                    window.cantidad = $('#opt-price').text();
                    window.jsUrl = this.calculatorConfig.serverUrl;
                    window.codCentro = this.calculatorConfig.merchant_code;
                    window.color = "";
                    window.server = this.calculatorConfig.server;
                    return '<scr' + 'ipt type="text/javascript" src="' + this.calculatorConfig.server + this.calculatorConfig.serverUrl + '" async></scr' + 'ipt>';
                }
                return false;
            }
        });
    }
);
