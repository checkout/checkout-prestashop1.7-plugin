<?php

namespace CheckoutCom\PrestaShop\Classes;

use Checkout\CheckoutApi;
use CheckoutCom\PrestaShop\Models\Config;

class CheckoutApiHandler
{
    /**
     * Checkout.com SDK Instance
     *
     * @var        CheckoutApi
     */
    protected static $api = null;

    /**
     * Initialize API.
     */
    public static function init()
    {
        CheckoutApiHandler::$api = new CheckoutApi(\Configuration::get('CHECKOUTCOM_SECRET_KEY'),
                                                    !\Configuration::get('CHECKOUTCOM_LIVE_MODE'),
                                                    \Configuration::get('CHECKOUTCOM_PUBLIC_KEY'));
    }

    /**
     * Access API.
     *
     * @return CheckoutApi
     */
    public static function api()
    {
        if (!CheckoutApiHandler::$api) {
            CheckoutApiHandler::init();
        }

        return CheckoutApiHandler::$api;
    }
}
