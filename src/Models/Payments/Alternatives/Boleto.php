<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\BoletoSource;

class Boleto extends Alternative
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
        $context = \Context::getContext();
        $source = new BoletoSource( $context->customer->firstname . ' ' . $context->customer->lastname,
                                    $params['birthDate'],
                                    $params['cpf']  );
        $payment = static::makePayment($source);

        return static::request($payment);
    }
}
