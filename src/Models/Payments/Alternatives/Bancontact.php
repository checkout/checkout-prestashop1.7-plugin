<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestBancontactSource;

class Bancontact extends Alternative
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
        $billing = new \Address((int) $context->cart->id_address_invoice);
        $source = new RequestBancontactSource();
        $source->type= 'bancontact';
        $source->payment_country = \Country::getIsoById($billing->id_country);
        $source->account_holder_name = $context->customer->firstname . ' ' . $context->customer->lastname;
        $source->billing_descriptor = \Configuration::get('PS_SHOP_NAME');
        $payment = static::makePaymentToken($source);

        return static::request($payment);
    }
}
