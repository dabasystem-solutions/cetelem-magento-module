<?php

namespace Cetelem\Payment\Helper;

use Cetelem\Payment\Api\PaymentInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper implements PaymentInterface
{
    /**
     * @param $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue($config_path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $method
     * @return string
     */
    public function getEnvironmentUrl($method): string
    {
        return $this->getConfig(
            $method . PaymentInterface::TEST_MODE
        ) == 1 ?
            PaymentInterface::TEST_URL :
            PaymentInterface::LIVE_URL;
    }

    /**
     * @return array
     */
    public function getConfigCetelem(): array
    {
        return [
            "enabled" => $this->getConfig(
                PaymentInterface::CETELEM . PaymentInterface::IS_ENABLED
            ),
            "calc_enabled" => $this->getConfig(
                PaymentInterface::CETELEM . PaymentInterface::SHOW_CALCULATOR
            ),
            "merchant_code" => $this->getConfig(
                PaymentInterface::CETELEM . PaymentInterface::MERCHANT_CODE
            ),
            "min_amount" => $this->getConfig(
                PaymentInterface::CETELEM . PaymentInterface::MIN_AMOUNT
            ),
            "serverUrl" => $this->getConfig(
                PaymentInterface::CETELEM . PaymentInterface::SERVER_URL
            ),
            "server" => $this->getEnvironmentUrl(
                PaymentInterface::CETELEM
            )
        ];
    }

    /**
     * @return array
     */
    public function getConfigEnCuotas(): array
    {
        return [
            "enabled" => $this->getConfig(
                PaymentInterface::ENCUOTAS . PaymentInterface::IS_ENABLED
            ),
            "calc_enabled" => $this->getConfig(
                PaymentInterface::ENCUOTAS . PaymentInterface::SHOW_CALCULATOR
            ),
            "merchant_code" => $this->getConfig(
                PaymentInterface::ENCUOTAS . PaymentInterface::MERCHANT_CODE
            ),
            "max_amount" => $this->getConfig(
                PaymentInterface::ENCUOTAS . PaymentInterface::MAX_AMOUNT
            ),
            "serverUrl" => $this->getConfig(
                PaymentInterface::ENCUOTAS . PaymentInterface::SERVER_URL
            ),
            "server" => $this->getEnvironmentUrl(
                PaymentInterface::ENCUOTAS
            )
        ];
    }
}
