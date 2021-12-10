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

class CheckoutcomFailureModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;

    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }

        // Set error message
        $this->context->controller->errors[] = $this->trans('An error has occured while processing your payment.', [], 'Modules.Checkoutcom.Failure.php');
        // Redirect to cartcontext
        $this->redirectWithNotifications('index.php?controller=order');
    }

}