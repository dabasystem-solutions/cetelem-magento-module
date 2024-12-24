<?php
namespace Cetelem\Payment\Plugin\ConfigurableProduct\Block\Product\View\Type;

class Configurable
{
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $jsonResult = json_decode($result, true);
        $jsonResult['sprice'] = [];
        foreach ($subject->getAllowProducts() as $simpleProduct) {
            $jsonResult['sprice'][$simpleProduct->getId()] = $simpleProduct->getPrice();
        }
        return json_encode($jsonResult);
    }
}
