<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Models\Config;
use Checkout\Models\Payments\TokenSource;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\ThreeDs;

class Card extends Method {

	/**
	 * Process payment.
	 *
	 * @param      array    $params  The parameters
	 *
	 * @return     Response
	 */
	public static function pay(array $params) {

		$source = new TokenSource($params['token']);
		$payment = static::makePayment($source);

		static::setMada($payment, Utilities::getValueFromArray($params, 'bin', 0));

		print_r(static::request($payment));
		die();

		return static::request($payment);

	}

	/**
	 * Set MADA to card payments.
	 *
	 * @param      Payment  $payment  The payment
	 * @param      integer                            $bin      The bin
	 */
	protected static function setMada(Payment $payment, $bin = 0) {
Debug::write('Card.setMada');
Debug::write($bin);

		if ($bin && Config::get('CHECKOUTCOM_CARD_MADA_CHECK_ENABLED')) {
			$environment = Config::get('CHECKOUTCOM_LIVE_MODE') ? 'production' : 'sandbox';
Debug::write($environment);
			$list = json_decode(Utilities::getFile(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . $environment . 'json'), true);
Debug::write($list);
			foreach ($list as $value) {
Debug::write($value);
				if($value['bin'] === $bin) {
Debug::write($value['bin']);
					$payment->threeDs = new ThreeDs(true);
					$payment->metadata->udf1 = 'mada';
					unset($payment->capture);
					unset($payment->capture_on);
					return;

				}

			}

		}

	}

}
