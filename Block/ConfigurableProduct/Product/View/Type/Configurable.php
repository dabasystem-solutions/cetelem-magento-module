<?php

namespace Cetelem\Payment\Block\ConfigurableProduct\Product\View\Type;

use Closure;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableType;

class Configurable
{

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->productRepository = $productRepository;
    }

    /**
     * @param $id
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProductById($id): ProductInterface
    {
        return $this->productRepository->getById($id);
    }

    /**
     * @param ConfigurableType $subject
     * @param Closure $proceed
     * @return string
     * @throws NoSuchEntityException
     */
    public function aroundGetJsonConfig(
        ConfigurableType $subject,
        Closure $proceed
    ): string {
        $config = $proceed();
        $config = $this->jsonDecoder->decode($config);

        foreach ($subject->getAllowProducts() as $prod) {
            $prodId = $prod->getId();
            $product = $this->getProductById($prodId);
            $config["sprice"][$prodId] = number_format($product->getPrice(), '2', '.', '');
        }
        return $this->jsonEncoder->encode($config);
    }
}
