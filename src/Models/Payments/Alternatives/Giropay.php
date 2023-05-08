<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestGiropaySource;
use CheckoutCom\PrestaShop\Helpers\Utilities;

class Giropay extends Alternative
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
        $source = new RequestGiropaySource();
        $source->purpose = 'Giropay by Checkout.com';
        $payment = static::makePaymentToken($source);

        return static::request($payment);
    }
}
