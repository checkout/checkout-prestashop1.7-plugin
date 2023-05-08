<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestSofortSource;

class Sofort extends Alternative
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
        $source = new RequestSofortSource();
        $source->type = 'sofort';
        $payment = static::makePaymentToken($source);

        return static::request($payment);
    }
}
