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

class CheckoutcomFailureModuleFrontController extends ModuleFrontController
{
  public $display_column_left = false;

  public function postProcess() {

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