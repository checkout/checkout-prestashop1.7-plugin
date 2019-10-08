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

        $cart_id = $this->context->cart->id;
        $secure_key = $this->context->customer->secure_key;

        // Failed for varies reasons
        if(!$response->isSuccessful()) {
            // @todo: if token expired return to checkout

            // Set error message
            $this->context->controller->errors[] = $this->trans('An error has occured while processing your transaction.', array(), 'Shop.Notifications.Error');
            // Redirect to cart
            $this->redirectWithNotifications(__PS_BASE_URI__.'index.php?controller=order&step=1&key='.$secure_key.'&id_cart='
                .(int)$cart_id);
        }

        // Requires more action
        if($response->isPending()) {
            // todo refirect
            Tools::redirectLink($response->getRedirection());
        }

        // No problems, redirect to confirmation
        Tools::redirectLink($this->context->link->getModuleLink(
            'checkoutcom',
            'confirmation',
            [
                'cart_id' => $cart_id,
                'secure_key' => $secure_key,
                'payment_flagged' => $response->isFlagged(),
                'action_id' => $response->action_id
            ],
            true)
        );

    }

}