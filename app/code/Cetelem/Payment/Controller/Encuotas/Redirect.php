<?php
namespace Cetelem\Payment\Controller\Encuotas;

use Cetelem\Payment\Controller\RedirectBase;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class Redirect extends RedirectBase
{
    const MESSAGE = 'Redirect to enCuotas, please wait...';

    /**
     * @return Page|ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__(self::MESSAGE));
        return $resultPage;
    }
}
