<?php

namespace Cetelem\Payment\Api;

interface PaymentInterface
{
    /** common constants */
    const TEST_URL         = "https://test.cetelem.es";
    const LIVE_URL         = "https://www.cetelem.es";
    const IS_ENABLED       = 'active';
    const TEST_MODE        = 'testmode';
    const IPS_ALLOWED      = 'allowed_ips';
    const SORT_ORDER       = 'sort_order';
    const MERCHANT_CODE    = 'credentials/merchant_code';
    const MERCHANT_URL     = 'credentials/merchant_url';
    const PRODUCT_CODE     = 'payment_configuration/product_code';
    const PAYMENT_MODE     = 'payment_configuration/payment_mode';
    const MIN_AMOUNT       = 'productview_configuration/min_amount';
    const SHOW_CALCULATOR  = 'productview_configuration/show_calculator';
    const SERVER_URL       = 'productview_configuration/js_url';
    const PREAPROVED_ORDER = 'order_configuration/order_status';
    const APROVED_ORDER    = 'order_configuration/order_approved_status';
    const CANCELED_ORDER   = 'order_configuration/canceled_status';

    /**  cetelem constants */
    const CETELEM               = 'payment/cetelempayment/';
    const CALC_COLOR            = 'productview_configuration/color';
    const CALC_TYPE             = 'productview_configuration/type';
    const CETELEM_REDIRECT_PATH = '/eCommerceLite/configuracion.htm';

    /**  encuotas constants */
    const ENCUOTAS               = 'payment/encuotaspayment/';
    const MAX_AMOUNT             = 'order_configuration/max_amount';
    const EXTERNAL_URL_ENCUOTAS  = 'cron/external_url';
    const ENCUOTAS_REDIRECT_PATH = '/eCommerceLite/enCuotas/configuracion.htm';
}
