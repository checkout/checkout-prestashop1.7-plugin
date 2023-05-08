<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestMultiBancoSource;

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

		$source = new RequestMultiBancoSource();
		$source->type = 'multibanco';
		$source->payment_country = $payment_country;
		$source->account_holder_name = $account_holder_name;
		
		$payment = static::makePaymentToken($source);

		return static::request($payment);
	}
}
