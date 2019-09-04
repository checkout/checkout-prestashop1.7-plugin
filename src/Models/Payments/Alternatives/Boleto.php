<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use CheckoutCom\PrestaShop\Helpers\Debug;
use Checkout\Models\Payments\BoletoSource;

class Boleto extends Alternative {

	/**
	 * Process payment.
	 *
	 * @param      array    $params  The parameters
	 *
	 * @return     Response  ( description_of_the_return_value )
	 */
	public static function pay(array $params) {

		$source = new BoletoSource($params['name'], $params['birthDate'],  $params['cpf']);
		$payment = static::makePayment($source);

		return static::request($payment);

	}

}
