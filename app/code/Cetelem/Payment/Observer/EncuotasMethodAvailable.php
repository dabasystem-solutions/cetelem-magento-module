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

class EncuotasMethodAvailable implements ObserverInterface, PaymentInterface
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
                PaymentInterface::ENCUOTAS.PaymentInterface::IS_ENABLED
            );
        $quote = $this->checkoutSession->getQuote();
        $grand_total = $quote->getGrandTotal();
        $checkResult = $observer->getEvent()->getResult();
        $max_amount = $this->helper
            ->getConfig(
                PaymentInterface::ENCUOTAS.PaymentInterface::MAX_AMOUNT
            );

        $min_amount = $this->helper
            ->getConfig(
                PaymentInterface::ENCUOTAS.PaymentInterface::MIN_AMOUNT
            );

        if ($observer->getEvent()->getMethodInstance()->getCode() == "encuotaspayment") {
            /** EnCuotos frontend visibility */
            if ($is_enabled) {
                if ($max_amount >= $grand_total && $min_amount < $grand_total) {
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
