<?php

namespace Cetelem\Payment\Controller\Index;

use Cetelem\Payment\Api\PaymentInterface;
use Cetelem\Payment\Helper\Data;
use Cetelem\Payment\Logger\Logger;
use Cetelem\Payment\Utils\Callback\CallbackResponse;
use Cetelem\Payment\Utils\Callback\ErrorResponse;
use Cetelem\Payment\Utils\Callback\SuccessResponse;
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

    private const RESULT_CODE_PRE_APPROVED = '00';
    private const RESULT_CODE_APPROVED = '50';
    private const RESULT_CODE_DENIED_1 = '99';
    private const RESULT_CODE_DENIED_2 = '51';

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
     * @var string
     */
    private $_configPath;

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

        /** @var \Magento\Sales\Model\Order */
        $order = $orderCollection->getFirstItem();

        $dataParams       = $quote->getPayment()->getmethod() == 'cetelempayment' ?
            PaymentInterface::CETELEM :
            PaymentInterface::ENCUOTAS;

        $this->setConfigPath($dataParams);

        $remoteAddr = $this->remoteAddress->getRemoteAddress();
        if (!$this->validateIpAddress($remoteAddr)) {
            $comment = __('Unauthorized access IP:' . $remoteAddr);
            $this->logger->info($comment);
            $response = new ErrorResponse($comment);
            $response->send();
            return;
        }

        if ($quote->isEmpty()) {
            $comment = __('Quote with reservedOrderId ' . $reservedOrderId . ' does not exist');
            $this->logger->info($comment);
            $response = new ErrorResponse($comment);
            $response->send();
            return;
        }

        if ($params["token"] != $quote->getData('cetelem_token')) {
            $comment = __('Invalid Token');
            $this->logger->info($comment);
            $response = new ErrorResponse($comment);
            $response->send();
            return;
        }

        $responseCode = (string)$params['codResultado'];
        if (!$this->validateResponseCode($responseCode)) {
            $comment = __('Invalid response code');
            $this->logger->info($comment);
            $response = new ErrorResponse($comment);
            $response->send();
            return;
        }

        $payment = $quote->getPayment();
        if (!$this->validatePaymentMethod($payment->getmethod())) {
            $comment = __("Order has been processed. Cannot change status");
            $this->logger->info($comment);
            $response = new ErrorResponse($comment);
            $response->send();
            return;
        }

        $callbackResponse = $this->processOrder($order, $quote, $responseCode);

        $this->logger->info("order status:".$order->getStatus());
        $this->logger->info("response code:".$responseCode);

        $callbackResponse->send();
    }

    /**
     * Sets the config path 
     *
     * @param string $path
     * @return void
     */
    private function setConfigPath(string $path)
    {
        $this->_configPath = $path;
    }

    private function getConfigPath()
    {
        return $this->_configPath;
    }

    private function validateIpAddress($ipAddress) : bool
    {
        $configIps = $this->helper->getConfig($this->getConfigPath() . self::CALLBACK_IPS_ALLOWED);
        if (!$configIps) {
            return true;
        }

        $configIps = preg_replace("/\s/", "", $configIps);
        $ips = explode(",", $configIps);
        return in_array($ipAddress, $ips);
    }

    private function validateResponseCode(string $responseCode)
    {
        $validCodes = [self::RESULT_CODE_PRE_APPROVED, self::RESULT_CODE_APPROVED, self::RESULT_CODE_DENIED_1, self::RESULT_CODE_DENIED_2];
        return in_array($responseCode, $validCodes);
    }

    private function validatePaymentMethod(string $paymentMethod)
    {
        $validMethods = ['cetelempayment', 'encuotaspayment'];
        return in_array($paymentMethod, $validMethods);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param Quote $quote
     * @param string $responseCode
     * @return CallbackResponse
     */
    private function processOrder(\Magento\Sales\Model\Order $order, Quote $quote, string $responseCode)
    {
        if ($responseCode == self::RESULT_CODE_PRE_APPROVED) {
            $status  = $this->helper->getConfig($this->getConfigPath() . self::CONFIG_PREAPROVED_ORDER);
            $comment = __('Pre-approved financing operation');
        } elseif ($responseCode == self::RESULT_CODE_APPROVED) {
            $status  = $this->helper->getConfig($this->getConfigPath() . self::CONFIG_APROVED_ORDER);
            $comment = 'Approved financing operation';
        } elseif ($responseCode == self::RESULT_CODE_DENIED_1 || $responseCode == self::RESULT_CODE_DENIED_2) {
            $status  = $this->helper->getConfig($this->getConfigPath() . self::CONFIG_CANCELED_ORDER);
            $comment = 'Financing operation denied';
        }

        if ($this->getCheckoutMethod($quote) == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $quote = $this->prepareGuestQuote($quote);
        }

        if ($order->isEmpty()) {
            $order = $this->createOrder($quote);
        }

        $order->setState($status);
        $order->setStatus($status);

        if (isset($params['duracion']) && $params['duracion'] != null) {
            $order->setMonths($params['duracion']);
        }

        $order->addStatusToHistory($order->getStatus(), $comment);
        $this->orderRepository->save($order);

        if ($order->getStatus() == 'processing' && $responseCode == self::RESULT_CODE_APPROVED) {
            $this->autoInvoice($order);
            $order->addCommentToStatusHistory(__($comment), false)
                ->setIsCustomerNotified(true);

            if (method_exists($order, "save")) {
                $order->save();
            }
            else {
                $this->orderRepository->save($order);
            }

            $this->sendEmail($order);
        }

        if ($responseCode == self::RESULT_CODE_PRE_APPROVED || $responseCode == self::RESULT_CODE_APPROVED) {
            $callbackResponse = new SuccessResponse((string) $order->getId());
        }
        else {
            $callbackResponse = new ErrorResponse($comment);
        }

        return $callbackResponse;
    }

    /**
     * @param $orderId
     * @throws LocalizedException
     * @throws Exception
     */
    private function autoInvoice(\Magento\Sales\Model\Order $order)
    {
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
                ->setIsCustomerNotified(true);
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
                $order->addStatusHistoryComment(__("Cetelem ha enviado correo del pedido correctamente"), false);
            }

            if (method_exists($order, "save")) {
                $order->save();
            }
            else {
                $this->orderRepository->save($order);
            }
        }
    }

    /**
     * @param Quote $quote
     * @return \Magento\Sales\Model\Order
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

    protected function prepareGuestQuote(Quote $quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $quote;
    }
}
