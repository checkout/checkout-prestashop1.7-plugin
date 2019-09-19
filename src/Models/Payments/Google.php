<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;
use Checkout\Models\Payments\TokenSource;

class Google extends Method {

	/**
	 * Process payment.
	 *
	 * @param      array    $params  The parameters
	 *
	 * @return     Response  ( description_of_the_return_value )
	 */
	public static function pay(array $params) {

		$source = new TokenSource($params['token']);
		$payment = static::makePayment($source);

		return static::request($payment);

	}

}
