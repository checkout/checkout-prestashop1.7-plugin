<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestKnetSource;

class Knet extends Alternative
{

    /**
     * Arabic locale.
     *
     * @var string
     */
    const LOCALE_AR = 'ar';

    /**
     * English locale.
     *
     * @var string
     */
    const LOCALE_EN = 'en';

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
        $language = substr($context->language->iso_code, 0, 2) !== Knet::LOCALE_AR ? Knet::LOCALE_EN : Knet::LOCALE_AR;
        $source = new RequestKnetSource();
        $source->language = $language;
        $source->type ='knet';
        $payment = static::makePaymentToken($source);

        return static::request($payment);
    }
}
