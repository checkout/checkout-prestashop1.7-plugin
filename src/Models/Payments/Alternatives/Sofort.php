<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use CheckoutCom\PrestaShop\Helpers\Debug;
use Checkout\Models\Payments\SofortSource;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;

class Sofort extends Alternative {

	/**
	 * Process payment.
	 *
	 * @param      array    $params  The parameters
	 *
	 * @return     Response  ( description_of_the_return_value )
	 */
	public static function pay(array $params) {

		$source = new SofortSource();
		$payment = static::makePayment($source);

		return static::request($payment);

	}

}