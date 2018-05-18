<?php

class models_methods_creditcard extends models_methods_Abstract
{
  protected $_code = 'creditcard';

  public function __construct() {
    $this->name = 'creditcard';
    parent::__construct();
  }

  public function _initCode() {}

  public function hookPaymentOptions($param) { 
    global $cookie;
    $hasError = false;
    $cart = $this->context->cart;
    $currency = $this->context->currency;
    $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
    $Api = CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE'),'authorization' => Configuration::get('CHECKOUTAPI_SECRET_KEY')));
    $amountCents = $Api->valueToDecimal($total, $currency->iso_code);
    $customer = new Customer((int) $cart->id_customer);
    $mode = Configuration::get('CHECKOUTAPI_TEST_MODE');
    $paymentTokenArray = $this->generatePaymentToken();
    $iso_code = $this->context->language->iso_code;
    $saveCard = Configuration::get('CHECKOUTAPI_SAVE_CARD');
    $cardList = $this->getCustomerCardList($cart->id_customer);
    $cardLists = array();

    if(!empty($cardList)){
        foreach ($cardList as $key) {
                $test[] = $key;
        }

        $this->context->smarty->assign('cardLists', $test);
    }

    switch ($iso_code) {
      case 'de':
        $localisation = 'DE-DE';
        break;
      
      case 'nl':
        $localisation = 'NL-NL';
        break;

      case 'fr':
        $localisation = 'FR-FR';
        break;

      case 'ko':
        $localisation = 'KR-KR';
        break;

      case 'it':
        $localisation = 'IT-IT';
        break;

      default:
        $localisation = 'EN-GB';
        break;
    }


    return array(
        'localisation'    => $localisation,
        'integrationType' => Configuration::get('CHECKOUTAPI_INTEGRATION_TYPE'),
        'renderMode'      => 2,
        'renderMode'      => 0,
        'hasError'        => $hasError,
        'methodType'      => $this->getCode(),
        'template'        => 'js.tpl',
        'simulateEmail'   => 'youremail@mail.com',
        'publicKey'       => Configuration::get('CHECKOUTAPI_PUBLIC_KEY'),
        'logourl'         => Configuration::get('CHECKOUTAPI_LOGO_URL'),
        'themecolor'      => Configuration::get('CHECKOUTAPI_THEME_COLOR'),
        'buttoncolor'     => Configuration::get('CHECKOUTAPI_BUTTON_COLOR'),
        'iconcolor'       => Configuration::get('CHECKOUTAPI_ICON_COLOR'),
        'usecurrencycode' => Configuration::get('CHECKOUTAPI_CURRENCY_CODE'),
        'title'           => Configuration::get('CHECKOUTAPI_TITLE'),
        'paymentMode'     => Configuration::get('CHECKOUTAPI_PAYMENT_MODE'),
        'paymentToken'    => $paymentTokenArray['token'],
        'message'         => $paymentTokenArray['message'],
        'success'         => $paymentTokenArray['success'],
        'eventId'         => $paymentTokenArray['eventId'],
        'mode'            => $mode,
        'amount'          => $amountCents,
        'mailAddress'     => $customer->email,
        'name'            => $customer->firstname . ' ' . $customer->lastname,
        'store'           => $customer->firstname . ' ' . $customer->lastname,
        'currencyIso'     => $currency->iso_code,
        'saveCard'          =>   $saveCard,
        'isGuest'           =>   $this->context->customer->is_guest
    );

    //return $this->setTemplate('module:checkoutapipayment/views/templates/frontend/hookpayment/js/js.tpl');
  }

  public function createCharge($config = array(), $cart) {
    $cardToken = Tools::getValue('cko_card_token');
    $config = array();
    $cart = $this->context->cart;
    $currency = $this->context->currency;
    $customer = new Customer((int) $cart->id_customer);
    $billingAddress = new Address((int) $cart->id_address_invoice);
    $shippingAddress = new Address((int) $cart->id_address_delivery);
    $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
    $scretKey = Configuration::get('CHECKOUTAPI_SECRET_KEY');
    $orderId = (int) $cart->id;

    $Api = CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE'),'authorization' => $scretKey));
    $amountCents = $Api->valueToDecimal($total, $currency->iso_code);

    $country = checkoutapipayment::getIsoCodeById($shippingAddress->id_country);
    $config['authorization'] = $scretKey;
    $config['mode'] = Configuration::get('CHECKOUTAPI_TEST_MODE');
    $config['timeout'] = Configuration::get('CHECKOUTAPI_GATEWAY_TIMEOUT');
    $billPhoneLength = strlen($billingAddress->phone);
    $chargeModeValue = 1;

    $billingAddressConfig = array(
        'addressLine1' => $billingAddress->address1,
        'addressLine2' => $billingAddress->address2,
        'postcode' => $billingAddress->postcode,
        'country' => $country,
        'city' => $billingAddress->city,
    );

    if ($billPhoneLength > 6) {
      $bilPhoneArray = array(
          'phone' => array('number' => $billingAddress->phone)
      );
      $billingAddressConfig = array_merge_recursive($billingAddressConfig, $bilPhoneArray);
    }

    $shipPhoneLength = strlen($shippingAddress->phone);
    $shippingAddressConfig = array(
        'addressLine1' => $shippingAddress->address1,
        'addressLine2' => $shippingAddress->address2,
        'postcode' => $shippingAddress->postcode,
        'country' => $country,
        'city' => $shippingAddress->city,
    );

    if ($shipPhoneLength > 6) {
      $shipPhoneArray = array(
          'phone' => array('number' => $shippingAddress->phone)
      );
      $shippingAddressConfig = array_merge_recursive($shippingAddressConfig, $shipPhoneArray);
    }

    $products = array();
    foreach ($cart->getProducts() as $item) {
      $products[] = array(
          'name' => strip_tags($item['name']),
          'sku' => strip_tags($item['reference']),
          'price' => $item['price'],
          'quantity' => $item['cart_quantity']
      );
    }

    if(Configuration::get('CHECKOUTAPI_IS_3D')) {
          $chargeModeValue = 2;
    }

    $customerName = $customer->firstname.' '.$customer->lastname;

    $config['postedParam'] = array(
        'customerName' => $customerName,
        'email' => $customer->email,
        'value' => $amountCents,
        'chargeMode' => $chargeModeValue,
        'trackId' => $orderId,
        'currency' => $currency->iso_code,
        'description' => "Cart Id::$orderId",
        'shippingDetails' => $shippingAddressConfig,
        'products' => $products,
        'customerIp' => $_SERVER['REMOTE_ADDR'],
        'billingDetails' => $billingAddressConfig,
        'metadata' => array(
            'server'            => _PS_BASE_URL_.__PS_BASE_URI__,
            'order_id'          => $orderId,
            'ps_version'        => _PS_VERSION_,
            'plugin_version'    => $this->version,
            'lib_version'       => CheckoutApi_Client_Constant::LIB_VERSION,
            'integration_type'  => 'JS',
            'time'              => date('Y-m-d H:i:s')
        )
    );

    if (Configuration::get('CHECKOUTAPI_PAYMENT_ACTION') == 'Y') {
      $config['postedParam'] = array_merge_recursive($config['postedParam'], $this->_captureConfig());
    } else {
      $config['postedParam'] = array_merge_recursive($config['postedParam'], $this->_authorizeConfig());
    }

    if(!empty($_POST['cko-card-token'])){
        $cardToken = $_POST['cko-card-token'];
        $config['postedParam'] = array_merge ( array('cardToken' => $cardToken) , $config['postedParam'] );
    } else {
        $entityId = $_POST['checkoutapipayment-saved-card'];
        $cardId = $this->getCardId($entityId);
        $config['postedParam'] = array_merge ( array('cardId' => $cardId['card_id']) , $config['postedParam'] );
    }

    return $Api->createCharge($config);
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

  private function generatePaymentToken() {
    $config = array();
    $cart = $this->context->cart;
    $currency = $this->context->currency;
    $customer = new Customer((int) $cart->id_customer);
    $billingAddress = new Address((int) $cart->id_address_invoice);
    $shippingAddress = new Address((int) $cart->id_address_delivery);
    $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
    $scretKey = Configuration::get('CHECKOUTAPI_SECRET_KEY');
    $orderId = (int) $cart->id;
    $Api = CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE'),'authorization' => $scretKey));
    $amountCents = $Api->valueToDecimal($total, $currency->iso_code);

    $chargeMode = Configuration::get('CHECKOUTAPI_IS3d');
    $chargeModeValue = 1;

    if($chargeMode) {
      $chargeModeValue = 2;
    }

    $country = checkoutapipayment::getIsoCodeById($shippingAddress->id_country);
    $config['authorization'] = $scretKey;
    $config['mode'] = Configuration::get('CHECKOUTAPI_TEST_MODE');
    $config['timeout'] = Configuration::get('CHECKOUTAPI_GATEWAY_TIMEOUT');

    $billPhoneLength = strlen($billingAddress->phone);
    $billingAddressConfig = array(
        'addressLine1' => $billingAddress->address1,
        'addressLine2' => $billingAddress->address2,
        'postcode' => $billingAddress->postcode,
        'country' => $country,
        'city' => $billingAddress->city,
    );

    if ($billPhoneLength > 6) {
      $bilPhoneArray = array(
          'phone' => array('number' => $billingAddress->phone)
      );
      $billingAddressConfig = array_merge_recursive($billingAddressConfig, $bilPhoneArray);
    }
    $shipPhoneLength = strlen($shippingAddress->phone);
    $shippingAddressConfig = array(
        'addressLine1' => $shippingAddress->address1,
        'addressLine2' => $shippingAddress->address1,
        'postcode' => $shippingAddress->postcode,
        'country' => $country,
        'city' => $shippingAddress->city,
    );

    if ($shipPhoneLength > 6) {
      $shipPhoneArray = array(
          'phone' => array('number' => $shippingAddress->phone)
      );
      $shippingAddressConfig = array_merge_recursive($shippingAddressConfig, $shipPhoneArray);
    }

    $products = array();
    foreach ($cart->getProducts() as $item) {
      $products[] = array(
          'name' => strip_tags($item['name']),
          'sku' => strip_tags($item['reference']),
          'price' => $item['price'],
          'quantity' => $item['cart_quantity']
      );
    }

    $config['postedParam'] = array(
        'email' => $customer->email,
        'value' => $amountCents,
        'currency' => $currency->iso_code,
        'chargeMode' => $chargeModeValue,
        'description' => "Card number::$orderId",
        'shippingDetails' => $shippingAddressConfig,
        'products' => $products,
        'card' => array(
            'billingDetails' => $billingAddressConfig
        )
    );

    if (Configuration::get('CHECKOUTAPI_PAYMENT_ACTION') == 'Y') {
      $config['postedParam'] = array_merge_recursive($config['postedParam'], $this->_captureConfig());
    } else {
      $config['postedParam'] = array_merge_recursive($config['postedParam'], $this->_authorizeConfig());
    }

    $paymentTokenCharge = $Api->getPaymentToken($config);
    $paymentTokenArray = array(
        'message' => '',
        'success' => '',
        'eventId' => '',
        'token' => '',
    );

    if ($paymentTokenCharge->isValid()) {
      $paymentTokenArray['token'] = $paymentTokenCharge->getId();
      $paymentTokenArray['success'] = true;
    } else {
      $paymentTokenArray['message'] = $paymentTokenCharge->getExceptionState()->getErrorMessage();
      $paymentTokenArray['success'] = false;
      $paymentTokenArray['eventId'] = $paymentTokenCharge->getEventId();
    }

    return $paymentTokenArray;

  }
}