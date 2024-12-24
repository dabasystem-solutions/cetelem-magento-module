<?php
namespace Cetelem\Payment\Model;

use Cetelem\Payment\Api\PaymentInterface;
use Cetelem\Payment\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;

class CetelemConfigProvider implements ConfigProviderInterface
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
        $config = $this->helper->getConfigCetelem();

        return [
            'cetelem' => [
                'title' => __("Pay your order in installments with Cetelem"),
                'image' => $this->assetRepository->getUrl('Cetelem_Payment::images/cetelempayment.jpg'),
                'calculator' => $config
            ]
        ];
    }
}
