<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use CheckoutCom\PrestaShop\Models\Config;
use Checkout\Models\Payments\IdealSource;

class Ideal extends Alternative
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
        $source = new IdealSource($params['bic'], Config::get('PS_SHOP_NAME'));
        $payment = static::makePayment($source);

        return static::request($payment);
    }
}
