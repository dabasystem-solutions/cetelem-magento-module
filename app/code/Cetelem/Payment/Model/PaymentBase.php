<?php

namespace Cetelem\Payment\Model;

use Cetelem\Payment\Helper\Data;
use Cetelem\Payment\Api\PaymentInterface;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class PaymentBase
{
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var OrderRepository
     */
    protected $order;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var CollectionFactory
     */
    protected $addressCollection;
    /**
     * @var StoreManagerInterface
     */
    protected $store;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param Session $checkoutSession
     * @param OrderRepository $order
     * @param CollectionFactory $addressCollection
     * @param OrderFactory $orderFactory
     * @param StoreManagerInterface $storeInterface
     * @param Data $helper
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepository $order,
        CollectionFactory $addressCollection,
        OrderFactory $orderFactory,
        StoreManagerInterface $storeInterface,
        Data $helper,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->order = $order;
        $this->orderFactory = $orderFactory;
        $this->store = $storeInterface;
        $this->addressCollection = $addressCollection;
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws Exception
     */
    public function getFieldWrapper(Quote $quote, $productCode, $merchantCode, $paymentMode, $callbackUrl): array
    {
        $quote->reserveOrderId();
        $orderId = $quote->getReservedOrderId();

        $amount = $quote->getGrandTotal() * 100;
        $product_code = $this->helper->getConfig($productCode);
        $address = $quote->getBillingAddress();

        $regexExclude='/[^a-zA-ZàáâäãåacceèéêëeiìíîïlnòóôöõøùúûüuuÿýzzñçcšžÀÁÂÄÃÅACCEEÈÉÊËÌÍÎÏILNÒÓÔÖÕØÙÚÛÜUUŸÝZZÑßÇŒÆCŠŽ \\s]+/';
        $phone = substr($address->getTelephone(), 0, 9);
        if (!preg_match('/(9|8)[0-9]{8}/', $phone)) {
            $phone='';
        }
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $token = '';
        for ($i = 0; $i < 32; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }

        $quote->setData('cetelem_token', $token);
        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);

        return [
            "COMANDO"=>'INICIO',
            "IdTransaccion" => self::buildTransactionId($orderId),
            "CodCentro" => $this->helper->getConfig($merchantCode),
            "Importe" => $amount,
            "CodProducto" => $product_code,
            "ModoPago" => $this->helper->getConfig($paymentMode),
            "Material" => "",
            "ReturnURL" => $this->store->getStore()->getBaseUrl()."cetelem/index/success",
            "ReturnOK" => $this->store->getStore()->getBaseUrl()
                . $this->helper->getConfig($callbackUrl)
                . '/Albaran/'.$orderId .'/token/'. $token ,
            "windowstate" => "",
            "Email"=> $address->getEmail(),
            "Nombre" => preg_replace($regexExclude, ' ', substr($address->getFirstname(), 0, 40)),
            "Apellidos" => preg_replace($regexExclude, ' ', substr($address->getLastname(), 0, 40)),
            "Direccion" => preg_replace($regexExclude, ' ', substr($address->getStreet()[0], 0, 50)),
            "Localidad" => preg_replace($regexExclude, ' ', substr($address->getCity(), 0, 20)),
            "CodPostalEnvio" => substr($address->getPostcode(), 0, 5),
            "Telefono1" => $phone,
            "Albaran" => $orderId
        ];
    }

    private static function buildTransactionId(string $orderId) : string
    {
        $length = 13;
        $pad = substr(date('Y'), -1) . str_pad((date('z')+1), 3, '0', STR_PAD_LEFT);
        if (strlen($pad . $orderId) <= $length) {
            $transactionId = $pad . str_pad($orderId, $length - strlen($pad), "0", STR_PAD_LEFT);
        }
        else {
            $pad = substr($pad, ($length - strlen($orderId)) * -1);
            $transactionId = $pad . $orderId;
        }
        return $transactionId;
    }
}
