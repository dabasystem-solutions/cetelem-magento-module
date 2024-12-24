<?php namespace Cetelem\Payment\Block;

use Cetelem\Payment\Helper\Data;
use Cetelem\Payment\Model\PaymentBase;
use Magento\Backend\Block\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Api\CartRepositoryInterface;

class Redirect extends Template
{

    /**
     * @var PaymentBase
     */
    protected $paymentBase;
    /**
     * @var Repository
     */
    protected $assetRepository;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Session
     */
    protected $checkoutSession;
    protected $paymentName;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param Context $context
     * @param PaymentBase $paymentBase
     * @param Repository $assetRepository
     * @param Data $helper
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param array $data
     */

    public function __construct(
        Context $context,
        PaymentBase $paymentBase,
        Repository $assetRepository,
        Data $helper,
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        array $data = []
    ) {
        $this->paymentBase = $paymentBase;
        $this->assetRepository = $assetRepository;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->paymentName = $this->getData('payment');
        $this->quoteRepository = $quoteRepository;
        parent::__construct($context, $data);
    }

    public function getLoader(): array
    {
        return ["loader"=>$this->assetRepository->getUrl('Cetelem_Payment::images/loader.gif')];
    }

    protected function getOrderId()
    {
        return $this->checkoutSession->getQuote()->getReservedOrderId();
    }

    protected function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
