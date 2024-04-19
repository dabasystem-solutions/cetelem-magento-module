<?php

namespace Cetelem\Payment\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class SetupConfigData implements DataPatchInterface
{
    private $moduleDataSetup;
    private $configWriter;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->configWriter->save(
            'payment/cetelempayment/allowed_ips',
            '213.170.60.39'
        );

        $this->configWriter->save(
            'payment/cetelempayment/allowed_ips',
            '213.170.60.39'
        );

        $this->configWriter->save(
            'payment/cetelempayment/credentials/merchant_url',
            'cetelem/index/callback'
        );

        $this->configWriter->save(
            'payment/encuotaspayment/credentials/merchant_url',
            'cetelem/index/callback'
        );

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
