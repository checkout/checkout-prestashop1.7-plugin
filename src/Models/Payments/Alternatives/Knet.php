<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\KnetSource;
use CheckoutCom\PrestaShop\Helpers\Debug;

class Knet extends Alternative {

	/**
	 * Process payment.
	 *
	 * @param      array    $params  The parameters
	 *
	 * @return     Response  ( description_of_the_return_value )
	 */
	public static function pay(array $params) {

		$context = \Context::getContext();
		$source = new KnetSource($context->language->locale);
		$payment = static::makePayment($source);

		return static::request($payment);

	}

}