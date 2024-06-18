<?php

namespace Cetelem\Payment\Utils\Callback;

class SuccessResponse extends CallbackResponse
{
	public function __construct(string $orderId) {
		$this->orderId = $orderId;
		$this->statusCode = 1;
		$this->statusText = "OK";
	}
}
