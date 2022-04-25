<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\PaypalSource;

class Paypal extends Alternative
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
        $invoice_number = 'CKO_' . $_POST['cart_id'];

        $source = new PaypalSource($invoice_number);
        $payment = static::makePayment($source);

        return static::request($payment);
    }
}
