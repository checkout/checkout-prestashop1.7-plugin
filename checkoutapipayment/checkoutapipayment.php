<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
if (!defined('_PS_VERSION_'))
    exit;

require_once (dirname(__FILE__). '/models/Checkoutapi.php');

class checkoutapipayment  extends models_Checkoutapi
{
    protected $_methodType;
    protected $_methodInstance;

    /**
     * @todo aim_available_currencies to be dynamic in admin
     * @throws Exception
     */

    public function __construct()
    {
        parent::__construct();
    }

    public  function _initCode()
    {
        $this->_code = $this->_methodInstance->getCode();
    }

    public function hookPaymentOptions($params)
    {  
        $smartyParam = parent::hookPaymentOptions($params);
        $this->context->smarty->assign($smartyParam);

        $payment_options = [
           $this->getIframePaymentOption($smartyParam),
        ];

        return $payment_options;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function generateForm()
    { 
        // return $this->context->smarty->fetch('module:checkoutapipayment/views/templates/frontend/hookpayment/js/js.tpl');
    }

    public function getIframePaymentOption($smartyParam)
    { 

        switch ($smartyParam['integrationType']) {
            case 'js':
                $iframeOption = new PaymentOption();
                $iframeOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true))
                             ->setAdditionalInformation($this->context->smarty->fetch('module:checkoutapipayment/views/templates/front/js.tpl'))
                             ->setModuleName($smartyParam['methodType'])
                             ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/CKO_Logo_Optical.png'));
                break;

            case 'frames':
                $iframeOption = new PaymentOption();
                $iframeOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true))
                             ->setAdditionalInformation($this->context->smarty->fetch('module:checkoutapipayment/views/templates/frontend/hookpayment/methods/creditcardframes.tpl'))
                             ->setModuleName($smartyParam['methodType'])
                             ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/CKO_Logo_Optical.png'));
                break;

             case 'hosted':
                $iframeOption = new PaymentOption();
                $iframeOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true))
                             ->setAdditionalInformation($this->context->smarty->fetch('module:checkoutapipayment/views/templates/frontend/hookpayment/methods/creditcardhosted.tpl'))
                             ->setModuleName($smartyParam['methodType'])
                             ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/CKO_Logo_Optical.png'));
                break;
            
            default:
                $iframeOption = new PaymentOption();
                $iframeOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true))
                             ->setAdditionalInformation($this->context->smarty->fetch('module:checkoutapipayment/views/templates/frontend/hookpayment/methods/creditcardframes.tpl'))
                             ->setModuleName($smartyParam['methodType'])
                             ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/CKO_Logo_Optical.png'));
                break;
        }

        return $iframeOption;
    }

    public function hookOrderConfirmation(array $params)
    { 
        if ($params['order']->module != $this->name)
            return;

        if ($params['order']->getCurrentState() != Configuration::get('PS_OS_ERROR')){
            $this->context->smarty->assign(array('status' => 'ok', 'id_order' => intval($params['order']->id)));
        } else {
            $this->context->smarty->assign('status', 'failed');
        }

        return $this->display(__FILE__, 'views/templates/frontend/hookconfirmation/orderconfirmation.tpl');

    }

    public static function getIsoCodeById($code)
    {
        $sql = '
        SELECT `iso_code`
        FROM `'._DB_PREFIX_.'country`
        WHERE `id_country` = \''.pSQL($code).'\'';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return $result['iso_code'];
    }
}