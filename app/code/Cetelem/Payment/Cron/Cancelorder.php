<?php
namespace Cetelem\Payment\Cron;

use Cetelem\Payment\Logger\Logger;
use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Cancelorder
{
    /**
     * @var CollectionFactory
     */
    protected $order;
    /**
     * @var
     */
    protected $orderCollectionFactory;
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry,
        Logger $logger
    ) {
        $this->order = $orderCollectionFactory;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $from = date('Y-m-d h:i:s', strtotime('-7 day', strtotime(date("Y-m-d h:i:s"))));
        $to = date('Y-m-d h:i:s', strtotime('-6 day', strtotime(date("Y-m-d h:i:s"))));

        $orders = $this->order->create();
        $orders->addFieldToFilter('created_at', ['from'=>$from, 'to'=>$to]);
        $orders_ids = '';
        foreach ($orders as $order) {
            $payment = $order->getPayment()->getMethod();
            $status = $order->getStatus();
            if (in_array($payment, ["cetelempayment","encuotas"]) && $status=="pending") {
                $order->setStatus('canceled');
                $order->save();
                $orders_ids .= $order->getId() . '/';
                foreach ($order->getAllItems() as $item) {
                    if ($this->stockRegistry->getStockItem($item->getProductId())->getData('manage_stock')) {
                        $productId = $item->getProductId();
                        $product = $this->productFactory->create()->load($productId);
                        $returnQty = $item->getQtyOrdered();
                        $origQty = $product->getQuantityAndStockStatus()['qty'];
                        $product->setStockData(
                            [
                                'use_config_manage_stock' => 0,
                                'manage_stock' => 1,
                                'is_in_stock' => 1,
                                'qty' => $returnQty + $origQty
                            ]
                        );
                        $product->save();
                    }
                }
            }
        }
        $this->logger->info("Canceled orders: " . $orders_ids);
    }
}
