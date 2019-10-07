<?php

class CheckoutcomFailureModuleFrontController extends ModuleFrontController
{
  public $display_column_left = false;

  /**
   * @see FrontController::initContent()
   */

  public function initContent() {
    if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
          return false;
      }

      $cart_id = Tools::getValue('cart_id');
      $secure_key = Tools::getValue('secure_key');

      // Set error message
      $this->context->controller->errors[] = $this->trans('An error has occured while processing your transaction.', array(), 'Shop.Notifications.Error');
      // Redirect to cart
      $this->redirectWithNotifications(__PS_BASE_URI__.'index.php?controller=order&step=1&key='.$secure_key.'&id_cart='
          .(int)$cart_id);
  }
}