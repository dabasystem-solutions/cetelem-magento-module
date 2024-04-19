<?php
namespace Cetelem\Payment\Model\System\Config\Source;

class Productcode
{
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' =>__('Agreement Default Value')],
            ['value' => 'PM', 'label' => __('Préstamo Mercantil (PM)')],
            ['value' => 'DP', 'label' => __('Asociado al acuerdo (DP)')]
        ];
    }
}
