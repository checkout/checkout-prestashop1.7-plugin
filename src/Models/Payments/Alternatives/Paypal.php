<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestPayPalSource;


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

        $source = new RequestPayPalSource();
        //$source->type = 'paypal';
        $source->plan = [ 'type' => 'MERCHANT_INITIATED_BILLING' ];
        $payment = static::makePaymentToken($source);

        return static::request($payment);
    }
}
