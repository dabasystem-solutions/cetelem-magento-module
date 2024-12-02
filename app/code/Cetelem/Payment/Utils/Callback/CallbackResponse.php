<?php

namespace Cetelem\Payment\Utils\Callback;

abstract class CallbackResponse
{
	protected int $statusCode;
	protected string $statusText;
	protected ?string $orderId = null;
	protected ?string $errorData = null;

	public function send()
	{
		$propoerties = get_object_vars($this);
		echo json_encode($propoerties);
	}
}
