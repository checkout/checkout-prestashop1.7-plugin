<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use CheckoutCom\PrestaShop\Helpers\Debug;
use Checkout\Models\Payments\AlipaySource;

class Alipay extends Alternative
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
        $source = new AlipaySource();
        $payment = static::makePayment($source);
        return static::request($payment);
    }
}
