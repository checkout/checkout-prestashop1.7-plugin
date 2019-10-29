<?php

namespace CheckoutCom\PrestaShop\Classes;

use Checkout\Models\Response;
use CheckoutCom\PrestaShop\Models\Config;

class CheckoutcomPaymentHandler
{
    /**
     * Most used methods.
     *
     * @var array
     */
    const COMMOM_METHODS = array(
        array(
            'key' => 'card',
            'class' => 'CheckoutCom\\PrestaShop\\Models\\Payments\\Card',
        ),
        // array(
        //     'key' => 'apple',
        //     'class' => ''
        // ),
        array(
            'key' => 'google',
            'class' => 'CheckoutCom\\PrestaShop\\Models\\Payments\\Google',
        ),
    );

    public static function execute(array $params)
    {
        // Basic
        foreach (static::COMMOM_METHODS as $method) {
            if ($params['source'] === $method['key']) {
                return static::pay($method['class'], $params);
            }
        }

        // Alternatives
        foreach (Config::definition('alternatives')[0] as $method) {
            if ($params['source'] === $method['key']) {
                return static::pay($method['class'], $params);
            }
        }
    }

    /**
     * Perform payment
     *
     * @param string $class The class
     */
    protected static function pay($class, $params)
    {
        $response = $class::pay($params);

        if (!$response) {
            $response = new Response();
            // @todo: add error message
        }

        return $response;
    }
}
