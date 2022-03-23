<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\MultibancoSource;

class Multibanco extends Alternative
{
	/**
	 * Process payment.
	 *
	 * @param array $params The parameters
	 *
	 * @return Response
	 */
	public static function pay(array $params)
	{
		$payment_country = $_POST['payment_country'];
		$account_holder_name = $_POST['account_holder_name'];

		$source = new MultibancoSource($payment_country, $account_holder_name);
		$payment = static::makePayment($source);

		return static::request($payment);
	}
}
