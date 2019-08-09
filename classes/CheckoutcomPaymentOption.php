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

        $context = Context::getContext();
        Debug::write($context);
        $context->smarty->assign([
            'CHECKOUTCOM_PUBLIC_KEY' => Configuration::get('CHECKOUTCOM_PUBLIC_KEY'),
            'CHECKOUTCOM_CARD_FORM_THEME' => Configuration::get('CHECKOUTCOM_CARD_FORM_THEME'),
         //

            'lang' => $context->card->id_lang, //Configuration::get('CHECKOUTCOM_CARD_LANG_FALLBACK'), // $context->card-id_lang
            'debug' => DebugMode::isDebugModeEnabled()
        ]);

		$option = new PaymentOption();
        $option->setAction($context->link->getModuleLink($module->name, 'payment', array(), true))
                ->setAdditionalInformation($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/card.tpl'))
                ->setModuleName($module->name)
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
Debug::write('#getAlternatives');
        if(!Configuration::get('CHECKOUTCOM_ALTERNATIVE_ENABLED')) {
            return;
        }

        $context = Context::getContext();

    	$option = new PaymentOption();
        $option->setAction($context->link->getModuleLink($module->name, 'payment', array(), true))
                ->setAdditionalInformation($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/alternative.tpl'))
                ->setModuleName($module->name)
                ->setCallToActionText($module->l(Configuration::get('CHECKOUTCOM_ALTERNATIVE_TITLE')));

        return $option;

	}

     protected function generateForm()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = sprintf("%02d", $i);
        }
        $years = [];
        for ($i = 0; $i <= 10; $i++) {
            $years[] = date('Y', strtotime('+'.$i.' years'));
        }
        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'months' => $months,
            'years' => $years,
        ]);
        return $this->context->smarty->fetch('module:paymentexample/views/templates/front/payment_form.tpl');
    }

}