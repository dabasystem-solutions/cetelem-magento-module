<?php

namespace Cetelem\Payment\Setup\Patch\Data;

use Exception;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class AddCetelemOrderStatus implements DataPatchInterface
{
    const STATUS_CODE = 'pending_cetelem';
    const STATUS_STATE = 'pending';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function apply()
    {
        $status = $this->statusFactory->create();

        $status->setData([
            'status' => self::STATUS_CODE,
            'label' => 'Pending Cetelem Payment',
        ]);

        /**
         * Save the new status
         */
        $statusResource = $this->statusResourceFactory->create();
        $statusResource->save($status);

        /**
         * Assign status to state
         */
        $status->assignState(self::STATUS_STATE, true, true);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
