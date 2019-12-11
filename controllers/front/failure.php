<?php
/**
 * Checkout.com
 * Authorised and regulated as an electronic money institution
 * by the UK Financial Conduct Authority (FCA) under number 900816.
 *
 * PrestaShop v1.7
 *
 * @category  prestashop-module
 * @package   Checkout.com
 * @author    Platforms Development Team <platforms@checkout.com>
 * @copyright 2010-2020 Checkout.com
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://docs.checkout.com/
 */

class CheckoutcomFailureModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;

    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }

        $cart_id = Tools::getValue('cart_id');

        $history = new OrderHistory();
        $history->id_order = Order::getOrderByCartId($cart_id);
        $history->changeIdOrderState(_PS_OS_ERROR_, Order::getOrderByCartId($cart_id));

        // Restore cart
        $this->context->cart = new Cart($cart_id);
        $this->context->cookie->id_cart = $cart_id;

        $duplication = $this->context->cart->duplicate();
        $this->context->cookie->id_cart = $duplication['cart']->id;
        $this->context->cookie->write();

        // Set error message
        $this->context->controller->errors[] = $this->module->l('An error has occured while processing your payment.');
        // Redirect to cartcontext
        $this->redirectWithNotifications('index.php?controller=order');
    }

}
