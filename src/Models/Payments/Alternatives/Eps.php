<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestEpsSource;

class Eps extends Alternative
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
        $source = new RequestEpsSource();
        $source->type = 'eps';
        $source->purpose = \Configuration::get('PS_SHOP_NAME');
        $payment = static::makePaymentToken($source);
        return static::request($payment);
    }
}
