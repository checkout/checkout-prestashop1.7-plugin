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
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;
use CheckoutCom\PrestaShop\Classes\CheckoutcomHelperForm;
use CheckoutCom\PrestaShop\Classes\CheckoutcomPaymentOption;
use OrderState;
use Checkout\CheckoutApi;
use Checkout\Models\Payments\Capture;

class CheckoutCom extends PaymentModule
{
    /**
     * Define module.
     */
    public function __construct()
    {
        $this->name = 'checkoutcom';
        $this->tab = 'payments_gateways';
        $this->version = '2.2.0';
        $this->author = 'Checkout.com';
        $this->need_instance = 1;

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Checkout.com');
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

        if (extension_loaded('curl') == false) {
            \PrestaShopLogger::addLog("cURL is not enabled.", 2, 0, 'checkoutcom' , 0);
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module.');
            return false;
        }

        Config::install();
        \PrestaShopLogger::addLog("The module has been installed.", 1, 0, 'checkoutcom' , 0, false, $this->context->employee->id);

        return parent::install() &&
            $this->addOrderState($this->l('Payment authorized, awaiting capture')) &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('header') &&
            $this->registerHook('displayCustomerAccount') &&
            $this->registerHook('actionOrderSlipAdd') &&
            $this->registerHook('displayAdminOrderContentOrder') && 
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayAdminOrderMainBottom') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('actionOrderStatusPostUpdate');
    }

    /**
     * Uninstall module.
     *
     * @return <type> ( description_of_the_return_value )
     */
    public function uninstall()
    {
        Config::uninstall();
        \PrestaShopLogger::addLog("The module has been uninstalled.", 1, 0, 'checkoutcom' , 0, false, $this->context->employee->id);
        return parent::uninstall();
    }

    /**
     * use the new module translation system
     * 
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitCheckoutComModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->checkoutcomSettings($this->context->smarty);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    /**
     * Prepare configuration page.
     *
     * @param <type> $smarty The smarty
     */
    protected function checkoutcomSettings(&$smarty)
    {
        $helper = new CheckoutcomHelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCheckoutComModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => Config::values(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'order_states' => OrderState::getOrderStates($this->context->language->id),
            'trigger_statuses' => json_decode(Configuration::get('CHECKOUTCOM_TRIGGER_STATUS')),
        );

        $helper->addToSmarty($smarty);

        $this->context->smarty->assign([
            'fields_value' => Config::values(),
            'order_states' => OrderState::getOrderStates($this->context->language->id),
            'trigger_statuses' => json_decode(Configuration::get('CHECKOUTCOM_TRIGGER_STATUS')),
        ]);
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        foreach (Config::keys() as $key) {
            $value = Tools::getValue($key);

            if (!$value && in_array($key, array('CHECKOUTCOM_SECRET_KEY', 'CHECKOUTCOM_PUBLIC_KEY', 'CHECKOUTCOM_SHARED_KEY'))) {
                $value = Configuration::get($key);
            }

            if ($value !== false) {
                Configuration::updateValue($key, $value);
            }
        }
        \PrestaShopLogger::addLog("Module configurations have been updated.", 1, 0, 'checkoutcom' , 0, true, $this->context->employee->id);

        if ( Tools::getValue('trigger_statuses') === "no_status" ) { 
            Configuration::updateValue('CHECKOUTCOM_TRIGGER_STATUS', null); 
        }elseif( Tools::getValue('trigger_statuses') ){ 
            Configuration::updateValue('CHECKOUTCOM_TRIGGER_STATUS', json_encode(Tools::getValue('trigger_statuses'))); 
        } 
 
        if ( Tools::isSubmit('submitCheckoutComModule') ) { 
            $this->context->controller->confirmations[] = 'Configuration has been successfully saved.'; 
        }
    }

    public function addOrderState($name)
    {
        $state_exist = false;
        $states = OrderState::getOrderStates((int)$this->context->language->id);
 
        // check if order state exist
        foreach ($states as $state) {
            if (in_array($name, $state)) {
                $state_exist = true;
                break;
            }
        }
 
        // If the state does not exist, we create it.
        if (!$state_exist) {
            // create new order state
            $order_state = new OrderState();
            $order_state->color = '#00ffff';
            $order_state->send_email = false;
            $order_state->module_name = $this->name;
            $order_state->name = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $language){
                $order_state->name[ $language['id_lang'] ] = $name;
            }
 
            // Update object
            $order_state->add();
        }
 
        return true;
    }

    /**
     * Hooks
     */

    /**
     * Display payment options.
     *
     * @return array
     */
    public function hookPaymentOptions($params)
    {

        if (!$this->active) {
            return;
        }

        $methods = array(
            CheckoutcomPaymentOption::getCard($this, $params),
            // CheckoutcomPaymentOption::getApple($this, $params),
            CheckoutcomPaymentOption::getGoogle($this, $params),
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

        if (Tools::getValue('controller') === 'order') {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . '/views/js/front.js');
            $this->context->controller->addJS($this->_path . '/views/js/cko.js');
            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        }

    }

    /**
     * Display saved card settings on customer dashbboard.
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function hookDisplayCustomerAccount()
    {
        // Show saved cards on customer's account if enable in module config
        if(Configuration::get('CHECKOUTCOM_CARD_SAVE_CARD_OPTION')) {
            return $this->display(__FILE__, 'views/templates/hook/customer-account.tpl');
        }
    }


    /**
     * used to create refunds on cko
     *
     * @param $params
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionOrderSlipAdd($params)
    {
        $order_id = $params['order']->id;
        $order = new Order((int)$order_id);
        $order_payment = OrderPayment::getByOrderId($order_id);
        $payment_id = $order_payment[0]->transaction_id;

        if(empty($payment_id)){
            $this->context->controller->errors[] = $this->l('An error has occured. No cko payment id found');
            return false;
        }

        $currency = new CurrencyCore($params['order']->id_currency);
        $currency_code = $currency->iso_code;

        $param = array(
            'payment_id' => $payment_id,
            'currency_code' => $currency_code,
        );

        // Check if a partial refund is made
        if (true === Tools::isSubmit('partialRefund')) {

            $amount = $this->_getPartialRefundAmount($order);

            if(!$amount){
                $this->context->controller->errors[] = $this->l('An error has occured. Invalid refund amount');
                return false;
            }

            $param['amount'] = $amount;
        } else {

            $amount = $this->_getStandardRefundAmount($order);

            if(!$amount) {
                $this->context->controller->errors[] = $this->l('An error has occured. Invalid refund amount');
                return false;
            }

            $param['amount'] = $amount;
        }

        $refund = Method::makeRefund($param);

        if(!$refund){
            $this->context->controller->errors[] = $this->l('An error has occured while processing your refund on checkout.com.');
        } else {
            $this->context->controller->success[] = $this->l('Payment refunded successfully on checkout.com.');
        }
    }

    /**
     * Get standard refund amount
     * @param $order
     * @return float|int
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function _getStandardRefundAmount($order)
    {
        $amount = 0;
        $productList = Tools::getValue('id_order_detail');
        if ($productList) {
            $productList = array_map('intval', $productList);
        }

        $qtyList = Tools::getValue('cancelQuantity');
        if ($qtyList) {
            $qtyList = array_map('intval', $qtyList);
        }

        $customizationList = Tools::getValue('id_customization');
        if ($customizationList) {
            $customizationList = array_map('intval', $customizationList);
        }

        $customizationQtyList = Tools::getValue('cancelCustomizationQuantity');
        if ($customizationQtyList) {
            $customizationQtyList = array_map('intval', $customizationQtyList);
        }

        $full_product_list = $productList;
        $full_quantity_list = $qtyList;

        if ($customizationList) {
            foreach ($customizationList as $key => $id_order_detail) {
                $full_product_list[(int) $id_order_detail] = $id_order_detail;
                if (isset($customizationQtyList[$key])) {
                    $full_quantity_list[(int) $id_order_detail] += $customizationQtyList[$key];
                }
            }
        }

        foreach ($full_product_list as $key => $id_order_detail) {
            $order_detail = new OrderDetail((int) ($id_order_detail));
            $amount += $order_detail->unit_price_tax_incl * $full_quantity_list[$id_order_detail];

            if ((int) Tools::getValue('refund_total_voucher_off') == 1) {
                $amount -= $voucher = (float) Tools::getValue('order_discount_price');
            } elseif ((int) Tools::getValue('refund_total_voucher_off') == 2) {
                $amount = $voucher = (float) Tools::getValue('refund_total_voucher_choose');
            }
        }

        if (Tools::isSubmit('shippingBack')) {
            $amount += $order->total_shipping;
        }

        return $amount;
    }

    /**
     * Get partial refund amount
     *
     * @param $order
     * @return bool|float|int
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function _getPartialRefundAmount($order)
    {
        if (Tools::isSubmit('partialRefundProduct') && ($refunds = Tools::getValue('partialRefundProduct')) && is_array($refunds)) {
            $amount = 0;
            $order_detail_list = array();
            $full_quantity_list = array();
            foreach ($refunds as $id_order_detail => $amount_detail) {
                $quantity = Tools::getValue('partialRefundProductQuantity');
                if (!$quantity[$id_order_detail]) {
                    continue;
                }

                $full_quantity_list[$id_order_detail] = (int)$quantity[$id_order_detail];

                $order_detail_list[$id_order_detail] = array(
                    'quantity' => (int)$quantity[$id_order_detail],
                    'id_order_detail' => (int)$id_order_detail,
                );

                $order_detail = new OrderDetail((int)$id_order_detail);
                if (empty($amount_detail)) {
                    $order_detail_list[$id_order_detail]['unit_price'] = (!Tools::getValue('TaxMethod') ? $order_detail->unit_price_tax_excl : $order_detail->unit_price_tax_incl);
                    $order_detail_list[$id_order_detail]['amount'] = $order_detail->unit_price_tax_incl * $order_detail_list[$id_order_detail]['quantity'];
                } else {
                    $order_detail_list[$id_order_detail]['amount'] = (float)str_replace(',', '.', $amount_detail);
                    $order_detail_list[$id_order_detail]['unit_price'] = $order_detail_list[$id_order_detail]['amount'] / $order_detail_list[$id_order_detail]['quantity'];
                }
                $amount += $order_detail_list[$id_order_detail]['amount'];
            }

            $shipping_cost_amount = (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) ? (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) : false;

            if ((int)Tools::getValue('refund_voucher_off') == 1) {
                $amount -= $voucher = (float)Tools::getValue('order_discount_price');
            } elseif ((int)Tools::getValue('refund_voucher_off') == 2) {
                $amount = $voucher = (float)Tools::getValue('refund_voucher_choose');
            }

            if ($shipping_cost_amount > 0) {
                if (!Tools::getValue('TaxMethod')) {
                    $tax = new Tax();
                    $tax->rate = $order->carrier_tax_rate;
                    $tax_calculator = new TaxCalculator(array($tax));
                    $amount += $tax_calculator->addTaxes($shipping_cost_amount);
                } else {
                    $amount += $shipping_cost_amount;
                }
            }

            return $amount;
        }

        return false;
    }

    /**
     * Used to hide or show refund button
     * for orders that were made from checkout module
     *
     * @param $params
     * @return string
     */
    public function hookDisplayAdminOrderContentOrder($params)
    {
        // check if order was made by checkout.com
        if($params['order']->module == $this->name ){
            $current_order_state = $params['order']->current_state;
            $is_capture = $current_order_state == Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS') ? true : false;

            $this->context->smarty->assign('is_capture', $is_capture);

            return $this->display(__FILE__, 'views/templates/admin/admin_order_content.tpl');
        }
    }

    public function hookDisplayBackOfficeHeader() 
    { 
        $this->context->controller->addJquery();
        
        $this->context->controller->addJS($this->_path . 'views/js/select2.js'); 
        $this->context->controller->addCSS($this->_path . 'views/css/select2.css'); 
 
        $this->context->controller->addJS($this->_path . 'views/js/admin.js'); 
        $this->context->controller->addCSS($this->_path . 'views/css/admin.css'); 
    }


    /** 
     * @param array $params 
     * @return string 
     */ 
    public function displayAdminOrder($params) 
    {
        $order_id = Tools::getValue('id_order');
        $payment = new OrderPayment();
        $payment = $payment->getByOrderId($order_id);
        $order = new Order($order_id);
        $transaction = [];
        $transaction['transaction_id'] = "CART_" . $order->id_cart;
        $transaction['amount'] = number_format( $order->total_paid_tax_incl, 2);
        $transaction['payment_method'] = $order->payment;

        $time = (float) \Configuration::get('CHECKOUTCOM_CAPTURE_TIME');
        $event = (float) \Configuration::get('CHECKOUTCOM_PAYMENT_EVENT');
        $action = (float) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
     
        if(strpos($order->payment, '-card') !== false ){
            $sql = 'SELECT * FROM '._DB_PREFIX_."checkoutcom_adminorder WHERE `transaction_id` = '" . $transaction['transaction_id'] . "'"; 
            $row = Db::getInstance()->executeS($sql);
     
            if ( !empty($row) ) {
                $transaction['amountCaptured'] = $row[0]['amount_captured'];
                $transaction['capturableAmount'] = $transaction['amount'] - $row[0]['amount_captured'];
     
                $transaction['amountRefunded'] = $row[0]['amount_refunded'];
                $transaction['refundableAmount'] = $transaction['amount'] - $row[0]['amount_refunded'];
     
                $transaction['isCapturable'] = false;
                $transaction['isRefundable'] = false;
            }elseif( !$action ){
                $transaction['amountCaptured'] = 0;
                $transaction['capturableAmount'] = (float) $transaction['amount'];
     
                $transaction['amountRefunded'] = 0;
                $transaction['refundableAmount'] = 0;
     
                $transaction['isCapturable'] = true;
                $transaction['isRefundable'] = false;

                if (Tools::isSubmit('amountToCapture')) { 
                    $amountToCapture = number_format( Tools::getValue('amountToCapture'), 2);

                    if ( $amountToCapture <= $transaction['capturableAmount']) {
                        $sql  = "INSERT INTO "._DB_PREFIX_."checkoutcom_adminorder (`transaction_id`, `amount_captured`, `amount_refunded`)";
                        $sql .= "VALUES ('".$transaction['transaction_id']."', ".$amountToCapture.", 0)";

                        $checkout = new CheckoutApi( \Configuration::get('CHECKOUTCOM_SECRET_KEY') );
                        try {
                            $details = $checkout->payments()->capture(new Capture($payment[0]->transaction_id, $amountToCapture*100));
                        } catch (Exception $ex) {
                            $details->http_code = $ex->getCode();
                            $details->message = $ex->getMessage();
                            $details->errors = $ex->getErrors();
                        }

                        if (Db::getInstance()->execute($sql) && $details->http_code === 202) {
                            $transaction['amountCaptured'] = $amountToCapture;
                            $transaction['capturableAmount'] = $transaction['capturableAmount'] - $amountToCapture;
                            $transaction['isCapturable'] = false;

                            $this->context->smarty->assign([ 
                                'capture_confirmation' => true
                            ]);
                        }else{
                            $this->context->smarty->assign([ 
                                'transactionError' => $details->message
                            ]);
                        }
                    }else{
                        $this->context->smarty->assign([
                            'transactionError' => 'The amount to capture is greater than the capturable amount'
                        ]);
                    }
                }
            } 
     
            $this->context->smarty->assign([ 
                'module_dir' => $this->_path,
                'transaction' => $transaction 
            ]); 

            return $this->display(__FILE__, '/views/templates/hook/hookAdminOrder.tpl'); 
        } 
    } 

    public function hookDisplayAdminOrderMainBottom($params)
    {
        return $this->displayAdminOrder($params);
    }

    public function hookDisplayAdminOrder($params)
    {
        return Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=') ? false : $this->displayAdminOrder($params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $new_status_id = $params['newOrderStatus']->id;
        $trigger_statuses = json_decode(\Configuration::get('CHECKOUTCOM_TRIGGER_STATUS'));
        $order = new Order($params['id_order']);
        $event = (float) \Configuration::get('CHECKOUTCOM_PAYMENT_EVENT');
        $action = (float) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
        $payment = new OrderPayment();
        $payment = $payment->getByOrderId($params['id_order']);
        $amountToCapture = (float) number_format( $order->total_paid_tax_incl, 2);

        if (!$event && !$action && in_array($new_status_id, $trigger_statuses)) {
            $sql  = "INSERT INTO "._DB_PREFIX_."checkoutcom_adminorder (`transaction_id`, `amount_captured`, `amount_refunded`)";
            $sql .= "VALUES ('CART_" . $order->id_cart . "', ".$amountToCapture.", 0)";
            Db::getInstance()->execute($sql);

            $checkout = new CheckoutApi( \Configuration::get('CHECKOUTCOM_SECRET_KEY') );
            try {
                $details = $checkout->payments()->capture(new Capture($payment[0]->transaction_id, $amountToCapture*100));
            } catch (Exception $ex) {
                //
            }
        }
    }
}