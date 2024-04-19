<?php

namespace Cetelem\Payment\Observer;

use Cetelem\Payment\Api\PaymentInterface;
use Cetelem\Payment\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\State;

class CetelemMethodAvailable implements ObserverInterface, PaymentInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param Observer $observer
     */

    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var State
     */
    private $state;

    public function __construct(
        Data $helper,
        Session $checkoutSession,
        State $state
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->state = $state;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $is_enabled = $this->helper
            ->getConfig(
                PaymentInterface::CETELEM.PaymentInterface::IS_ENABLED
            );
        $min_amount = $this->helper
            ->getConfig(
                PaymentInterface::CETELEM.PaymentInterface::MIN_AMOUNT
            );
        $quote = $this->checkoutSession->getQuote();
        $grand_total = $quote->getGrandTotal();
        $checkResult = $observer->getEvent()->getResult();
        /** backend visibility */
        if ($observer->getEvent()->getMethodInstance()->getCode() == "cetelempayment") {
            if ($is_enabled) {
                if ($grand_total >= $min_amount) {
                    $checkResult->setData('is_available', true);
                } else {
                    $checkResult->setData('is_available', false);
                }
            } else {
                $checkResult->setData('is_available', false);
            }
            /** backend visibility */
            if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
                $checkResult->setData('is_available', false);
            }
        }
    }
}
