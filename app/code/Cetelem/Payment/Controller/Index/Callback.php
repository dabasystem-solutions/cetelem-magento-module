<?php

namespace Cetelem\Payment\Controller\Index;

use Cetelem\Payment\Api\PaymentInterface;
use Cetelem\Payment\Helper\Data;
use Cetelem\Payment\Logger\Logger;
use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Helper\Data as CheckoutData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;

class Callback extends Action implements CsrfAwareActionInterface
{
    const CONFIG_PREAPROVED_ORDER = PaymentInterface::PREAPROVED_ORDER;
    const CONFIG_APROVED_ORDER    = PaymentInterface::APROVED_ORDER;
    const CONFIG_CANCELED_ORDER   = PaymentInterface::CANCELED_ORDER;
    const CALLBACK_IPS_ALLOWED    = PaymentInterface::IPS_ALLOWED;

    /**
     * @var OrderInterface
     */
    protected $order;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var Transaction
     */
    protected $transaction;
    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var QuoteCollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var CheckoutData
     */
    protected $checkoutData;

    /**
     * @param Context $context
     * @param OrderInterface $order
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductFactory $productFactory
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param StockRegistryInterface $stockRegistry
     * @param Data $helper
     * @param RemoteAddress $remoteAddress
     * @param Logger $logger
     * @param OrderSender $orderSender
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderFactory $quoteCollectionFactory
     * @param QuoteManagement $quoteManagement
     * @param CheckoutData $checkoutData
     */
    public function __construct(
        Context                  $context,
        OrderInterface           $order,
        OrderRepositoryInterface $orderRepository,
        ProductFactory           $productFactory,
        InvoiceService           $invoiceService,
        Transaction              $transaction,
        InvoiceSender            $invoiceSender,
        StockRegistryInterface   $stockRegistry,
        Data                     $helper,
        RemoteAddress            $remoteAddress,
        Logger                   $logger,
        OrderSender              $orderSender,
        QuoteCollectionFactory   $quoteCollectionFactory,
        OrderCollectionFactory   $orderCollectionFactory,
        OrderFactory             $orderFactory,
        QuoteManagement          $quoteManagement,
        CheckoutData             $checkoutData
    ) {
        $this->order           = $order;
        $this->orderRepository = $orderRepository;
        $this->productFactory  = $productFactory;
        $this->invoiceService  = $invoiceService;
        $this->transaction     = $transaction;
        $this->invoiceSender   = $invoiceSender;
        $this->stockRegistry   = $stockRegistry;
        $this->helper          = $helper;
        $this->remoteAddress   = $remoteAddress;
        $this->logger          = $logger;
        $this->orderSender     = $orderSender;
        $this->orderFactory    = $orderFactory;
        $this->quoteManagement = $quoteManagement;
        $this->checkoutData    = $checkoutData;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;

        return parent::__construct($context);
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $params          = $this->getRequest()->getParams();
        $reservedOrderId = $params['Albaran'];

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection */
        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection->addFieldToFilter("reserved_order_id", $reservedOrderId);
        
        /** @var Quote */
        $quote = $quoteCollection->getFirstItem();
        
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter("quote_id", $quote->getId());

        /** @var Order */
        $order = $orderCollection->getFirstItem();

        if (!$order->isEmpty()) {
            $this->logger->info("Order " . $order->getId() . " already has quote_id " . $quote->getId());
            throw new \Exception("Order already exists");
        }

        $dataParams       = $quote->getPayment()->getmethod() == 'cetelempayment' ?
            PaymentInterface::CETELEM :
            PaymentInterface::ENCUOTAS;
        $remoteAddr       = $this->remoteAddress->getRemoteAddress();
        $configIps        = $this->helper->getConfig($dataParams . self::CALLBACK_IPS_ALLOWED);
        $ips              = explode(",", $configIps);

        



        if (in_array($this->remoteAddress->getRemoteAddress(), $ips) || true) { // todo eliminar el true
            if (!$quote->isEmpty()) {
                if ($params["token"] == $quote->getData('cetelem_token')) {
                    (string)$responseCode = $params['codResultado'];
                    if (!empty($reservedOrderId) && in_array($responseCode, ['00', '50', '99', '51'])) {
                        $payment = $quote->getPayment();
                        if (
                            in_array($payment->getmethod(), ['cetelempayment', 'encuotaspayment'])
                        ) {
                            if ($responseCode == '00') {
                                $status  = $this->helper->getConfig($dataParams . self::CONFIG_PREAPROVED_ORDER);
                                $comment = __('Pre-approved financing operation');
                            } elseif ($responseCode == '50') {
                                $status  = $this->helper->getConfig($dataParams . self::CONFIG_APROVED_ORDER);
                                $comment = 'Approved financing operation';
                                

                            } elseif ($responseCode == '99' || $responseCode == '51') {
                                $status  = $this->helper->getConfig($dataParams . self::CONFIG_CANCELED_ORDER);
                                $comment = 'Financing operation denied';
                            }

                            if ($this->getCheckoutMethod($quote) == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
                                $quote = $this->prepareGuestQuote($quote);
                            }
                            $order = $this->createOrder($quote);
                            $order->setState($status);
                            $order->setStatus($status);

                            if (isset($params['duracion']) && $params['duracion'] != null) {
                                $order->setMonths($params['duracion']);
                            }

                            $order->addStatusToHistory($order->getStatus(), $comment);
                            $this->orderRepository->save($order);

                            if ($order->getStatus() == 'processing' && $responseCode == '50') {
                                $this->autoInvoice($order->getId());
                                $order->addCommentToStatusHistory(__($comment), false)
                                        ->setIsCustomerNotified(true)
                                        ->save();

                                $this->sendEmail($order);
                            }
                        } else {
                            $comment = __("Order has been processed. Cannot change status");
                        }
                    }
                } else {
                    $comment = __('Invalid Token');
                }
            }
            else {
                $comment = __('Quote with reservedOrderId ' . $reservedOrderId . ' does not exist');
            }
        } else {
            $comment = __('Unauthorized access IP:' . $remoteAddr);
        }
        $this->logger->info($comment);
        $this->logger->info("status:".$order->getStatus());
        $this->logger->info("status:".$responseCode);
        //echo $comment;
    }

    /**
     * @param $orderId
     * @throws Exception
     * @deprecated 2.1.0
     */
    private function returnStock($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        foreach ($order->getAllItems() as $item) {
            if ($this->stockRegistry->getStockItem($item->getProductId())->getData('manage_stock')) {
                $productId = $item->getProductId();
                $product   = $this->productFactory->create()->load($productId);
                $returnQty = $item->getQtyOrdered();
                $origQty   = $product->getQuantityAndStockStatus()['qty'];
                $product->setStockData(
                    [
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => $returnQty + $origQty
                    ]
                );
                $product->save();
            }
        }
    }

    /**
     * @param $orderId
     * @throws LocalizedException
     * @throws Exception
     */
    private function autoInvoice($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $transaction = $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transaction->save();
            $this->invoiceSender->send($invoice);
            $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                ->setIsCustomerNotified(true)
                ->save();
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Envía el correo electrónico de la orden al cliente.
     *
     * @param \Magento\Sales\Model\Order $order
     * @throws \Magento\Framework\Exception\MailException
     */
    private function sendEmail(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getEmailSent()) {
            if ($this->orderSender->send($order)) {
                $order->addStatusHistoryComment(__("Cetelem no ha sido posible envíar el correo de confirmación."), false);
            }
            else {
                $order->addStatusHistoryComment(__("Cetelem ha enviado correo del pedido correctamente"), false)->save();
            }
        }
    }

    /**
     * @param Quote $quote
     * @return Order
     */
    private function createOrder(Quote $quote)
    {
        return $this->quoteManagement->submit($quote);
    }

    protected function getCheckoutMethod(Quote $quote)
    {
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $quote->getCheckoutMethod();
    }

    private function ignoreAddressValidation(Quote $quote)
    {
        // $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        // if (!$quote->getIsVirtual()) {
        //     $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        // }
    }

    protected function prepareGuestQuote(Quote $quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $quote;
    }
}
