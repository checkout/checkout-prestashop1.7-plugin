<?php
class models_DataLayer extends PaymentModule
{
    public function saveAdminSetting($data)
    {
        $respond = array();
        Configuration::updateValue('CHECKOUTAPI_TEST_MODE', Tools::getvalue('checkoutapi_test_mode'));
        Configuration::updateValue('CHECKOUTAPI_PUBLIC_KEY', Tools::getvalue('checkoutapi_public_key'));
        Configuration::updateValue('CHECKOUTAPI_SECRET_KEY', Tools::getvalue('checkoutapi_secret_key'));
        Configuration::updateValue('CHECKOUTAPI_ORDER_STATUS', Tools::getvalue('checkoutapi_order_status'));
        Configuration::updateValue('CHECKOUTAPI_AUTOCAPTURE_DELAY', Tools::getvalue('checkoutapi_autocapture_delay'));
        Configuration::updateValue('CHECKOUTAPI_GATEWAY_TIMEOUT', Tools::getvalue('checkoutapi_gateway_timeout'));
        Configuration::updateValue('CHECKOUTAPI_INTEGRATION_TYPE', Tools::getvalue('checkoutapi_integration_type'));
        Configuration::updateValue('CHECKOUTAPI_IS_3D', Tools::getvalue('checkoutapi_is_3d'));
        Configuration::updateValue('CHECKOUTAPI_PAYMENT_ACTION', Tools::getvalue('checkoutapi_payment_action'));
        Configuration::updateValue('CHECKOUTAPI_LOGO_URL', Tools::getvalue('checkoutapi_logo_url'));
        Configuration::updateValue('CHECKOUTAPI_THEME_COLOR', Tools::getvalue('checkoutapi_theme_color'));
        Configuration::updateValue('CHECKOUTAPI_ICON_COLOR', Tools::getvalue('checkoutapi_icon_color'));
        Configuration::updateValue('CHECKOUTAPI_BUTTON_COLOR', Tools::getvalue('checkoutapi_button_color'));
        Configuration::updateValue('CHECKOUTAPI_CURRENCY_CODE', Tools::getvalue('checkoutapi_currency_code'));
        Configuration::updateValue('CHECKOUTAPI_TITLE', Tools::getvalue('checkoutapi_title'));
        Configuration::updateValue('CHECKOUTAPI_PAYMENT_MODE', Tools::getvalue('checkoutapi_payment_mode'));
        Configuration::updateValue('CHECKOUTAPI_THEME', Tools::getvalue('checkoutapi_theme'));
        Configuration::updateValue('CHECKOUTAPI_CUSTOM_CSS', Tools::getvalue('checkoutapi_custom_css'));
        Configuration::updateValue('CHECKOUTAPI_HOLD_REVIEW_OS', Tools::getvalue('checkoutapi_hold_review_os'));
        Configuration::updateValue('CHECKOUTAPI_SAVE_CARD', Tools::getvalue('checkoutapi_save_card'));

        $cardType = Tools::getValue('cardType');

        if(!empty($cardType) && is_array($cardType) && sizeof($cardType)>0) {
            $cards = helper_Card::getCardType($this);
            $tmpArray= array();

            foreach($cards as $cardInfo) {
                $tmpArray[$cardInfo['id']] = 0;
            }

            $mergeArray = array_merge($tmpArray,$cardType);
            foreach($mergeArray as $card => $value) {
                Configuration::updateValue($card, $value);
            }
        }

        $respond ['message'] = $this->l('Configuration updated');
        $respond ['status'] = 'success';

        return $respond;
    }

    public function installState()
    {
        $db = Db::getInstance();
        $moduleName ='checkoutapipayment';
        $db->insert('order_state', array(
            'invoice'        =>  0,
            'send_email'     =>  0,
            'module_name'    => $moduleName,
            'unremovable'    =>  1,
            'delivery'       =>  0,
            'shipped'        =>  0,
            'paid'           =>  0,
            'deleted'        =>  0,
            'color'         =>  '#4169E1'

        ),false,true, Db::REPLACE);

        $sql = 'SELECT * FROM '._DB_PREFIX_."order_state WHERE module_name = '$moduleName'";
        $row = Db::getInstance()->getRow($sql);
        global $cookie;

        $db->insert('order_state_lang', array(
            'id_order_state' =>  $row['id_order_state'],
            'id_lang'        =>  $cookie->id_lang ,
            'name'           =>  "Payment Authorised",
            'template'       =>  ''

        ),false,true, Db::REPLACE);

        Configuration::updateValue('PS_OS_CHECKOUT', $row['id_order_state']);

        $sql2 = 'SELECT * FROM '._DB_PREFIX_."order_state WHERE module_name = '$moduleName'";
        $row2 = Db::getInstance()->getRow($sql2);

        $db->insert('order_state_lang', array(
            'id_order_state' =>  $row2['id_order_state']+1,
            'id_lang'        =>  $cookie->id_lang ,
            'name'           =>  "Partial refund",
            'template'       =>  ''

        ),false,true, Db::REPLACE);


        Configuration::updateValue('PS_OS_PARTIAL_REFUND', $row2['id_order_state']);


        $this->_createChargeOrderCheckoutTable();
        $this->_createCheckoutSaveCardTable();

    }

    private function _createCheckoutSaveCardTable()
    {
     $sql = "CREATE TABLE  IF NOT EXISTS "._DB_PREFIX_."checkout_customer_cards
             (
                entity_id  INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                customer_id  INT(11) NOT NULL COMMENT 'Customer ID from PS'  ,
                card_id      VARCHAR(100) NOT NULL COMMENT 'Card ID from Checkout API' ,
                card_number  VARCHAR(4) NOT NULL COMMENT 'Short Customer Credit Card Number',
                card_type VARCHAR(20) NOT NULL COMMENT 'Credit Card Type',
                card_enabled BIT NOT NULL DEFAULT 1 COMMENT 'Credit Card Enabled'
             ) ENGINE=InnoDB;
             ";

     $db = Db::getInstance();
     if (!$db->execute($sql))
         die('Error has occured while creating your table. Please try again ');
    }

    private function _createChargeOrderCheckoutTable()
    {
     $sql = "CREATE TABLE  IF NOT EXISTS "._DB_PREFIX_."charge_order_checkout
             (
                id_charge  INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_order   INT(11) NOT NULL ,
                charge     VARCHAR(256) NOT NULL ,
                chargeObj  TEXT,
                amount TEXT,
                currency TEXT
             ) ENGINE=InnoDB;
             ";

     $db = Db::getInstance();
     if (!$db->execute($sql))
         die('Error has occured while creating your table. Please try again ');
    }

    public function unInstallState()
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM '._DB_PREFIX_."order_state WHERE module_name = 'checkoutapipayment'";
        $row = Db::getInstance()->getRow($sql);

        if(isset($row['id_order_state']) && $row['id_order_state']) {
            $sql = 'Delete from ' . _DB_PREFIX_ . 'order_state_lang where id_order_state=' . $row['id_order_state'];
            if (!$db->execute($sql))
                die('Error has occured when uninstalling order_state_lang ');

            $sql = 'Delete from ' . _DB_PREFIX_ . 'order_state where id_order_state=' . $row['id_order_state'];
            if (!$db->execute($sql))
                die('Error has occured when uninstalling order_state ');
        }
    }

    public function logCharge ($order_id,$charge_id,$chargeObj)
    {
        $db = Db::getInstance();
        $moduleName ='checkoutapipayment';
        $stringCharge = $chargeObj->getRawOutput();
        $amount =  $chargeObj->getValue();
        $currency = $chargeObj->getCurrency();

        $db->insert('charge_order_checkout', array(
            'id_order'    => $order_id  ,
            'charge'      => $charge_id ,
            'chargeObj'   => $stringCharge,
            'amount'      => $amount,
            'currency'    => $currency
        ));
    }

    public function getCharge ($order_idj)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_."charge_order_checkout WHERE id_order = $order_idj";
        return Db::getInstance()->getRow($sql);
    }

    public function getOrderId ($chargeId)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_."charge_order_checkout WHERE charge = '$chargeId'";
        return Db::getInstance()->getRow($sql);
    }
}