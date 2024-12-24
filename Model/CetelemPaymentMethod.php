<?php
namespace Cetelem\Payment\Model;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Pay In Store payment method model
 */
class CetelemPaymentMethod extends AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'cetelempayment';

    /**
    * Availability option
    *
    * @var bool
    */
    protected $_isOffline = true;
}
