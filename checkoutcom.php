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

/**
 * Define module constants
 */
define('CHECKOUTCOM_ROOT', __DIR__);

if (!defined('_PS_VERSION_') || !is_readable(CHECKOUTCOM_ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    exit;
}

/**
 * Fix missing namespace at install
 */
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;
use CheckoutCom\PrestaShop\Classes\CheckoutcomHelperForm;
use CheckoutCom\PrestaShop\Classes\CheckoutcomPaymentOption;

class CheckoutCom extends PaymentModule
{

    /**
     * Define module.
     */
    public function __construct()
    {
        $this->name = 'checkoutcom';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Checkout.com';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Checkout.com Payment Gateway');
        $this->description = $this->l('Checkout.com is an international provider of online payment solutions. We support 150+ currencies and access to all international cards and popular local payment methods.');

        $this->confirmUninstall = $this->l('Are you sure you want to stop accepting payments?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
Debug::write('# checkoutcom.install');
        if (extension_loaded('curl') == false)
        {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module.');
            return false;
        }

        Config::install();

        return parent::install() &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('header') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('actionPaymentCCAdd') &&
            $this->registerHook('actionPaymentConfirmation') &&
            $this->registerHook('displayPayment') &&
            $this->registerHook('displayPaymentReturn') &&
            $this->registerHook('displayPaymentTop');
    }

    /**
     * Uninstall module.
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function uninstall()
    {
        Config::uninstall();
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitCheckoutComModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->checkoutcomSettings($this->context->smarty);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    /**
     * Prepare configuration page.
     *
     * @param      <type>  $smarty  The smarty
     */
    protected function checkoutcomSettings(&$smarty) {

        $helper = new CheckoutcomHelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCheckoutComModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => Config::values(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $helper->addToSmarty($smarty);

    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
 Debug::write('checkoutcom.postProcess-> ' . Tools::isSubmit('CHECKOUTCOM_SECRET_KEY'));
        foreach (Config::keys() as $key) {
            $value = Tools::getValue($key);

            if($key === 'CHECKOUTCOM_SECRET_KEY') {

            }


Debug::write('----');
Debug::write($key);
Debug::write($value);
Debug::write('----');
            if($value !== false) {
                Configuration::updateValue($key, $value);
            }
        }
    }


    /**
     * Hooks
     */

    /**
     * Display payment options.
     *
     * @return     array
     */
    public function hookPaymentOptions($params) {
Debug::write('#hookPaymentOptions');
        if (!$this->active) {
            return;
        }

        $methods = array(
            CheckoutcomPaymentOption::getCard($this, $params),
            // CheckoutcomPaymentOption::getApple($this, $params),
             CheckoutcomPaymentOption::getGoogle($this, $params)
        );

        $alternatives = CheckoutcomPaymentOption::getAlternatives($this, $params);
        foreach ($alternatives as $method) {
            array_push($methods, $method);
        }

        return array_filter($methods); // Remove nulls

    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
Debug::write('#hookHeader');
        if(Tools::getValue('controller') === 'order') {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'/views/js/front.js');
            $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        }

    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
Debug::write('#hookPayment');
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        $this->smarty->assign('module_dir', $this->_path);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
Debug::write('#hookPaymentReturn');
        // if ($this->active == false)
        //     return;

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR'))
            $this->smarty->assign('status', 'ok');

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    public function hookActionPaymentCCAdd()
    {
Debug::write('#hookActionPaymentCCAdd');
        /* Place your code here. */
    }

    public function hookActionPaymentConfirmation()
    {
Debug::write('#hookActionPaymentConfirmation');
        /* Place your code here. */
    }

    public function hookDisplayPayment()
    {
Debug::write('#hookDisplayPayment');
        /* Place your code here. */
    }

    public function hookDisplayPaymentReturn()
    {

Debug::write('#hookDisplayPaymentReturn');
        /* Place your code here. */
    }

    public function hookDisplayPaymentTop()
    {
Debug::write('#hookDisplayPaymentTop');
        // I don't think this will be needed.
        /* Place your code here. */
    }
}
