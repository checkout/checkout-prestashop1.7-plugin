<?php

namespace CheckoutCom\PrestaShop\Classes;

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
        array(
            'key' => 'id',
            'class' => 'CheckoutCom\\PrestaShop\\Models\\Payments\\Card',
        ),
        array(
            'key' => 'apple',
            'class' => 'CheckoutCom\\PrestaShop\\Models\\Payments\\Apple'
        ),
        array(
            'key' => 'google',
            'class' => 'CheckoutCom\\PrestaShop\\Models\\Payments\\Google',
        ),
    );

    public static function execute(array $params)
    {
        $module = \Module::getInstanceByName('checkoutcom');
        $module->logger->info('Channel Payment Handler -- Execute Payment');
        // Basic
        foreach (static::COMMOM_METHODS as $method) {
            if ($params['source'] === $method['key']) {
                return $method['class']::pay($params);
            }
        }

        // Alternatives
        foreach (Config::definition('alternatives')[0] as $method) {
            if ($params['source'] === $method['key']) {
                return $method['class']::pay($params);
            }
        }
    }

}
