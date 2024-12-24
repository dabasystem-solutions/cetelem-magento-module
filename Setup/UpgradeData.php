<?php

namespace Cetelem\Payment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * UpgradeData constructor.
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $installer]);

        /** @var QuoteSetup $quoteInstaller */
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $installer]);

        $salesSetup->addAttribute(Order::ENTITY, 'months', [
                'type' => Table::TYPE_INTEGER,
                'length' => 11,
                'visible' => false,
                'nullable' => true
            ]);

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'months',
            [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'comment' => 'Mensualidades'
                ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'months',
            [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'comment' => 'Mensualidades'
                ]
        );

        if (version_compare($context->getVersion(), "2.0.2", "<")) {
            $salesSetup->addAttribute(Order::ENTITY, 'cetelem_token', [
                'type' => Table::TYPE_TEXT,
                'length' => 128,
                'visible' => false,
                'nullable' => true
            ]);

            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'cetelem_token',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'comment' => 'Cetelem Token Order'
                ]
            );
        }

        if (version_compare($context->getVersion(), "3.0.0", "<")) {
            $quoteInstaller->addAttribute('quote', 'cetelem_token', [
                'type' => Table::TYPE_TEXT,
                'length' => 128,
                'visible' => false,
                'nullable' => true
            ]);

            $installer->getConnection()->addColumn(
                'quote',
                'cetelem_token',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'comment' => 'Cetelem Token Quote'
                ]
            );
        }
        $installer->endSetup();
    }
}
