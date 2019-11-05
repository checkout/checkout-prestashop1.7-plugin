<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\GiropaySource;
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
        $source = new GiropaySource(\Configuration::get('PS_SHOP_NAME'), Utilities::getValueFromArray($params, 'bic'));
        $payment = static::makePayment($source);

        return static::request($payment);
    }
}
