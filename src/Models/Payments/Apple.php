<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use Checkout\Models\Tokens\ApplePay;
use Checkout\Models\Tokens\ApplePayHeader;
use Checkout\Models\Payments\TokenSource;
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
        $header = new ApplePayHeader($data['header']['transactionId'], $data['header']['publicKeyHash'], $data['header']['ephemeralPublicKey']);
        $applepay = new ApplePay($data['version'], $data['signature'], $data['data'], $header);
        try {
            $token = CheckoutApiHandler::api()->tokens()->request($applepay);
        } catch (CheckoutHttpException $ex) {
            \PrestaShopLogger::addLog($ex->getBody(), 3, $ex->getCode(), 'checkoutcom' , 0, true);
        }

        if ($token) {
            $payment = static::makePayment(new TokenSource($token->getTokenId()), array(), true, $params['source']);
            $response = static::request($payment);
        }
        return $response;
    }
}