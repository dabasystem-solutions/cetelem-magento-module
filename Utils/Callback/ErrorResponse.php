<?php

namespace Cetelem\Payment\Utils\Callback;

class ErrorResponse extends CallbackResponse
{
	public function __construct(string $message) {
		$this->errorData = $message;
		$this->statusCode = 7;
		$this->statusText = "ERROR";
	}
}
