<?php
namespace Cetelem\Payment\Model\System\Config\Source;

class Paymentmode
{
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('Agreement Default Value')],
            ['value' => '0', 'label' => '0'],
            ['value' => '1', 'label' => '1'],
            ['value' => '2', 'label' => '2'],
            ['value' => '3', 'label' => '3']
        ];
    }
}
