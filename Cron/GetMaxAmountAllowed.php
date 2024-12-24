<?php
namespace Cetelem\Payment\Cron;

use Cetelem\Payment\Api\PaymentInterface;
use Cetelem\Payment\Helper\Data;
use Cetelem\Payment\Logger\Logger;
use Exception;
use Magento\Framework\App\Config\Storage\WriterInterface;

class GetMaxAmountAllowed implements PaymentInterface
{
    /**
     * @var WriterInterface
     */
    protected $configWriter;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param WriterInterface $configWriter
     * @param Data $helper
     * @param Logger $logger
     */
    public function __construct(
        WriterInterface $configWriter,
        Data $helper,
        Logger $logger
    ) {
        $this->configWriter = $configWriter;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $extUrl = $this->helper
                ->getConfig(
                    PaymentInterface::ENCUOTAS . PaymentInterface::EXTERNAL_URL_ENCUOTAS
                );
            $value  = file_get_contents($extUrl);
            $this->configWriter->save(PaymentInterface::ENCUOTAS . PaymentInterface::MAX_AMOUNT, $value);
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
