<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use Checkout\CheckoutApi;
use Checkout\Models\Customer;
use Checkout\Models\Response;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\ThreeDs;
use Checkout\Models\Payments\IdSource;
use Checkout\Models\Payments\Metadata;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use Checkout\Models\Payments\BillingDescriptor;
use Checkout\Models\Payments\Method as MethodSource;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Checkout\Models\Payments\Refund;

abstract class Method
{
    /**
     * Ignore fields.
     *
     * @var array
     */
    const IGNORE_FIELDS = array('source', 'isolang', 'id_lang', 'module', 'controller', 'fc');

    /**
     * Process payment.
     *
     * @param array $params The parameters
     *
     * @return Response
     *
     * @note		Cannot be abstract static after PHP 5.2.
     */
    public static function pay(array $params)
    {
        $response = new Response();
        $response->http_code = 400;
        $response->errors = array(Utilities::getValueFromArray($params, 'source', 'Payment method') . ' in development.');
        $response->message = $response->errors[0];

        return $response;
    }

    /**
     * Generate payment object.
     *
     * @param \Checkout\Models\Payments\IdSource $source The source
     * @param array     $params Parameters from the request.
     *
     * @return Payment
     */
    public static function makePayment(MethodSource $source, array $params = array())
    {
        $context = \Context::getContext();
        $total = $context->cart->getOrderTotal();
        $payment = new Payment($source, $context->currency->iso_code);

        $payment->amount = static::fixAmount($total, $context->currency->iso_code);
        $payment->metadata = static::getMetadata($context);
        $payment->customer = static::getCustomer($context, $params);
        $payment->description = \Configuration::get('PS_SHOP_NAME') . ' Order';
        $payment->payment_type = 'Regular';
        $payment->reference = 'CART_' . $context->cart->id;

        // Set the payment specifications
        $payment->capture = (bool) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
        $payment->success_url = $context->link->getModuleLink(  'checkoutcom',
                                                                'confirmation',
                                                                ['cart_id' => $context->cart->id,
                                                                 'secure_key' => $context->customer->secure_key],
                                                                true);
        $payment->failure_url = $context->link->getModuleLink(  'checkoutcom',
                                                                'failure',
                                                                ['cart_id' => $context->cart->id,
                                                                 'secure_key' => $context->customer->secure_key],
                                                                true);

        static::addThreeDs($payment);
        static::addDynamicDescriptor($payment);
        static::addCaptureOn($payment);

        return $payment;
    }

    /**
     * Turn amount into integer according to currency.
     *
     * @param float $amount The amount
     * @param string $currency The currency
     *
     * @return int
     */
    public static function fixAmount($amount, $currency = '', $reverse = false)
    {
        $multiplier = 100;
        $full = array('BYN', 'BIF', 'DJF', 'GNF', 'ISK', 'KMF', 'XAF', 'CLF', 'XPF', 'JPY', 'PYG', 'RWF', 'KRW', 'VUV', 'VND', 'XOF');
        $thousands = array('BHD', 'LYD', 'JOD', 'KWD', 'OMR', 'TND');

        if (in_array($currency, $thousands)) {
            $multiplier = 1000;
        } elseif (in_array($currency, $full)) {
            $multiplier = 1;
        }

        if($reverse) {
            $price = \Tools::ps_round($amount / $multiplier);
        } else {
            $price = (int) ('' . ($amount * $multiplier)); //@todo: Waiting on SDK precision fix. (#41)
        }

        if ($currency === 'CLP') {
            //@todo: fix this.
        }

        return $price;
    }

    /**
     * Get Meta information.
     *
     * @param \Context $context The context
     *
     * @return Metadata the metadata
     */
    protected static function getMetadata(\Context $context)
    {
        $metadata = new Metadata();

        $module = \Module::getInstanceByName('checkoutcom');
        
        $udf5 = "Platform Data - PrestaShop " . _PS_VERSION_
        . ", Integration Data - Checkout.com " . $module->version . ", SDK Data - PHP SDK ". CheckoutApi::VERSION 
        .", Server - " . \Tools::getHttpHost(true);

        $metadata->udf5 = $udf5;

        return $metadata;
    }

    /**
     * Get Customer information.
     *
     * @param \Context $context The context
     *
     * @return Customer the metadata
     */
    protected static function getCustomer(\Context $context, array $params)
    {
        $customer = new Customer();
        $customer->email = $context->customer->email;
        $customer->name = $context->customer->firstname . ' ' . $context->customer->lastname;

        return $customer;
    }

    /**
     * Make API request.
     *
     * @param \Checkout\Models\Payments\Payment $payment The payment
     *
     * @return <null|Response>
     */
    protected static function request(Payment $payment)
    {
        $response = new Response();

        try {
            $response = CheckoutApiHandler::api()->payments()->request($payment);
        } catch (CheckoutHttpException $ex) {
            $response->http_code = $ex->getCode();
            $response->message = $ex->getMessage();
            $response->errors = $ex->getErrors();
            \PrestaShopLogger::addLog($ex->getMessage(), 3, $ex->getCode(), 'checkoutcom' , 0, true);
        }

        return $response;
    }

    /**
     * Add extra params to source object.
     *
     * @param \Checkout\Models\Payments\IdSource $source The source
     * @param array $params The parameters
     */
    protected static function setSourceAttributes(IdSource $source, array $params)
    {
        foreach ($params as $key => $value) {
            if (!in_array($key, static::IGNORE_FIELDS)) {
                $source->{$key} = $value;
            }
        }
    }

    /**
     * Helper methods.
     */

    /**
     * Adds a capture on.
     *
     * @param \Checkout\Models\Payments\Payment $payment The payment
     */
    public static function addCaptureOn(Payment $payment)
    {
        $time = (float) \Configuration::get('CHECKOUTCOM_CAPTURE_TIME');
        if ($time && \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION')) {
            $payment->capture_on = Utilities::formatDate(time() + ($time >= 0.0027 ? $time : 0.0027) * 3600);
        }
    }

    /**
     * Adds a dynamic descriptor.
     *
     * @param \Checkout\Models\Payments\Payment $payment The payment
     */
    public static function addDynamicDescriptor(Payment $payment)
    {
        if (\Configuration::get('CHECKOUTCOM_DYNAMIC_DESCRIPTOR_ENABLE')) {
            $payment->billing_descriptor = new BillingDescriptor(
                \Configuration::get('CHECKOUTCOM_DYNAMIC_DESCRIPTOR_NAME'),
                \Configuration::get('CHECKOUTCOM_DYNAMIC_DESCRIPTOR_CITY')
            );
        }
    }

    /**
     * Adds a 3DS and IP.
     *
     * @param \Checkout\Models\Payments\Payment $payment The payment
     */
    public static function addThreeDs(Payment $payment)
    {
        // Security
        $payment->payment_ip = \Tools::getRemoteAddr();
        $payment->threeDs = new ThreeDs((bool) \Configuration::get('CHECKOUTCOM_CARD_USE_3DS'));

        if ($payment->threeDs->enabled) {
            $payment->threeDs->attempt_n3d = (bool) \Configuration::get('CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D');
        }
    }

    /**
     * @param array $params
     * @return bool|mixed
     */
    public static function makeRefund(array $params)
    {
        $cko_payment_id = $params['payment_id'];

        // Check if cko_payment_id is empty
        if(empty($cko_payment_id)){
            return false;
        }

        try {
            // Check if payment is already voided or captured on checkout.com hub
            $details = CheckoutApiHandler::api()->payments()->details($cko_payment_id);

            if ($details->status == 'Refunded') {
                return false;
            }

            $ckoPayment = new Refund($cko_payment_id);

            if(isset($params['amount'])){
                $ckoPayment->amount = static::fixAmount($params['amount'], $params['currency_code']);
            }

            $response = CheckoutApiHandler::api()->payments()->refund($ckoPayment);

            if (!$response->isSuccessful()) {
                //@todo return error message
            } else {
                return $response;
            }
        } catch (CheckoutHttpException $ex) {

        }

        return false;
    }
}
