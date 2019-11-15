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
use Checkout\Models\Response;
use CheckoutCom\PrestaShop\Helpers\Debug;
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
        $payment_flagged = false;
        $transaction_id = '';
        $status = 'Pending';

        $cart = new Cart((int) $cart_id);
        $customer = new Customer((int) $cart->id_customer);

        if (Tools::isSubmit('cko-session-id')) {
            $response = $this->_verifySession($_REQUEST['cko-session-id']);

            if ($response->isSuccessful() && !$response->isPending()) {

                $payment_flagged = $response->isFlagged();
                if(isset($response->actions)) {
                    $actions = $response->actions;
                    $action_id = $actions[0]['id'];
                    $transaction_id = $action_id;
                }
                $reference = $response->reference;
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

        $payment_status = $payment_flagged == true ? Configuration::get('CHECKOUTCOM_FLAGGED_ORDER_STATUS') : $this->getOrderStatus($status);
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
             * load order history and change status
             */
            $history = new OrderHistory();
            $history->id_order = Order::getOrderByCartId((int) $cart->id);
            $history->changeIdOrderState($payment_status, Order::getOrderByCartId((int) $cart->id));

            /**
             * load order payment and set cko action id as order transaction id
             */
            $order = new Order($order_id);
            $payments = $order->getOrderPaymentCollection();
            $payments[0]->transaction_id = $transaction_id;
            $payments[0]->update();

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

    /**
     * Gets the order status.
     *
     * @param string $status The status
     *
     * @return int the order status
     */
    protected function getOrderStatus($status)
    {
        switch ($status) {
            case 'Captured':
            case 'Partially Captured':
                return Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS');
            case 'Declined':
                return _PS_OS_ERROR_;
            case 'Cancelled':
                return _PS_OS_CANCELED_;
            case 'Voided':
                return Configuration::get('CHECKOUTCOM_VOID_ORDER_STATUS');
            case 'Refunded':
            case 'Partially Refunded':
                return Configuration::get('CHECKOUTCOM_REFUND_ORDER_STATUS');
            case 'Pending':
                return _PS_OS_PREPARATION_;
            default:
                return Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS');
        }
    }
}
