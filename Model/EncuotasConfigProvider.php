<?php
namespace Cetelem\Payment\Model;

use Cetelem\Payment\Api\PaymentInterface;
use Cetelem\Payment\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;

class EncuotasConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Repository
     */
    protected $assetRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * CetelemConfigProvider constructor.
     * @param Repository $assetRepository
     * @param Data $helper
     */
    public function __construct(
        Repository $assetRepository,
        Data $helper
    ) {
        $this->assetRepository = $assetRepository;
        $this->helper = $helper;
    }

    /**
     * @return array[]
     */
    public function getConfig(): array
    {
        $config = $this->helper->getConfigEnCuotas();

        return [
            'encuotas' => [
                'title' => __("Pay your order in installments with enCuotas"),
                'encuotasimage' => $this->assetRepository->getUrl('Cetelem_Payment::images/encuotaspayment.jpg'),
                'calculator' => $config
            ]
        ];
    }
}
