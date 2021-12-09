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
		$source = new MultibancoSource();
		$payment = static::makePayment($source);

		$payment->source->payment_country = $_POST['payment_country'];
		$payment->source->account_holder_name = $_POST['account_holder_name'];

		return static::request($payment);
	}
}
