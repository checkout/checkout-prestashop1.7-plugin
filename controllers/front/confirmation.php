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

use Checkout\Models\Response;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;
use CheckoutCom\PrestaShop\Classes\CheckoutcomCustomerCard;

class CheckoutcomConfirmationModuleFrontController extends ModuleFrontController
{

    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        $flagged = false;
        $transaction_id = '';
        $status = 'Pending';

        $cart = new Cart((int) $cart_id);
        $customer = new Customer((int) $cart->id_customer);

        if (Tools::isSubmit('cko-session-id')) {
            $response = $this->_verifySession($_REQUEST['cko-session-id']);

            if ($response->isSuccessful() && !$response->isPending()) {
                $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
                if ($this->module->validateOrder(
                                                    $cart->id,
                                                    _PS_OS_PAYMENT_,
                                                    $total,
                                                    $this->module->displayName,
                                                    '',
                                                    array(),
                                                    (int) $cart->id_currency,
                                                    false,
                                                    $customer->secure_key
                                                )
                ) {
                    $this->context->order = new Order($this->module->currentOrder); // Add order to context. Experimental.
                } else {
                    \PrestaShopLogger::addLog("Failed to create order.", 2, 0, 'Cart' , $cart_id, true);
                    // Set error message
                    $this->context->controller->errors[] = $this->module->l('Payment method not supported. (0003)');
                    // Redirect to cartcontext
                    $this->redirectWithNotifications('index.php?controller=order&step=1&key=' . $customer->secure_key . '&id_cart=' . $cart->id);
                }

                $flagged = $response->isFlagged();
                $threeDS = $response->getValue(array('threeDs', 'enrolled')) === 'Y';

                $transaction_id = $response->id;;
                $reference = $this->context->order->getUniqReference();
                $status = $response->status;

                $context = \Context::getContext();

                if($context->cookie->__isset('save-card-checkbox') ){
                    CheckoutcomCustomerCard::saveCard($response,$context->customer->id);
                    $context->cookie->__unset('save-card-checkbox');
                }
            }
        } else {
            // Set error message
            $this->context->controller->errors[] = $this->trans('An error has occured while processing your transaction.', array(), 'Shop.Notifications.Error');
            // Redirect to cart
            $this->redirectWithNotifications(__PS_BASE_URI__ . 'index.php?controller=order&step=1&key=' . $secure_key . '&id_cart='
                . (int) $cart_id);
        }

        $message = null; // You can add a comment directly into the order so the merchant will see it in the BO.

        /**
         * If the order has been validated we try to retrieve it
         */
        $order_id = Order::getOrderByCartId((int) $cart->id);

        if ($order_id && ($secure_key == $customer->secure_key)) {
            /**
             * The order has been placed so we redirect the customer on the confirmation page.
             */
            $module_id = $this->module->id;

            /**
             * load order payment and set cko action id as order transaction id
             */

            $order = new Order($order_id);
            $payments = $order->getOrderPaymentCollection();
            $payments[0]->transaction_id = $transaction_id;
            $payments[0]->update();

            /**
             * Load the order history, change the status and send email confirmation
             */
            $orderStatus = $status === 'Captured' ? \Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS') : \Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS');

            // Flag Order
            if($flagged && $threeDS && !Utilities::addMessageToOrder($this->module->l('⚠️ This order is flagged as a potential fraud. We have proceeded with the payment, but we recommend you do additional checks before shipping the order.'), $order)) {
                \PrestaShopLogger::addLog('Failed to add payment flag note to order.', 2, 0, 'CheckoutcomPlaceorderModuleFrontController' , $order->id, true);
            }

            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . (int) $cart->id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key);
        } else {

            \PrestaShopLogger::addLog("Cart {$cart->id} didn't match any order.", 2, 0, 'Cart' , $cart_id, true);

            /*
             * An error occured and is shown on a new page.
             */
            $this->context->controller->errors[] = $this->trans('An error has occured while processing your transaction.', array(), 'Shop.Notifications.Error');
            // Redirect to cart
            $this->redirectWithNotifications(__PS_BASE_URI__ . 'index.php?controller=order&step=1&key=' . $secure_key . '&id_cart='
                . (int) $cart_id);
        }
    }

    private function _verifySession($session_id)
    {
        $response = new Response();

        try {
            // Get payment response
            $response = CheckoutApiHandler::api()->payments()->details($session_id);
        } catch (CheckoutHttpException $ex) {
            $response->http_code = $ex->getCode();
            $response->message = $ex->getMessage();
            $response->errors = $ex->getErrors();
        }

        return $response;
    }

}