<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use Checkout\CheckoutApi;
use Checkout\Common\CustomerRequest;
use Checkout\Models\Response;
use Checkout\Payments\Request\PaymentRequest;
use Checkout\Models\Payments\ThreeDs;
use Checkout\Metadata\Card\Source\CardMetadataRequestSource;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use Checkout\Models\Payments\BillingDescriptor;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Checkout\Payments\RefundRequest;
use Checkout\Payments\Request\Source\RequestIdSource as IdSource; 
use Checkout\Payments\Request\Source\RequestTokenSource as TokenSource;
use Checkout\Payments\Previous\PaymentRequest as PreviousPaymentRequest;

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
        $response = (object)[];
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
    public static function makePaymentId($source, array $params = array(), bool $capture = true, $type = "card")
    {
        $module = \Module::getInstanceByName('checkoutcom');
       
        $module->logger->info(
                'Channel Method -- make Payment for source :'.$type,
                array('obj' => $source)
        );
        $context = \Context::getContext();
        $total = $context->cart->getOrderTotal();
        //$payment = new Payment($source, $context->currency->iso_code);
        $request = static::get_payment_request();
        $request->source =$source;
        $request->capture = (bool) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
        $request->reference ='CART_' . $context->cart->id;
        $request->amount = static::fixAmount($total, $context->currency->iso_code);
        $request->currency = $context->currency->iso_code;
        $request->customer = static::getCustomer($context, $params);
        //$request->sender = $paymentIndividualSender;

       // $payment->amount = static::fixAmount($total, $context->currency->iso_code);
        //$request->metadata = static::getMetadata($context);
       // $payment->customer = static::getCustomer($context, $params);
        $request->description = \Configuration::get('PS_SHOP_NAME') . ' Order';
        $request->payment_type = 'Regular';
       // $payment->reference = 'CART_' . $context->cart->id;

        // Set the payment specifications
        //$payment->capture = (bool) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
        $request->success_url = $context->link->getModuleLink(  'checkoutcom',
                                                                'confirmation',
                                                                ['cart_id' => $context->cart->id,
                                                                 'secure_key' => $context->customer->secure_key,
                                                                'source' => $type],
                                                                true);
        $request->failure_url = $context->link->getModuleLink(  'checkoutcom',
                                                                'failure',
                                                                ['cart_id' => $context->cart->id,
                                                                 'secure_key' => $context->customer->secure_key,
                                                                 'source' => $type],
                                                                true);
        try {
            $response = CheckoutApiHandler::api()->getPaymentsClient()->requestPayment($request);
            static::addThreeDs($response);
            static::addDynamicDescriptor($response);
            static::addCaptureOn($response);

        return $response;
        } catch (CheckoutApiException $e) {
            // API error
            $error_details = $e->error_details;
            $http_status_code = isset($e->http_metadata) ? $e->http_metadata->getStatusCode() : null;
        } catch (CheckoutAuthorizationException $e) {
            // Bad Invalid authorization
        }
        
    }

    public static function makePaymentToken($source, array $params = array(), bool $capture = true, $type = "card")
    {
        $module = \Module::getInstanceByName('checkoutcom');
       
        $module->logger->info(
                'Channel Method -- make Payment for source :'.$type,
                array('obj' => $source)
        );
        $context = \Context::getContext();
        $total = $context->cart->getOrderTotal();
        //$payment = new Payment($source, $context->currency->iso_code);
        $request = static::get_payment_request();
        $request->items = static::getProducts($context);
        $request->source =$source;
        $request->capture = (bool) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
        $request->reference ='CART_' . $context->cart->id;
        $request->amount = static::fixAmount($total, $context->currency->iso_code);
        $request->currency = $context->currency->iso_code;
        $request->customer = static::getCustomer($context, $params);
        $billing = new \Address((int) $context->cart->id_address_invoice);
        // print_r($billing);
        // exit;
        $request->shipping = (object) array("from_address_zip"=>$billing->postcode,'address'=>array("address_line1"=>$billing->address1,"city"=>$billing->city,"zip"=>$billing->postcode,"country"=>\Country::getIsoById($billing->id_country)));
        //$request->sender = $paymentIndividualSender;

       // $payment->amount = static::fixAmount($total, $context->currency->iso_code);
        //$request->metadata = static::getMetadata($context);
       // $payment->customer = static::getCustomer($context, $params);
        $request->description = \Configuration::get('PS_SHOP_NAME') . ' Order';
        $request->payment_type = 'Regular';
       // $payment->reference = 'CART_' . $context->cart->id;

        // Set the payment specifications
        //$payment->capture = (bool) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
        $request->success_url = $context->link->getModuleLink(  'checkoutcom',
                                                                'confirmation',
                                                                ['cart_id' => $context->cart->id,
                                                                 'secure_key' => $context->customer->secure_key,
                                                                'source' => $type],
                                                                true);
        $request->failure_url = $context->link->getModuleLink(  'checkoutcom',
                                                                'failure',
                                                                ['cart_id' => $context->cart->id,
                                                                 'secure_key' => $context->customer->secure_key,
                                                                 'source' => $type],
                                                                true);

            print_r($request);
            exit;                                                    
        try {
            $response = CheckoutApiHandler::api()->getPaymentsClient()->requestPayment($request);
           
           // static::addThreeDs($response);
           // static::addDynamicDescriptor($response);
            //static::addCaptureOn($response);

        return $response;
        } catch (CheckoutApiException $e) {
            // API error
            $error_details = $e->error_details;
            $http_status_code = isset($e->http_metadata) ? $e->http_metadata->getStatusCode() : null;
        } catch (CheckoutAuthorizationException $e) {
            // Bad Invalid authorization
        }
        
    }

    public static function get_payment_request() {
		if ( \Configuration::get('CHECKOUTCOM_SERVICE') == 0) {
            return new PaymentRequest();
			
		} else {
			return new PreviousPaymentRequest();
		}
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
        $metadata = new CardMetadataRequestSource();

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
        $customer = new CustomerRequest();
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
    protected static function request($payment)
    {
        //$response = new Response();
        $module = \Module::getInstanceByName('checkoutcom');
        $module->logger->info(
                'Channel Method -- Request Payment :',
                array('obj' => $payment)
        );
        try {
            $response = $payment;
        } catch (CheckoutHttpException $ex) {
            $response->http_code = $ex->getCode();
            $response->message = $ex->getMessage();
            $response->errors = $ex->getErrors();
            \PrestaShopLogger::addLog($ex->getMessage(), 3, $ex->getCode(), 'checkoutcom' , 0, true);
        }
        $module->logger->info(
                'Channel Method -- Response Payment :',
                array('obj' => $response)
        );

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
        $event = (float) \Configuration::get('CHECKOUTCOM_PAYMENT_EVENT');
        $action = (float) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
        if ($time && $event && !$action) {
            $payment->capture = true;
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

        $threeDs = '3ds';
        $payment->$threeDs = $payment->threeDs;
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
            $details = CheckoutApiHandler::api()->getPaymentsClient()->getPaymentDetails($cko_payment_id);
            // print_r($details);
            // exit;
            if ($details['status'] == 'Refunded') {
                return false;
            }

            $request = new RefundRequest();
            $request->reference = "reference";
            

            //$ckoPayment = new Refund($cko_payment_id);

            if(isset($params['amount'])){
                $request->amount = static::fixAmount($params['amount'], $params['currency_code']);
            }
            
            $response = CheckoutApiHandler::api()->getPaymentsClient()->refundPayment($cko_payment_id, $request);

            if (!isset($response['action_id'])) {
                //@todo return error message
            } else {
               
                return $response;
            }
        } catch (CheckoutHttpException $ex) {

        }

        return false;
    }
    
    /**
     *
     */
    public static function getBanks($source)
    {
        $response = CheckoutApiHandler::api()->payments()->banks($source);
        return $response;
    }

    public static function getProducts(\Context $context)
    {
        $products = array();
        foreach ($context->cart->getProducts() as $item) {
            $product =  (object)[];
            $product->name = $item['name'];
            $product->quantity = (int) $item['cart_quantity'];
            $product->unit_price = (int) ('' . ($item['price_wt'] * 100));
            $product->tax_rate = (int) ('' . ($item['rate'] * 100));
            $product->total_amount = (int) ('' . ($item['total_wt'] * 100));
            $product->total_tax_amount = (int) ('' . (($item['total_wt'] - $item['total']) * 100));

            $products[] = $product;
        }

        $shipping = static::getShipping($context);
        if($shipping) {
            $products []= $shipping;
        }
        return $products;
    }

    public static function getShipping(\Context $context)
    {
        $product = null;
        if($context->cart->id_carrier) {

            $carrier = new \Carrier($context->cart->id_carrier, $context->cart->id_lang);
           
            $product = (object)[];
            $product->name = $carrier->name;
            $product->quantity = 1;
            $product->tax_rate = (int) $carrier->getTaxesRate(new \Address((int) $context->cart->id_address_delivery)) * 100;
            $product->unit_price = static::fixAmount($context->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING));
            $product->total_amount = $product->unit_price;
            $product->total_tax_amount = $product->unit_price - static::fixAmount($context->cart->getOrderTotal(false, \Cart::ONLY_SHIPPING));
            $product->type = 'shipping_fee';
            if($product->unit_price==0){
                $product = null;
            }
        }

        return $product;
    }

}
