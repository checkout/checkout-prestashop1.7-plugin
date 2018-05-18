<?php

/**

 * Created by PhpStorm.

 * User: dhiraj.gangoosirdar

 * Date: 10/30/2014

 * Time: 12:51 PM

 */

class models_methods_creditcardpci extends models_methods_Abstract
{
    protected  $_code = 'creditcardpci';

    public function __construct()
    {
        $this->name = 'creditcardpic';
        $this->author = 'Checkout.com';
        $this->description = $this->l('Receive payment with gateway 3.0');
        parent::__construct();
    }

    public  function _initCode()
    {

    }

    public function hookPaymentOptions($param)
    {
        $hasError = false;
        $ccType = Tools::getValue('cc_type');
        $cc_owner = Tools::getValue('cc_owner');
        $cc_number = Tools::getValue('cc_number');
        $cc_exp_month = Tools::getValue('cc_exp_month');
        $cc_exp_year = Tools::getValue('cc_exp_year');
        $cc_cid = Tools::getValue('cc_cid');
        $cards = helper_Card::getCardType($this);
        $cart = $this->context->cart;
        $saveCard = Configuration::get('CHECKOUTAPI_SAVE_CARD');
        $cardList = $this->getCustomerCardList($cart->id_customer);
        $cardLists = array();

        if(!empty($cardList)){
            foreach ($cardList as $key) {
                    $test[] = $key;
            }

            $this->context->smarty->assign('cardLists', $test);
        }

        return  array(
            'integrationType'   =>   Configuration::get('CHECKOUTAPI_INTEGRATION_TYPE'),
            'hasError' 			=>	 $hasError,
            'cards' 			=>	 $cards,
            'ccType' 			=>	 $ccType,
            'cc_owner' 			=>	 $cc_owner,
            'cc_exp_month' 		=>	 $cc_exp_month,
            'cc_exp_year' 		=>	 $cc_exp_year,
            'months' 			=>	 helper_Card::getExMonth(),
            'years' 			=>	 helper_Card::getExYear(),
            'methodType' 		=>	 $this->getCode(),
            'saveCard'          =>   $saveCard,
            'isGuest'           =>   $this->context->customer->is_guest
           );
    }

    public  function createCharge($config = array(),$cart)
    { 
        if($_POST['isSavedCard'] == 'false'){
            $invoiceAddress = new Address((int)$cart->id_address_invoice);

            $config['postedParam']['card'] = array_merge_recursive( $config['postedParam']['card'], array(
                                            //'phoneNumber'   =>   $invoiceAddress->phone ,
                                            'name'          =>   Tools::getValue('cc_owner'),
                                            'number'        =>   trim((string)Tools::getValue('cc_number')),
                                            'expiryMonth'   =>   (int)Tools::getValue('cc_exp_month'),
                                            'expiryYear'    =>   (int)Tools::getValue('cc_exp_year'),
                                            'cvv'           =>   Tools::getValue('cc_cid'),
                                   )
                                );
        } else{
            $entityId = $_POST['checkoutapipayment-saved-card'];
            $cardId = $this->getCardId($entityId);
            $config['postedParam'] = array_merge_recursive( $config['postedParam'], array(
                                        'cardId'=> $cardId['card_id']
                                    ));
        }

        if(Configuration::get('CHECKOUTAPI_PAYMENT_ACTION') =='Y') {
            $config['postedParam'] = array_merge_recursive($config['postedParam'],$this->_captureConfig());
        }else {
            $config['postedParam'] = array_merge_recursive($config['postedParam'],$this->_authorizeConfig());
        }

       return parent::_createCharge($config);
    }

    public function getCustomerCardList($customerId) {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM '._DB_PREFIX_."checkout_customer_cards WHERE customer_id = $customerId AND card_enabled = 1";
        $row = Db::getInstance()->executeS($sql);

        return $row;
    }

    public function getCardId($entityId){

        $db = Db::getInstance();
        $sql = 'SELECT card_id FROM '._DB_PREFIX_."checkout_customer_cards WHERE entity_id = $entityId";
        $row = Db::getInstance()->getRow($sql);

        return $row;
       
    }
}