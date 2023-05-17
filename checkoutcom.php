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
use Checkout\CheckoutApi;
use Checkout\Models\Payments\Capture;
use Checkout\Models\Payments\Refund;
use CheckoutCom\PrestaShop\Models\Payments\Method;

class CheckoutCom extends PaymentModule
{
    /** @var \Monolog\Logger $logger */
    public $logger;
    
    /**
     * Define module.
     */
    public function __construct()
    {
        $this->name = 'checkoutcom';
        $this->tab = 'payments_gateways';
        $this->version = '2.3.7';
        $this->author = 'Checkout.com';
        $this->need_instance = 1;

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();
        Debug::initLogger($this, 'checkoutcom', true);
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
            $this->logger->error('Install : cURL extension is not enabled.');
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module.');
            return false;
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."checkoutcom_adminorder`(
            `id_checkoutcom_adminorder` int(11) NOT NULL AUTO_INCREMENT,
            `transaction_id` varchar(255) NOT NULL,
            `amount_captured` float(20,2) NOT NULL,
            `amount_refunded` float(20,2) NOT NULL,
            PRIMARY KEY (id_checkoutcom_adminorder)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $db = Db::getInstance();
        if (!$db->execute($sql)){
            $this->logger->error('Install : Error has occured while creating checkoutcom_adminorder table');
            throw new Exception($this->l('Cannot create checkoutcom_adminorder table'));
        }
        Configuration::updateValue('CHECKOUTCOM_TRIGGER_STATUS', null);
        $this->logger->info('Install : Table checkoutcom_adminorder created.');
        Config::install();
        $this->logger->info('Install : The module has been installed.');
        \PrestaShopLogger::addLog("The module has been installed.", 1, 0, 'checkoutcom' , 0, false, $this->context->employee->id);
 
        Tools::clearSmartyCache();

        return parent::install() &&
            $this->addOrderState('Payment authorized by CKO, awaiting capture') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('header') &&
            $this->registerHook('displayCustomerAccount') &&
            $this->registerHook('actionOrderSlipAdd') &&
            $this->registerHook('displayAdminOrderContentOrder') && 
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayAdminOrderMainBottom') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('actionProductCancel');
    }

    /**
     * Uninstall module.
     *
     * @return <type> ( description_of_the_return_value )
     */
    public function uninstall()
    {
        Config::uninstall();
        $this->logger->info('The module has been uninstalled.');
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
        $triggerStatus = Configuration::get('CHECKOUTCOM_TRIGGER_STATUS');
        $config_values = Config::values();

        if (Configuration::get('CHECKOUTCOM_SERVICE') == 0) {
            $config_values['CHECKOUTCOM_SECRET_KEY'] = str_replace('Bearer ', '', $config_values['CHECKOUTCOM_SECRET_KEY']);
            $config_values['CHECKOUTCOM_PUBLIC_KEY'] = str_replace('Bearer ', '', $config_values['CHECKOUTCOM_PUBLIC_KEY']);
        }

        $config_values['CHECKOUTCOM_SECRET_KEY_NAS'] = Configuration::get('CHECKOUTCOM_SECRET_KEY_NAS');
        $config_values['CHECKOUTCOM_PUBLIC_KEY_NAS'] = Configuration::get('CHECKOUTCOM_PUBLIC_KEY_NAS');
        $config_values['CHECKOUTCOM_SECRET_KEY_ABC'] = Configuration::get('CHECKOUTCOM_SECRET_KEY_ABC');
        $config_values['CHECKOUTCOM_PUBLIC_KEY_ABC'] = Configuration::get('CHECKOUTCOM_PUBLIC_KEY_ABC');

        $helper->tpl_vars = array(
            'fields_value' => $config_values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'order_states' => OrderState::getOrderStates($this->context->language->id),
            'trigger_statuses' => $triggerStatus ? json_decode($triggerStatus, true) : [],
        );

        $helper->addToSmarty($smarty);

        $this->context->smarty->assign([
            'fields_value' => $config_values,
            'languages' => $this->context->controller->getLanguages(),
            'order_states' => OrderState::getOrderStates($this->context->language->id),
            'trigger_statuses' =>  $triggerStatus ? json_decode($triggerStatus, true) : [],
            'webhook_url' => $this->context->shop->getBaseURL().'index.php?fc=module&module=checkoutcom&controller=webhook',
        ]);
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        foreach (Config::keys() as $key) {
            $value = Tools::getValue($key);

            // if (!$value && in_array($key, array('CHECKOUTCOM_SECRET_KEY', 'CHECKOUTCOM_PUBLIC_KEY', 'CHECKOUTCOM_SHARED_KEY'))) {
            //     $value = Configuration::get($key);
            // }

            if ($value !== false) {
                if (is_array($value)) {
                    Configuration::updateValue($key, json_encode($value));
                }elseif ( in_array($key, array('CHECKOUTCOM_SECRET_KEY', 'CHECKOUTCOM_PUBLIC_KEY'))) {
                    if (Configuration::get('CHECKOUTCOM_SERVICE') == 0) {
                        Configuration::updateValue($key, 'Bearer '.str_replace('Bearer ', '',$value));
                        Configuration::updateValue($key.'_NAS', $value);
                    }else{
                        Configuration::updateValue($key, $value);
                        Configuration::updateValue($key.'_ABC', $value);
                    }
                }else{
                    Configuration::updateValue($key, $value);
                }
            }
        }
        
        if (Tools::isSubmit('set_webhook')) {
            $authorization_key = md5(uniqid(rand(), true));
            $signature_key = md5(uniqid(rand(), true));

            $data = [
               "name" => $this->context->shop->name." Prestashop NAS", 
               "conditions" => [
                    [
                        "type" => "event", 
                        "events" => [
                           "gateway" => [
                                "payment_approved", 
                                "payment_declined", 
                                "card_verification_declined", 
                                "card_verified", 
                                "payment_authorization_incremented", 
                                "payment_authorization_increment_declined", 
                                "payment_capture_declined", 
                                "payment_captured", 
                                "payment_refund_declined", 
                                "payment_refunded", 
                                "payment_void_declined", 
                                "payment_voided" 
                           ], 
                           "dispute" => [
                                "dispute_canceled", 
                                "dispute_evidence_required", 
                                "dispute_expired", 
                                "dispute_lost", 
                                "dispute_resolved", 
                                "dispute_won" 
                            ], 
                           "marketplace" => [
                                "payments_disabled", 
                                "payments_enabled", 
                                "vmss_failed", 
                                "vmss_passed", 
                                "match_failed", 
                                "match_passed", 
                                "sub_entity_created" 
                            ] 
                        ] 
                    ] 
                ], 
               "actions" => [
                    [
                        "type" => "webhook", 
                        "url" => $this->context->shop->getBaseURL()."index.php?fc=module&module=checkoutcom&controller=webhook", 
                        "headers" => [
                            "Authorization" => $authorization_key
                        ], 
                        "signature" => [
                          "key" => $signature_key
                        ] 
                    ] 
                ] 
            ];

            $url = 'https://api.sandbox.checkout.com/workflows';
            if(Configuration::get('CHECKOUTCOM_LIVE_MODE')){
                $url = 'https://api.checkout.com/workflows';
            }
            $this->logger->info('workflow api url:'.$url);
            $secret_key = Configuration::get('CHECKOUTCOM_SECRET_KEY');

            $this->curlCall($url, $data, $secret_key);

            Configuration::updateValue('CHECKOUTCOM_AUTHENTIFICATION_KEY', $authorization_key);
            Configuration::updateValue('CHECKOUTCOM_SIGNATURE_KEY', $signature_key);

            $this->context->controller->confirmations[] = 'Your Webhook has been successfully set.';
            return;
        }

        $this->logger->info('Module configurations have been updated');
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

    public function curlCall($url, $data, $secret_key)
    {
        $ch = curl_init($url);
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $headers = array(
            'Content-Type:application/json',
            'Authorization:'.$secret_key,
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $this->logger->info($result);
        curl_close($ch);
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
           //$order_state->add();
            if (!$order_state->add()) {
                $this->logger->error('Install : Cannot create order state : '. $name);
                throw new Exception($this->l('Cannot create order state'));
            }
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
            CheckoutcomPaymentOption::getGoogle($this, $params),
            CheckoutcomPaymentOption::getApple($this, $params),
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
            $this->context->controller->addJS($this->_path . '/views/js/apple-pay-sdk.js');
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
        
        $data = Tools::getValue('cancel_product',[]);
        $order_id = $params['order']->id;
        $order = new Order((int)$order_id);
        $order_payment = OrderPayment::getByOrderId($order_id);
        $payment_id = $order_payment[0]->transaction_id;
        $amount  = 0;
        $currency =  new CurrencyCore($order->id_currency);
        $currency_code = $currency->iso_code;
       
        if (! $this->active || ($order->module != $this->name)) {
            return;
        }

        //Conditional block for ps >= 1.7.7
        if(count($data) > 0){

            //Check if the payment is valid
            if(empty($payment_id)){
                $this->context->controller->errors[] = $this->l('An error has occured. No cko payment id found');
                return false;
            }
            // $checkout = new CheckoutApi( \Configuration::get('CHECKOUTCOM_SECRET_KEY') );  
            if(strlen($payment_id)>0)
            {
                $returnedQuantity = 0;
                if(count($params['productList'])==0){
                //Add code here if any extra handling is needed just for shipping refund
                }
                else{
                    foreach($params['productList'] as $product){
                        $amount+= $product['total_refunded_tax_incl'];
                        $returnedQuantity += $product['quantity'];
                    }
                }

                //Don't process payment if voucher option is enabled
                if(isset($data["voucher"]) &&  $data["voucher"]==1){
                    $isFullRefund = $this->_isFullRefund($order, $returnedQuantity);
                    
                    //Change order state if all items are
                    if($isFullRefund){
                        $this->_updateOrderState($order);
                    }
                    return;
                }       
            
                //Add shipping amount from param for partial refund
                if(isset($data['shipping']) && $data['shipping']){
                    $shippingRefunded = $this->_getShippingAmount($order_id);   
                    $amount += $shippingRefunded;
                }

                //Calculate pending shipping cost from order slip for standard refund
                else if(isset($data['shipping_amount']) && $data['shipping_amount']>0){
                    $amount += $data['shipping_amount'];
                }

                //Deduct voucher from refund based on merchan't input
                $amount -= $this->calculateDiscount($data,$order);
            }       
        }

        // Conditional block for ps < 1.7.7
        else{
            
            if(empty($payment_id)){
                $this->context->controller->errors[] = $this->l('An error has occured. No cko payment id found');
                return false;
            }

            // Do nothing if it a voucher refund
            if(Tools::getValue('generateDiscount', "off") === "on"){
                return;
            }

            // Check if a partial refund is made
            if (true === Tools::isSubmit('partialRefund')) {

                $amount = $this->_getPartialRefundAmount($order);

                if(!$amount){
                    $this->context->controller->errors[] = $this->l('An error has occured. Invalid refund amount');
                    return false;
                }

            } 
             // Check if a standard refund is made
            else {
                $this->logger->info('Hook : Refund 1.7.5 standard refund');
                $amount = $this->_getStandardRefundAmount($order);
               
                if(!$amount) {
                    $this->context->controller->errors[] = $this->l('An error has occured. Invalid refund amount');
                    return false;
                }
              
            }
            
        }

        //Refund the amount using sdk.
        $refund = $this->_refund($payment_id, $amount, $currency_code);
        if(!$refund){
            $this->context->controller->errors[] = $this->l('An error has occured while processing your refund on checkout.com.');
            $this->errors[] = $this->l('An error has occured while processing your refund on checkout.com.');

            $this->logger->error('Refund failure : true');
            // No refund, so get back refunded products quantities, and available products stock quantities.
            $this->_rollbackOrder($order);
        } else {
            $this->context->controller->success[] = $this->l('Payment refunded successfully on checkout.com.');
        }
    }




    /**
     * Rollback order quantities 
     * @param $order
     * @return bool
     */
    private function _rollbackOrder($order){
        $id_order_details = Tools::isSubmit('generateCreditSlip') ? Tools::getValue('cancelQuantity')
                : Tools::getValue('partialRefundProductQuantity');
            if (is_array($id_order_details) && ! empty($id_order_details)) {
                // Prestashop versions < 1.7.7.
                foreach ($id_order_details as $id_order_detail => $quantity) {
                    // Update order detail.
                    $order_detail = new OrderDetail($id_order_detail);
                    $order_detail->product_quantity_refunded -= $quantity;
                    $order_detail->update();

                    // Update product available quantity.
                    StockAvailable::updateQuantity($order_detail->product_id, $order_detail->product_attribute_id, -$quantity, $order->id_shop);
                }
            }

            if (Tools::isSubmit('token')) {
                $this->logger->error('PS < 1.7.7');
                // Prestashop versions < 1.7.7.
                Tools::redirectAdmin(AdminController::$currentIndex . '&id_order=' . $order->id . '&vieworder&token=' . Tools::getValue('token'));
            } else {
                $this->logger->error('PS > 1.7.7');
                // Display warning to customer if any for Prestashop versions >= 1.7.7.
                $this->get('session')->getFlashBag()->set('error', 'An error has occured while processing your refund on checkout.com.');
                    
                // Prestashop versions >= 1.7.7.
                $url_admin_orders = $this->context->link->getAdminLink('AdminOrders');
                $url_admin_order = str_replace('/?_token=', '/' . $order->id . '/view?_token=', $url_admin_orders);

                Tools::redirectAdmin($url_admin_order);
            }
            return true;
    }



     /**
     * Update order status to refunded
     * @param $order
     * @return bool
     */
    private function _updateOrderState($order){
        $currentStatus = $order->getCurrentOrderState()->id;
        $status = \Configuration::get('CHECKOUTCOM_REFUND_ORDER_STATUS');
        if($currentStatus !== $status){ 
            $history = new OrderHistory();
            $history->id_order = $order->id;
            $history->changeIdOrderState($status, $order->id, true);
            $this->logger->info('Hook : order status'. $status);
            $history->addWithemail();
        }
        return true;
    }



    /**
     * Refund via cko sdk
     * @param $payment_id
     * @param $amount
     * @return bool
     */
    private function _refund($payment_id, $amount, $currency_code){
        
        $param = array(
            'payment_id' => $payment_id,
            'currency_code' => $currency_code,
            'amount' => $amount
        );
      
        $refund = Method::makeRefund($param);
        return $refund;   
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
        $amount = round( $amount, 2);
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
            $amount = round( $amount, 2);
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
        $transaction['id_currency'] = $order->id_currency;
        $time = (float) \Configuration::get('CHECKOUTCOM_CAPTURE_TIME');
        $event = (float) \Configuration::get('CHECKOUTCOM_PAYMENT_EVENT');
        $action = (float) \Configuration::get('CHECKOUTCOM_PAYMENT_ACTION');
        $trigger_statuses = json_decode(\Configuration::get('CHECKOUTCOM_TRIGGER_STATUS'));
        
        if(strpos($order->payment, '-card') !== false ){
            $sql = 'SELECT * FROM '._DB_PREFIX_."checkoutcom_adminorder WHERE `transaction_id` = '" . $transaction['transaction_id'] . "'"; 
            $row = Db::getInstance()->executeS($sql);
            if( !$action && !$event ){
                $transaction['amountCaptured'] = 0;
                $transaction['capturableAmount'] = (float) $transaction['amount'];
     
                $transaction['amountRefunded'] = 0;
                $transaction['refundableAmount'] = 0;
     
                $transaction['isCapturable'] = true;
                $transaction['isRefundable'] = false;

                if ( !empty($row) ) {
                    $transaction['amountCaptured'] = $row[0]['amount_captured'];
                    $transaction['capturableAmount'] = $transaction['amount'] - $row[0]['amount_captured'];
         
                    $transaction['amountRefunded'] = $row[0]['amount_refunded'];
                    $transaction['refundableAmount'] = $transaction['amount'] - $row[0]['amount_refunded'];
         
                    $transaction['isCapturable'] = false;
                    $transaction['isRefundable'] = false;

                    if ( $transaction['amount'] > $row[0]['amount_captured']) {
                        $transaction['isCapturable'] = true;
                    }
                }


                if (Tools::isSubmit('amountToCapture')) { 
                    $amountToCapture = (float) number_format( Tools::getValue('amountToCapture'), 2);
                    $amountToCaptureInt = $amountToCapture*100;

                    if ($amountToCapture <= $transaction['capturableAmount']) {
                        $checkout = new CheckoutApi( \Configuration::get('CHECKOUTCOM_SECRET_KEY') );

                        $capture_type = "NonFinal";
                        if ($amountToCapture == $transaction['capturableAmount']) {
                            $capture_type = "Final";
                        }

                        try {
                            $details = $checkout->payments()->capture(new Capture($payment[0]->transaction_id, (int) $amountToCaptureInt, $capture_type));
                        } catch (Exception $ex) {
                          
                            $details->http_code = $ex->getCode();
                            $details->message = $ex->getMessage();
                            $details->errors = $ex->getErrors();
                            $caught = true;
                        }

                        if ($details->http_code === 202) {
                            if ( empty($row) ) {
                                $sql  = "INSERT INTO "._DB_PREFIX_."checkoutcom_adminorder (`transaction_id`, `amount_captured`, `amount_refunded`)";
                                $sql .= "VALUES ('".$transaction['transaction_id']."', ".$amountToCapture.", 0)";
                                Db::getInstance()->execute($sql);
                            }else{
                                $sql  = "UPDATE "._DB_PREFIX_."checkoutcom_adminorder";
                                $sql .= " SET `amount_captured`=".($transaction['amountCaptured']+$amountToCapture);
                                $sql .= " WHERE `transaction_id`='".$transaction['transaction_id']."'";
                                Db::getInstance()->execute($sql);
                            }

                            $transaction['amountCaptured'] = $transaction['amountCaptured']+$amountToCapture;
                            $transaction['capturableAmount'] = $transaction['capturableAmount'] - $amountToCapture;
                            $transaction['isCapturable'] = false;
                            if ( $transaction['amount'] > $transaction['amountCaptured']) {
                                $transaction['isCapturable'] = true;
                            }

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
        $amountToCapture = round($order->total_paid_tax_incl, 2);
        $trigger_statuses = $trigger_statuses ? $trigger_statuses : [];
        if (!$event && !$action && in_array($new_status_id, $trigger_statuses)) {
            $checkout = new CheckoutApi( \Configuration::get('CHECKOUTCOM_SECRET_KEY') );
            try {
                $details = $checkout->payments()->capture(new Capture($payment[0]->transaction_id, intval(round($amountToCapture*100))));
            } catch (Exception $ex) {
                return;
            }

            $sql  = "INSERT INTO "._DB_PREFIX_."checkoutcom_adminorder (`transaction_id`, `amount_captured`, `amount_refunded`)";
            $sql .= "VALUES ('CART_" . $order->id_cart . "', ".$amountToCapture.", 0)";
            Db::getInstance()->execute($sql);
        }
    }


    /**
     * Calculate the shipping amount refunded on the order using credit slip
     * @param $order_id
     * @return float
     */
    private function _getShippingAmount($order_id){
        //calculate the already paid shipping
        $sql = 'SELECT total_shipping_tax_incl as total_shipping_tax_incl FROM '._DB_PREFIX_."order_slip   WHERE `id_order` = '" .$order_id . "' order by id_order_slip desc limit 1"; 
        $OrderSlips = Db::getInstance()->executeS($sql);
        $shippingRefunded =0;
        foreach($OrderSlips as $OrderSlip){
            $shippingRefunded +=$OrderSlip['total_shipping_tax_incl'];
        }

        return $shippingRefunded;
    }


     /**
     * Calculate discount/voucher amount used for the order
     * @param $params
     * @param $order
     * @return float
     */
    public function calculateDiscount($params,$order)
    {
        //Calculate the discount on the items
        $amount = 0;

        if (false == empty($params['voucher_refund_type'])) {
            if ($params['voucher_refund_type'] == 1) {
                    return (float) $order->total_discounts_tax_incl;
            }
        }

        return $amount;
    }

    /**
     * Check if all the items in the order are refunded (to mark the overall status of the order) 
     * @param $order
     * @param $currentRefundedCount
     * @return bool
     */
    private function _isFullRefund($order, $currentRefundedCount)
    {
        $refunded_products=$currentRefundedCount;
        
        $sql = 'SELECT * FROM '._DB_PREFIX_."order_detail  WHERE `id_order` = '" .$order->id . "'"; 
        $orderDetails = Db::getInstance()->executeS($sql);
        $totalProducts = 0;
        foreach($orderDetails as $orderdetail){

            $refunded_products += $orderdetail['product_quantity_return']+ $orderdetail['product_quantity_refunded'];
            $totalProducts += $orderdetail['product_quantity'];
        }
      
        if($totalProducts === $refunded_products){
            return true;
        }
        return false;
    }
}
