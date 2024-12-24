<?php namespace Cetelem\Payment\Block;

use Magento\Checkout\Block\Onepage\Success as OnePageSuccess;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;

class Success extends OnePageSuccess
{
    /**
     * @var Repository
     */
    protected $assetRepository;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        Repository $assetRepository,
        array $data = []
    ) {
        $this->assetRepository = $assetRepository;
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }
    public function getOrderStatus()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $order->getStatus();
    }

    public function getLogo($payment): string
    {
        return $this->assetRepository->getUrl('Cetelem_Payment::images/' . $payment . '.jpg');
    }

    public function getOrderPaymentMethod(): string
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $order->getPayment()->getMethod();
    }
}
