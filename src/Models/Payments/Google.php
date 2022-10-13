<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use Checkout\Models\Tokens\GooglePay;
use Checkout\Models\Payments\TokenSource;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;

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
        $response = parent::pay($params);

        $token = '';
        $payment = null;
        $data = json_decode($params['token'], true);
        $googlepay = new GooglePay($data['protocolVersion'], $data['signature'], $data['signedMessage']);

        try {
            $token = CheckoutApiHandler::api()->tokens()->request($googlepay);
        } catch (CheckoutHttpException $ex) {
            \PrestaShopLogger::addLog($ex->getBody(), 3, $ex->getCode(), 'checkoutcom' , 0, true);
        }

        if ($token) {
            $payment = static::makePayment(new TokenSource($token->getTokenId()));

            $threeDs = '3ds';
            $payment->$threeDs->enabled = true;

            $response = static::request($payment);
        }

        return $response;
    }
}
