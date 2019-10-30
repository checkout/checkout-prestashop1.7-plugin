<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use CheckoutCom\PrestaShop\Models\Payments\Method;

abstract class Alternative extends Method
{
    /**
     * Load variabels
     *
     * @return array
     */
    public static function assign()
    {
        return array();
    }
}
