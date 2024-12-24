<?php namespace Cetelem\Payment\Block;

use Cetelem\Payment\Api\PaymentInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class CetelemRedirect extends Redirect implements PaymentInterface
{
    /**
     * @return string
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function getFields(): string
    {
        $html ='';
        $configData = $this->paymentBase
            ->getFieldWrapper(
                $this->getQuote(),
                PaymentInterface::CETELEM . PaymentInterface::PRODUCT_CODE,
                PaymentInterface::CETELEM . PaymentInterface::MERCHANT_CODE,
                PaymentInterface::CETELEM . PaymentInterface::PAYMENT_MODE,
                PaymentInterface::CETELEM . PaymentInterface::MERCHANT_URL
            );
        foreach ($configData as $field => $value) {
            if ($value != '') {
                $html .= '<input type="hidden" name = "' . $field . '" value = "' . $value . '">';
            }
        }
        return $html;
    }
    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->helper->getEnvironmentUrl(PaymentInterface::CETELEM) . PaymentInterface::CETELEM_REDIRECT_PATH;
    }
}
