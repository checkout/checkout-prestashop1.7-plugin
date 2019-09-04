<?php

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;

class CheckoutcomPaymentModuleFrontController extends ModuleFrontController
{
    /**
     * Layout
     *
     * @var        boolean
     */
    public $display_column_left = false;

    /**
     * Initialize the page.
     */
    public function init()
    {
        header('Content-Type: text/plain; charset=utf-8');
        parent::init();
    }

    /**
     * Most used methods.
     *
     * @var        array
     */
    const COMMOM_METHODS = array(
        array(
            'key' => 'card',
            'class' => CheckoutCom\PrestaShop\Models\Payments\Card::class
        ),
        array(
            'key' => 'apple',
            'class' => ''
        ),
        array(
            'key' => 'google',
            'class' => ''
        )
    );

    /**
     * Initiate content display.
     */
    public function initContent()
    {

        // Basic
        foreach (static::COMMOM_METHODS as $method) {
            if(Tools::getValue('source') === $method['key']) {
                $this->pay($method['class']);
            }
        }

        // ALternatives
        foreach (Config::definition('alternatives')[0] as $method) {
            if(Tools::getValue('source') === $method['key']) {
                $this->pay($method['class']);
            }
        }

        die(''); // Payment method not supported.

    }

    /**
     * Perform payment
     *
     * @param     string  $class  The class
     */
    protected function pay($class) {

        $response = $class::pay(Tools::getAllValues());
        if(!$response || !$response->isSuccessful()) {
            // redirect to error page
        }

print_r($response);
die();

    }

}