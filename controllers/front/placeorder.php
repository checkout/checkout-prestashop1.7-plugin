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
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Classes\CheckoutcomCustomerCard;
use CheckoutCom\PrestaShop\Classes\CheckoutcomPaymentHandler;

class CheckoutcomPlaceorderModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
        if (!$cart->id || $cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            $this->context->controller->errors[] = $this->trans('Missing information for checkout.', [], 'Modules.Checkoutcom.Placeorder.php');
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
            $this->context->controller->errors[] = $this->trans('Payment method not supported. (0001)', [], 'Modules.Checkoutcom.Placeorder.php');
            $this->redirectWithNotifications('index.php?controller=order');
            return;
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            // Set error message
            $this->context->controller->errors[] = $this->trans('Payment method not supported. (0002)', [], 'Modules.Checkoutcom.Placeorder.php');
            Tools::redirect('index.php?controller=order&step=1');
            return;
        }

        $this->paymentProcess($customer);
    }

    /**
     * Process payment
     *
     * @param Customer $customer The customer
     */
    protected function paymentProcess(Customer $customer)
    {
        $source_type = Tools::isSubmit('source')?Tools::getValue('source'):"card";
        $this->module->logger->info('Channel Placeorder -- source type : ' . $source_type);
        $response = CheckoutcomPaymentHandler::execute(Tools::getAllValues());
        if ($response->isSuccessful()) {
            $this->module->logger->info('Channel Placeorder -- payment process : response isSuccessful');
            $this->module->logger->info(
                'Channel Placeorder -- Response :',
                array('obj' => $response)
        );
            // Flag Order
            if($response->isFlagged() && !Utilities::addMessageToOrder($this->trans('⚠️ This order is flagged as a potential fraud. We have proceeded with the payment, but we recommend you do additional checks before shipping the order.' , [], 'Modules.Checkoutcom.Placeorder.php'), $this->context->order)) {
				$this->module->logger->error('Channel Placeorder -- Failed to add payment flag note to order');
                \PrestaShopLogger::addLog('Failed to add payment flag note to order.', 2, 0, 'CheckoutcomPlaceorderModuleFrontController' , $this->context->order->id, true);
            }

            $url = $response->getRedirection();
            if ($url) {
                //log redirect url
                $this->module->logger->info('Channel Placeorder -- Redirection to : ' . $url);

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

                //log customer id
                $this->module->logger->info('Channel Placeorder -- save card  -- Customer id : ' . $customer->id);

                CheckoutcomCustomerCard::saveCard($response, $customer->id);
            }

            if ( !isset( $this->module->currentOrder ) ) {
                $cart = new Cart((int) $this->context->cart->id);
                $customer = new Customer((int) $cart->id_customer);
                $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

                //log cart and customer info
                $this->module->logger->info(sprintf('Channel Placeorder -- Create order for cart %s ', $cart->id));
                $this->module->logger->info('Channel Placeorder --  Create order for Customer id : ' . $cart->id_customer);
                $this->module->logger->info('Channel Placeorder --  Create order for amount : ' .  $total);

                $suffix = '-card';
                if($source_type === 'apple'){
                    $suffix = '-apay';
                }

                if ($this->module->validateOrder(
                                                    $cart->id,
                                                    _PS_OS_PAYMENT_,
                                                    $total,
                                                    $this->module->displayName.$suffix,
                                                    '',
                                                    array(),
                                                    (int) $cart->id_currency,
                                                    false,
                                                    $customer->secure_key
                                                )
                ) {
                    $this->context->order = new Order($this->module->currentOrder); // Add order to context. Experimental.
                } else {
					$this->module->logger->error(sprintf('Channel Placeorder -- Failed to create order for cart %s', $cart->id));
                    \PrestaShopLogger::addLog("Failed to create order.", 2, 0, 'Cart' , $cart_id, true);
                    // Set error message
                    $this->context->controller->errors[] = $this->module->l('Payment method not supported. (0004)');
                    // Redirect to cartcontext
                    $this->redirectWithNotifications('index.php?controller=order&step=1&key=' . $customer->secure_key . '&id_cart=' . $cart->id);
                }
            }

            /**
             * load order payment and set cko action id as order transaction id
             */
            $payments = $this->context->order->getOrderPaymentCollection();
            $payments[0]->transaction_id = $response->id;
            $payments[0]->update();

            //log payment transaction id
            $this->module->logger->info('Channel Placeorder --  payment transaction id : ' .  $response->id);
           

            // Reset order history
            $sql = 'DELETE FROM `'._DB_PREFIX_.'order_history` WHERE `id_order`='.$this->context->order->id;
            Db::getInstance()->execute($sql);

            $history = new OrderHistory();
            $history->id_order = $this->context->order->id;
            $history->changeIdOrderState(\Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS'), $this->context->order->id, true);
            $history->add();
            
            //Log order history
            $this->module->logger->info('Channel Placeorder -- New order id : ' . $this->context->order->id);
            $this->module->logger->info('Channel Placeorder -- New order status : ' .  \Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS'));
           


            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $this->context->cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key);
        } else {
            //log failure
            $this->module->logger->info('Channel Placeorder -- payment process : response failed');
            
            $this->handleFail($response);
        }

    }

    /**
     * Handle fail payment response.
     *
     * @param      \Checkout\Models\Response  $response  The response
     */
    protected function handleFail($response) {
		
        $this->module->logger->error('Channel Placeorder -- HandleFail Payment for order not processed');
        \PrestaShopLogger::addLog('Payment for order not processed.', 3, 0, 'checkoutcom' , $this->module->currentOrder, true);

        if (isset($response->status) && $response->status === 'Declined' && $response->response_summary) {
			$this->module->logger->error(sprintf('Channel Placeorder -- HandleFail An error has occured while processing your payment. Payment Declined : %s', $response->response_summary));
            $this->context->controller->errors[] = $this->trans('An error has occured while processing your payment. Payment Declined.', [], 'Modules.Checkoutcom.Placeorder.php');
        } else {

            // Set error message
			$this->module->logger->error(sprintf('Channel Placeorder -- HandleFail response : %s', $response->message));
            $this->context->controller->errors[] = $this->trans($response->message);
          
            foreach ($response->errors as $error) {
                $this->context->controller->errors[] = 'Error: ' . $error;
            }
        }

        // Redirect to cartcontext
        $this->redirectWithNotifications('index.php?controller=order');

    }

}