<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use Checkout\CheckoutApi;
use Checkout\Models\Response;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;
use CheckoutCom\PrestaShop\Classes\CheckoutcomPaymentHandler;

class CheckoutcomPlaceorderModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {

        $cart = $this->context->cart;
        if (!$cart->id || $cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
            Tools::redirect('index.php?controller=order&step=1');

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == 'checkoutcom')
            {
                $authorized = true;
                break;
            }
        if (!$authorized)
            //@todo redirect to failed
            die('payment not autorized');

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        if ($this->module->validateOrder(
                                            $cart->id,
                                            _PS_OS_PREPARATION_,
                                            $total,
                                            $this->module->displayName,
                                            'message',
                                            array('mailid' => 'mailmess'),
                                            (int) $currency->id,
                                            false,
                                            $customer->secure_key
                                        )
        ) {

            $this->paymentProcess($customer);

        } else {

            //@todo: add log here

            // Set error message
            $this->context->controller->errors[] = $this->module->l('Payment method not supported.');
            // Redirect to cartcontext
            $this->redirectWithNotifications('index.php?controller=order&step=1&key=' . $customer->secure_key . '&id_cart=' . $cart->id);

        }

    }


    /**
     * Process payment
     *
     * @param      Customer  $customer  The customer
     */
    protected function paymentProcess(Customer $customer) {

        $response = CheckoutcomPaymentHandler::execute(Tools::getAllValues());
        if($response->isSuccessful()) {

            $url = $response->getRedirection();
            if($url)
                Tools::redirect($url);

            $status = Configuration::get('CHECKOUTCOM_PAYMENT_ACTION') ? Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS') : Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS');
            if($response->isFlagged()) {
                $status = Configuration::get('CHECKOUTCOM_FLAGGED_ORDER_STATUS');
            }

            $history = new OrderHistory();
            $history->id_order = Order::getOrderByCartId($this->context->cart->id);
            $history->changeIdOrderState($status, Order::getOrderByCartId($this->context->cart->id));

            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $this->context->cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key);

        } else {

            // Add log here

            $history = new OrderHistory();
            $history->id_order = Order::getOrderByCartId($this->context->cart->id);
            $history->changeIdOrderState(_PS_OS_ERROR_, Order::getOrderByCartId($this->context->cart->id));

            // Restore cart
            $duplication = $this->context->cart->duplicate();
            $this->context->cookie->id_cart = $duplication['cart']->id;
            $this->context->cookie->write();

            // Set error message
            $this->context->controller->errors[] = $this->module->l('@todo print error related to api call here.');
            // Redirect to cartcontext
            $this->redirectWithNotifications('index.php?controller=order');

        }

    }
}
