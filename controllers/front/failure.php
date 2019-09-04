<?php

class CheckoutcomFailureModuleFrontController extends ModuleFrontController
{
  public $display_column_left = false;

  /**
   * @see FrontController::initContent()
   */

  public function initContent() {
    $this->display_column_left = false;
    parent::initContent();

    $cart = $this->context->cart;
    $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
    $currency = $this->context->currency;
    $customer = new Customer((int) $cart->id_customer);
    $paymentToken = $_REQUEST['cko-payment-token'];

    if($paymentToken){
		$config['authorization'] = Configuration::get('CHECKOUTAPI_SECRET_KEY');
		    $config['paymentToken'] = $paymentToken;

		    $Api = CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE')));
		    $respondCharge = $Api->verifyChargePaymentToken($config);

		    $this->module->validateOrder((int) $cart->id, Configuration::get('PS_OS_ERROR'), $total, $this->module->displayName, $respondCharge->getStatus() . ' by Checkout.com. (' . $respondCharge->getResponseMessage() . ')', array

		          ('transaction_id' => $respondCharge->getId()), (int) $currency->id, false, $customer->secure_key);

		    $dbLog = models_FactoryInstance::getInstance('models_DataLayer');
		    $dbLog->logCharge($this->module->currentOrder, $respondCharge->getId(), $respondCharge);

		    Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='
		            .(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='
		            .(int)$this->module->currentOrder);

    } else {
    	Tools::redirect('index.php?controller=order');
    }
  }
}