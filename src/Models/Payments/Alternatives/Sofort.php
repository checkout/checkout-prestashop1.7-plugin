<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\SofortSource;

class Sofort extends Alternative
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
        $source = new SofortSource();
        $payment = static::makePayment($source);

        return static::request($payment);
    }
}
