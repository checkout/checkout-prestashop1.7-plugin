<?php

namespace CheckoutCom\PrestaShop\Classes;

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Models\Config;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
//use PrestaShop\PrestaShop\Adapter\Debug\DebugMode;


class CheckoutcomPaymentOption extends PaymentOption
{

	/**
	 * Generate payment option.
	 *
	 * @param      <type>         $module  The module
	 * @param      <type>         $params  The parameters
	 *
	 * @return     PaymentOption  The card.
	 */
    public static function getCard(&$module, &$params) {
Debug::write('CheckoutcomPaymentOption.getCard');
    	if(!Config::get('CHECKOUTCOM_CARD_ENABLED')) {
           return;
        }
Debug::write('CheckoutcomPaymentOption.getCard -> enabled');
        // Load Context
        $context = \Context::getContext();
Debug::write('CheckoutcomPaymentOption.getCard -> context');
        $context->smarty->assign([
            'module' => $module->name,
            'CHECKOUTCOM_PUBLIC_KEY' => Config::get('CHECKOUTCOM_PUBLIC_KEY'),
            'lang' => Config::get('CHECKOUTCOM_CARD_LANG_FALLBACK'),
            'debug' => _PS_DEBUG_PROFILING_ //@todo: DebugMode::isDebugModeEnabled()
        ]);

Debug::write('CheckoutcomPaymentOption.getCard -> smarty -> ' . _PS_DEBUG_PROFILING_);
        $option = new PaymentOption();
        $option->setForm($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/card.tpl'))
                ->setModuleName($module->name . '-card-form')
                ->setLogo(\Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . '/views/img/supported.svg'))
                ->setCallToActionText($module->l(Config::get('CHECKOUTCOM_CARD_TITLE')));
Debug::write('CheckoutcomPaymentOption.getCard -> option');
        return $option;

    }

    /**
     * Generate alternative payment methods.
     *
     * @param      <type>  $module  The module
     * @param      <type>  $params  The parameters
     *
     * @return     array
     */
    public static function getAlternatives(&$module, &$params) {

        // Load Context
        $context = \Context::getContext();

        $list = array();
        $methods = Config::definition('alternatives')[0];

        foreach ($methods as $field) {

        	if(Config::get($field['name']) &&
                in_array($context->currency->iso_code,
                         Utilities::getValueFromArray($field, 'currencies', array()))) {

                $class = Utilities::getValueFromArray($field, 'class');
                if($class) {
                    $context->smarty->assign($field);
                    $context->smarty->assign($class::assign());
                }

        		$option = new PaymentOption();
        		$option->setForm($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/alternatives/'.$field['key'].'.tpl'))
                        ->setModuleName($module->name . '-' . $field['key'] . '-form')
                        ->setLogo(\Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . '/views/img/' . $field['key'] . '.svg'))
                        ->setCallToActionText($field['title']);

		        $list []= $option;

        	}

        }

        return $list;

    }

    /**
     * Generate payment option.
     *
     * @param      <type>         $module  The module
     * @param      <type>         $params  The parameters
     *
     * @return     PaymentOption  The card.
     */
    public static function getGoogle(&$module, &$params) {
Debug::write('CheckoutcomPaymentOption.getGoogle');
        if(!Config::get('CHECKOUTCOM_GOOGLE_ENABLED')) {
           return;
        }
Debug::write('CheckoutcomPaymentOption.getGoogle -> enabled');
        // Load Context
        $context = \Context::getContext();
Debug::write('CheckoutcomPaymentOption.getGoogle -> context');
        $context->smarty->assign([
            'module' => $module->name,
            'CHECKOUTCOM_PUBLIC_KEY' => Config::get('CHECKOUTCOM_PUBLIC_KEY'),
            'merchantid' => Config::get('CHECKOUTCOM_GOOGLE_ID'),
            'live' => Config::get('CHECKOUTCOM_LIVE_MODE')
        ]);
Debug::write('CheckoutcomPaymentOption.getGoogle -> smarty');
        $option = new PaymentOption();
        $option->setForm($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/google.tpl'))
                ->setModuleName($module->name . '-google-form')
                ->setLogo(\Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . '/views/img/googlepay.svg'))
                ->setCallToActionText($module->l(Config::get('CHECKOUTCOM_GOOGLE_TITLE')));
Debug::write('CheckoutcomPaymentOption.getGoogle -> opttion');
        return $option;

    }

}