<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\EpsSource;

class Eps extends Alternative
{
	/**
     * Process payment.
     *
     * @param array $params The parameters
     *
     * @return Response
     */
    public static function pay()
    {
        $source = new EpsSource(\Configuration::get('PS_SHOP_NAME'));
        $payment = static::makePayment($source);
        return static::request($payment);
    }
}
