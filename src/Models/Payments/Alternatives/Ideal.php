<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestIdealSource;

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
        $bic = self::checkBic($params['bic']);
        $source = new RequestIdealSource();
        $source->type = 'ideal';
        $source->description = 'Prestashop';
        $source->bic =$bic;
        //$bic, /*\Configuration::get('PS_SHOP_NAME')*/ 'iDEAL Payment');
        $payment = static::makePaymentToken($source);

        return static::request($payment);
    }

    /**
     * handle bank branch bic
     * 
     * @return String (bic)
     */
    public static function checkBic($bicInputByCustomer) {

        $bic = strlen($bicInputByCustomer) == 11 ? substr($bicInputByCustomer, 0, -3) : $bicInputByCustomer;

        return $bic;
    }
}
