<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\PrestaShop\Adapter\Debug\DebugMode;




class CheckoutcomPaymentOption extends PaymentOption
{

    /**
     * List card payment option.
     *
     * @param      checkoutcom         $module  The module
     * @param      array          $params  The parameters
     *
     * @return     PaymentOption  The card.
     */
	public static function getCard($module, array $params) {
Debug::write('#getCard');
        if(!Configuration::get('CHECKOUTCOM_CARD_ENABLED')) {
           return;
        }

        // Load Context
        $context = Context::getContext();

        // Load language
        $lang = Language::getLanguage($context->cart->id_lang);

        $context->smarty->assign([
            'module' => $module->name,
            'CHECKOUTCOM_PUBLIC_KEY' => Configuration::get('CHECKOUTCOM_PUBLIC_KEY'),
            'CHECKOUTCOM_CARD_FORM_THEME' => Configuration::get('CHECKOUTCOM_CARD_FORM_THEME'),
            'lang' => $lang ? $lang['language_code'] : Configuration::get('CHECKOUTCOM_CARD_LANG_FALLBACK'),
            'debug' => DebugMode::isDebugModeEnabled()
        ]);

		$option = new PaymentOption();
        $option->setForm($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/forms/card.tpl'))
                ->setModuleName($module->name . '_card')
                ->setCallToActionText($module->l(Configuration::get('CHECKOUTCOM_CARD_TITLE')));

        return $option;

	}

    /**
     * List Alternative Payments Option.
     *
     * @param      checkoutcom        $module  The module
     * @param      array          $params  The parameters
     *
     * @return     PaymentOption  The alternatives.
     */
	public static function getAlternatives($module, array $params) {

        AlternativePayment::getKeys();

        return;
Debug::write('#getAlternatives');
Debug::write(Configuration::get('CHECKOUTCOM_ALTERNATIVE_METHODS'));
        if(!Configuration::get('CHECKOUTCOM_ALTERNATIVE_ENABLED')) {
            return;
        }

        $context = Context::getContext();

    	$option = new PaymentOption();
        $option->setAction($context->link->getModuleLink($module->name, 'payment', array(), true))
                ->setAdditionalInformation($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/alternative.tpl'))
                ->setModuleName($module->name . '_alternative')
                ->setCallToActionText($module->l(Configuration::get('CHECKOUTCOM_ALTERNATIVE_TITLE')));

        return $option;

	}

}