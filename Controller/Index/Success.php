<?php
namespace Cetelem\Payment\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class Success extends Action
{

    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Success constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $order = $this->orderRepository->get($this->checkoutSession->getLastOrderId());

        if ($order->getStatus() == 'canceled') :
            $this->messageManager->addError(__('Rejected funding request.')); 
        elseif ($order->getStatus() == 'pending') :
            $this->messageManager->addSuccess(__('Pending funding request')); 
        else:
            $this->messageManager->addSuccess(__('Approved funding request'));
        endif;

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/onepage/success');
        return $resultRedirect;
    }
}
