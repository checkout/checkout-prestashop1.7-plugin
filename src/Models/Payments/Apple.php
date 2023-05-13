<?php

namespace CheckoutCom\PrestaShop\Models\Payments;


use Checkout\Tokens\ApplePayTokenData;
use Checkout\Tokens\ApplePayTokenRequest;
use Checkout\Tokens\CardTokenRequest;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;

class Apple extends Method
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
        $token = '';
        $payment = null;
        $data = json_decode($params['token'], true);
        $request = new CardTokenRequest();
        $request->type = 'applepay';
        $request->token_data = $data;
        $transaction_id       = $data['header']['transactionId'];
		$public_key_hash      = $data['header']['publicKeyHash'];
		$ephemeral_public_key = $data['header']['ephemeralPublicKey'];
		$version              = $data['version'];
		$signature            = $data['signature'];
		$data                 = $data['data'];
        $header = [
			'transactionId'      => $transaction_id,
			'publicKeyHash'      => $public_key_hash,
			'ephemeralPublicKey' => $ephemeral_public_key,
		];

        $apple_pay_token_data            = new ApplePayTokenData();
        $apple_pay_token_data->data      = $data;
        $apple_pay_token_data->header    = $header;
        $apple_pay_token_data->signature = $signature;
        $apple_pay_token_data->version   = $version;

        $apple_pay_token_request             = new ApplePayTokenRequest();
        $apple_pay_token_request->token_data = $apple_pay_token_data;

			
        try {
            $token = CheckoutApiHandler::token()->getTokensClient()->requestWalletToken($apple_pay_token_request);
        } catch (CheckoutHttpException $ex) {
            \PrestaShopLogger::addLog($ex->getBody(), 3, $ex->getCode(), 'checkoutcom' , 0, true);
        }

        if ($token) {
            $source  = (object)[];
            $source->type = 'token';
            $source->token = $token['token'];
            $payment = static::makePaymentToken($source);
            $response = static::request($payment);
        }
        return $response;
    }
}