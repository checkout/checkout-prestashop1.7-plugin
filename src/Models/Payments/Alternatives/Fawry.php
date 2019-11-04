<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\FawrySource;

class Fawry extends Alternative
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
        $source = new FawrySource();
        $payment = static::makePayment($source);
        return static::request($payment);
    }
}
