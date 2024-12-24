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
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class Email implements ObserverInterface
{
    protected $objectManager;
    protected $logger;
    protected $_current_order; // Declaración de la propiedad

    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $this->_current_order = $order; // Asignación del valor a la propiedad
            

            $payment = $order->getPayment()->getMethodInstance()->getCode();

            if ($payment == 'cetelempayment') {
                $this->logger->info('Stop new order email triggered for order: ' . $order->getIncrementId());
                $this->stopNewOrderEmail($order);
            }
        } catch (\Exception $e) {
            $this->logger->error('An error occurred: ' . $e->getMessage());
        }
    }

    public function stopNewOrderEmail(\Magento\Sales\Model\Order $order)
    {
        $this->logger->info('Stopping new order email for order: ' . $order->getIncrementId());

        $order->setCanSendNewEmailFlag(false);
        $order->setSendEmail(false);
        $order->setIsCustomerNotified(false);
        try {
            $order->save();
            $this->logger->info('New order email stopped successfully for order: ' . $order->getIncrementId());
        } catch (\Exception $e) {
            $this->logger->error('An error occurred while stopping new order email: ' . $e->getMessage());
        }
    }
}