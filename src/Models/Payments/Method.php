<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use Checkout\Models\Payments\Method as MethodSource;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\ThreeDs;
use Checkout\Models\Payments\IdSource;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;
use Checkout\Models\Payments\BillingDescriptor;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;

abstract class Method {

	/**
	 * Ignore fields.
	 *
	 * @var        array
	 */
	const IGNORE_FIELDS = array('source', 'isolang', 'id_lang', 'module', 'controller', 'fc');


	/**
	 * Process payment.
	 *
	 * @param      array    $params  The parameters
	 *
	 * @return     Response  ( description_of_the_return_value )
	 */
	abstract public static function pay(array $params);

	/**
	 * Generate payment object.
	 *
	 * @param      \Checkout\Models\Payments\IdSource  $source  The source
	 *
	 * @return     Payment                             ( description_of_the_return_value )
	 */
	public static function makePayment(MethodSource $source) {

		$context = \Context::getContext();

		$payment = new Payment($source, $context->currency->iso_code);
		$payment->amount = (int)('' . ($context->cart->getOrderTotal() * 100)); //@todo: improve this

		//$payment->metadata = ['methodId' => $methodId];
		//$payment->reference = $order->getUniqReferenceOf();

        // Set the payment specifications
        $payment->capture = Config::needsAutoCapture();
        $payment->success_url = $context->link->getModuleLink('checkoutcom', 'verify', [], true);
        $payment->failure_url = $context->link->getModuleLink('checkoutcom', 'fail', [], true);
        $payment->description = Config::get('PS_SHOP_NAME') . ' Order';
        $payment->payment_type = 'Regular';

        // Security
        $payment->threeDs = new ThreeDs((bool) Config::get('CHECKOUTCOM_CARD_USE_3DS'));
        $payment->threeDs->attempt_n3d = (bool) Config::get('CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D');
        $payment->payment_ip = \Tools::getRemoteAddr();

        if (Config::get('CHECKOUTCOM_DYNAMIC_DESCRIPTOR_ENABLE')) {
        	$payment->billing_descriptor = new BillingDescriptor(Config::get('CHECKOUTCOM_DYNAMIC_DESCRIPTOR_NAME'), Config::get('CHECKOUTCOM_DYNAMIC_DESCRIPTOR_CITY'));
        }

		return $payment;

	}

	/**
	 * Make API request.
	 *
	 * @param      \Checkout\Models\Payments\Payment  $payment  The payment
	 *
	 * @return     <null|Response>
	 */
	protected static function request(Payment $payment) {

		$response = null;

		try{
			$response = CheckoutApiHandler::api()->payments()->request($payment);
		} catch(CheckoutHttpException $ex) {
			Debug::write($ex->getBody());
		}

		return $response;

	}

	/**
	 * Add extra params to source object.
	 *
	 * @param      \Checkout\Models\Payments\IdSource  $source  The source
	 * @param      array                               $params  The parameters
	 */
	protected static function setSourceAttributes(IdSource $source, array $params) {

		foreach ($params as $key => $value) {
			if(!in_array($key, static::IGNORE_FIELDS)) {
				$source->{$key} = $value;
			}
		}

	}

}