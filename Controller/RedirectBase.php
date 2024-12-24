<?php
namespace Cetelem\Payment\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class RedirectBase extends Action
{
    const MESSAGE = 'Please Wait';
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $pageFactory
    ) {
        $this->pageFactory     = $pageFactory;
        return parent::__construct($context);
    }

    /**
     * @return Page|ResultInterface
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}
