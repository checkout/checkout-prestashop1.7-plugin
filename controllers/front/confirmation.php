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

class CheckoutcomConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }


        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        $payment_flagged = Tools::getValue('payment_flagged');
        $transaction_id = Tools::getValue('action_id');

        $cart = new Cart((int) $cart_id);
        $customer = new Customer((int) $cart->id_customer);

        if(Tools::isSubmit('cko-session-id')){
            $response = $this->_verifySession($_REQUEST['cko-session-id']);

            if($response->isSuccessful()) {
                $payment_flagged = $response->isFlagged();
                $actions = $response->actions;
                $action_id = $actions[0]['id'];
                $transaction_id = $action_id;
            } else {
                // Set error message
                $this->context->controller->errors[] = $this->trans('An error has occured while processing your transaction.', array(), 'Shop.Notifications.Error');
                // Redirect to cart
                $this->redirectWithNotifications(__PS_BASE_URI__.'index.php?controller=order&step=1&key='.$secure_key.'&id_cart='
                    .(int)$cart_id);
            }
        }

        /**
         * Since it's an example we are validating the order right here,
         * You should not do it this way in your own module.
         */
        $payment_status = $payment_flagged == true ? Configuration::get('CHECKOUTCOM_FLAGGED_ORDER_STATUS') : Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS');
        $message = null; // You can add a comment directly into the order so the merchant will see it in the BO.

        /**
         * Converting cart into a valid order
         */
        $module_name = $this->module->displayName;
        $currency_id = (int) Context::getContext()->currency->id;

        $this->module->validateOrder(
            $cart_id,
            $payment_status,
            $cart->getOrderTotal(),
            $module_name,
            $message,
            array( 'transaction_id' => $transaction_id),
            $currency_id,
            false,
            $secure_key
        );

        /**
         * If the order has been validated we try to retrieve it
         */
        $order_id = Order::getOrderByCartId((int) $cart->id);

        if ($order_id && ($secure_key == $customer->secure_key)) {
            /**
             * The order has been placed so we redirect the customer on the confirmation page.
             */
            $module_id = $this->module->id;
            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key);
        } else {
            /*
             * An error occured and is shown on a new page.
             */
            $this->errors[] = $this->module->l('An error occured. Please contact the merchant to have more informations');

            return $this->setTemplate('error.tpl');
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
            Debug::write($ex->getBody());
        }

        return $response;
    }
}
