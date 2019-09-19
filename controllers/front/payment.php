<?php

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;

class CheckoutcomPaymentModuleFrontController extends ModuleFrontController
{

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
     * Initialize the page.
     */
    public function init()
    {
        header('Content-Type: text/plain; charset=utf-8');
        parent::init();
    }

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

        // @redirect to error.
        die('Payment method not supported.');

    }

    /**
     * Perform payment
     *
     * @param     string  $class  The class
     */
    protected function pay($class) {

        $response = $class::pay(Tools::getAllValues());

        // Failed for varies reasons
        if(!$response->isSuccessful()) {
            // @todo: if token expired return to checkout
            print_r($response);
            die('falid could not make the payment');
        }

        // Requires more action
        if($response->isPending()) {
            // todo refirect
            Tools::redirectLink($response->getRedirection());
        }

        // No problems, redirect to confirmation
        $cart_id = $this->context->cart->id;
        $secure_key = $this->context->customer->secure_key;
        Tools::redirectLink($this->context->link->getModuleLink('checkoutcom', 'confirmation', ['cart_id' => $cart_id, 'secure_key' => $secure_key], true));

    }

}