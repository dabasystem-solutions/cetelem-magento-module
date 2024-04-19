<?php namespace Cetelem\Payment\Block;

use Cetelem\Payment\Api\PaymentInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class EncuotasRedirect extends Redirect implements PaymentInterface
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
                PaymentInterface::ENCUOTAS . PaymentInterface::PRODUCT_CODE,
                PaymentInterface::ENCUOTAS . PaymentInterface::MERCHANT_CODE,
                PaymentInterface::ENCUOTAS . PaymentInterface::PAYMENT_MODE,
                PaymentInterface::ENCUOTAS . PaymentInterface::MERCHANT_URL
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
        return $this->helper->getEnvironmentUrl(PaymentInterface::ENCUOTAS) . PaymentInterface::ENCUOTAS_REDIRECT_PATH;
    }
}
