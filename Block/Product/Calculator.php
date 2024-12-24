<?php

namespace Cetelem\Payment\Block\Product;

use Cetelem\Payment\Api\PaymentInterface;
use Cetelem\Payment\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Calculator extends Template implements PaymentInterface
{

    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Product
     */
    protected $product;

    /**
     * Calculator constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Product $product
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context  $context,
        Registry $registry,
        Product  $product,
        Data     $helper,
        array    $data = []
    ) {
        $this->product  = $product;
        $this->registry = $registry;
        $this->helper   = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|null
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return float|int
     */
    public function getPriceProduct()
    {
        $product = $this->product->load($this->getCurrentProduct()->getId());
        if ($product->getTypeId() == 'bundle' || $product->getTypeId() == 'grouped') {
            $minPrice = 0;
        } else {
            $minPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        }
        return $minPrice;
    }

    /**
     * @return array
     */
    public function getConfigCetelem(): array
    {
        return $this->helper->getConfigCetelem();
    }

    /**
     * @return array
     */
    public function getConfigEnCuotas(): array
    {
        return $this->helper->getConfigEnCuotas();
    }
}
