<?php

abstract class models_methods_Abstract extends PaymentModule  implements models_InterfacePayment
{
    public function __construct()
    {
        $this->tab = 'payments_gateways';
        $this->version = '1.1.5';
        $this->author = 'Checkout.com';
        $this->displayName = 'Checkout.com  (Gateway 3.0)';
        $this->description = $this->l('Receive payment with gateway 3.0');
        parent::__construct();
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function install(){}

    public function uninstall(){}

    public function hookOrderConfirmation(array $params){}

    public function hookBackOfficeHeader(){}

    public function getContent(){}

    public function hookPaymentOptions($params){}

    public function hookHeader(){}

    abstract public  function createCharge($config = array(),$cart);

    protected function _createCharge($config)
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=> Configuration::get('CHECKOUTAPI_TEST_MODE')));
        return $Api->createCharge($config);
    }

    protected function _captureConfig()
    {
        $to_return = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
            'autoCapTime' => Configuration::get('CHECKOUTAPI_AUTOCAPTURE_DELAY')
        );

        return $to_return;
    }

    protected function _authorizeConfig()
    {
        $to_return= array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
            'autoCapTime' => 0
        );

        return $to_return;
    }

    public function getContext()
    {
        return $this->context;
    }
}