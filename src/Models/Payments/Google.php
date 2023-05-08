<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use Checkout\Tokens\CardTokenRequest;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Checkout\Tokens\GooglePayTokenData;
use Checkout\Tokens\GooglePayTokenRequest;

class Google extends Method
{
    /**
     * Process payment.
     *
     * @param array $params The parameters
     *
     * @return Response ( description_of_the_return_value )
     */
    public static function pay(array $params)
    {
        //$response = parent::pay($params);

        $token = '';
        $payment = null;
        $data = json_decode($params['token'], true);
        // $data['type'] = 'googlepay';
        // $data = (object)$data;
        $google_pay            = new GooglePayTokenData();
		$google_pay->signature = $data['signature'];
        $google_pay->protocolVersion = $data['protocolVersion'];
		$google_pay->signedMessage   = $data['signedMessage'];
        $google_pay_token_request             = new GooglePayTokenRequest();
		$google_pay_token_request->token_data = $google_pay;
        //$googlepay = new GooglePay($data['protocolVersion'], $data['signature'], $data['signedMessage']);
        // print_r($request);
        // exit;
        try {
            
            $token = CheckoutApiHandler::token()->getTokensClient()->requestWalletToken( $google_pay_token_request );
        } catch (CheckoutHttpException $ex) {
            \PrestaShopLogger::addLog($ex->getBody(), 3, $ex->getCode(), 'checkoutcom' , 0, true);
        }

        if ($token) {

             $source  = (object)[];
            $source->type = 'token';
            $source->token = $token['token'];
            $payment = static::makePaymentToken($source);

            $threeDs = '3ds';
           // $payment->$threeDs->enabled = true;

            $response = static::request($payment);
        }

        return $response;
    }
}
