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

		$this->paymentProcess($customer);
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

			// Flag Order
			if($response->isFlagged() && !Utilities::addMessageToOrder($this->module->l('⚠️ This order is flagged as a potential fraud. We have proceeded with the payment, but we recommend you do additional checks before shipping the order.'), $this->context->order)) {
				\PrestaShopLogger::addLog('Failed to add payment flag note to order.', 2, 0, 'CheckoutcomPlaceorderModuleFrontController' , $this->context->order->id, true);
			}

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

			/**
			 * load order payment and set cko action id as order transaction id
			 */
			$payments = $this->context->order->getOrderPaymentCollection();
			$payments[0]->transaction_id = $response->id;
			$payments[0]->update();

			$history = new OrderHistory();
			$history->id_order = $this->context->order->id;
			$history->changeIdOrderState(\Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS'), $this->context->order->id);
			// $history->addWithemail();

			Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $this->context->cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key);
		} else {
			$this->handleFail($response);
		}

	}

	/**
	 * Handle fail payment response.
	 *
	 * @param      \Checkout\Models\Response  $response  The response
	 */
	protected function handleFail() {

		\PrestaShopLogger::addLog('Payment for order not processed.', 3, 0, 'checkoutcom' , $this->module->currentOrder, true);

		// Set error message
		$this->context->controller->errors[] = $this->trans('An error has occured while processing your transaction.', array(), 'Shop.Notifications.Error');

		// Redirect to cartcontext
		$this->redirectWithNotifications('index.php?controller=order');

	}

}