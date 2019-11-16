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
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Classes\CheckoutcomPaymentHandler;
use CheckoutCom\PrestaShop\Classes\CheckoutcomCustomerCard;

class CheckoutcomPlaceorderModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
        if (!$cart->id || $cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            $this->context->controller->errors[] = $this->module->l('Missing information for checkout.');
            $this->redirectWithNotifications('index.php?controller=order');
            return;
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'checkoutcom') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            // Set error message
            $this->context->controller->errors[] = $this->module->l('Payment method not supported. (0001)');
            $this->redirectWithNotifications('index.php?controller=order');
            return;
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            // Set error message
            $this->context->controller->errors[] = $this->module->l('Payment method not supported. (0002)');
            Tools::redirect('index.php?controller=order&step=1');
            return;
        }

        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        if ($this->module->validateOrder(
                                            $cart->id,
                                            _PS_OS_PREPARATION_,
                                            $total,
                                            $this->module->displayName,
                                            '',
                                            array(),
                                            (int) $this->context->cart->id_currency,
                                            false,
                                            $customer->secure_key
                                        )
        ) {
            $this->paymentProcess($customer);
        } else {

            \PrestaShopLogger::addLog("Failed to create order.", 2, 0, 'Cart' , $cart_id, true);

            // Set error message
            $this->context->controller->errors[] = $this->module->l('Payment method not supported. (0003)');
            // Redirect to cartcontext
            $this->redirectWithNotifications('index.php?controller=order&step=1&key=' . $customer->secure_key . '&id_cart=' . $cart->id);
        }
    }

    /**
     * Process payment
     *
     * @param Customer $customer The customer
     */
    protected function paymentProcess(Customer $customer)
    {
        $response = CheckoutcomPaymentHandler::execute(Tools::getAllValues());
        if ($response->isSuccessful()) {
            $url = $response->getRedirection();
            if ($url) {
                if(Tools::getIsset('save-card-checkbox')){
                    $context = \Context::getContext();
                    $context->cookie->__set('save-card-checkbox', '1');
                    $context->cookie->write();
                }

                Tools::redirect($url);
                return;
            }

            // check if save card option was checked on checkout page
            if(Tools::getIsset('save-card-checkbox')){
                CheckoutcomCustomerCard::saveCard($response, $customer->id);
            }


            $status = Configuration::get('CHECKOUTCOM_PAYMENT_ACTION') ? Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS') : Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS');
            if ($response->isFlagged()) {
                $status = Configuration::get('CHECKOUTCOM_FLAGGED_ORDER_STATUS');
            }

            $history = new OrderHistory();
            $history->id_order = Order::getOrderByCartId($this->context->cart->id);
            $history->changeIdOrderState($status, Order::getOrderByCartId($this->context->cart->id));

            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $this->context->cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key);
        } else {
            \PrestaShopLogger::addLog("Payment for order not processed.", 3, 0, 'checkoutcom' , $this->module->currentOrder, true);

            $history = new OrderHistory();
            $history->id_order = Order::getOrderByCartId($this->context->cart->id);
            $history->changeIdOrderState(_PS_OS_ERROR_, Order::getOrderByCartId($this->context->cart->id));

            // Restore cart
            $duplication = $this->context->cart->duplicate();
            $this->context->cookie->id_cart = $duplication['cart']->id;
            $this->context->cookie->write();

            // Set error message
            $this->context->controller->errors[] = $this->module->l($response->message);
            // Redirect to cartcontext
            $this->redirectWithNotifications('index.php?controller=order');
        }
    }
}
