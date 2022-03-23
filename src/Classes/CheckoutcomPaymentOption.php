<?php

namespace CheckoutCom\PrestaShop\Classes;

use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Models\Config;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use CheckoutCom\PrestaShop\Classes\CheckoutcomCustomerCard;
use CheckoutCom\PrestaShop\Models\Payments\Method;
use Checkout\Models\Payments\IdealSource;

//use PrestaShop\PrestaShop\Adapter\Debug\DebugMode;

class CheckoutcomPaymentOption extends PaymentOption
{
    /**
     * Generate payment option.
     *
     * @param <type> $module The module
     * @param <type> $params The parameters
     *
     * @return PaymentOption the card
     */
    public static function getCard(&$module)
    {

        if (!Config::get('CHECKOUTCOM_CARD_ENABLED')) {
            return;
        }

        // Load Context
        $context = \Context::getContext();

        // Get customers card list if exist
        if (!$context->customer->is_guest) {
            $cardList = CheckoutcomCustomerCard::getCardList($context->customer->id);

            if (!empty($cardList)) {
                $context->smarty->assign('cardLists', $cardList);
            }
        }

        $context->smarty->assign([
            'module' => $module->name,
            'CHECKOUTCOM_PUBLIC_KEY' => Config::get('CHECKOUTCOM_PUBLIC_KEY'),
            'lang' => Config::get('CHECKOUTCOM_CARD_LANG_FALLBACK'),
            'debug' => _PS_MODE_DEV_, //@todo: DebugMode::isDebugModeEnabled() or _PS_DEBUG_PROFILING_
            'save_card_option' => Config::get('CHECKOUTCOM_CARD_SAVE_CARD_OPTION'),
            'billingId' => $context->cart->id_address_invoice,
            'is_guest' =>$context->customer->is_guest,
            'img_dir' => _MODULE_DIR_.'checkoutcom/views/img/',
            'js_dir' => _MODULE_DIR_.'checkoutcom/views/js/',
            'isSingleIframe' => Config::get('CHECKOUTCOM_CARD_IFRAME_STYLE') === 'singleIframe' ? true : false
        ]);

        $option = new PaymentOption();
        $option->setForm($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/card.tpl'))
                ->setModuleName($module->name . '-card-form')
                ->setLogo(\Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . '/views/img/supported.svg'))
                ->setCallToActionText($module->l(Config::get('CHECKOUTCOM_CARD_TITLE')));

        return $option;
    }

    /**
     * Generate alternative payment methods.
     *
     * @param <type> $module The module
     * @param <type> $params The parameters
     *
     * @return array
     */
    public static function getAlternatives(&$module)
    {
        // Load Context
        $context = \Context::getContext();

        $list = array();
        $methods = Config::definition('alternatives')[0];

        $address_invoice = new \Address($context->cart->id_address_invoice);
        $country_invoice = $address_invoice->country;

        foreach ($methods as $field) {
            if (Config::get($field['name']) &&
                in_array($context->currency->iso_code,
                         Utilities::getValueFromArray($field, 'currencies', array()))) {
                $class = Utilities::getValueFromArray($field, 'class');
                if ($class) {
                    $context->smarty->assign($field);
                    $context->smarty->assign($class::assign());
                }

                // MULTIBANCO
                if ( $field['key'] === 'multibanco' ) {
                    // Change Multibanco payment label
                    $field['title'] = "Pay with APM by Checkout.com";

                    // Allow only portugal invoice address for Multibanco
                    if ( $country_invoice !== 'Portugal' ) {
                        continue;
                    }
                // Allow only Multibanco for portugal invoice address 
                }else if( $country_invoice === 'Portugal' ){
                    continue;
                }
                
                // iDeal : get iDeal banks
                $bic = '';
                $source = new IdealSource($bic, 'iDEAL payment');
                $banks = Method::getBanks($source);
                $issuers = $banks->countries[0]['issuers'];
                $context->smarty->assign('idealBanks', $issuers);

                $option = new PaymentOption();
                $option->setForm($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/alternatives/' . $field['key'] . '.tpl'))
                        ->setModuleName($module->name . '-' . $field['key'] . '-form')
                        ->setLogo(\Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . '/views/img/' . $field['key'] . '.svg'))
                        ->setCallToActionText($field['title']);

                $list[] = $option;
            }
        }

        return $list;
    }

    /**
     * Generate payment option.
     *
     * @param <type> $module The module
     * @param <type> $params The parameters
     *
     * @return PaymentOption the card
     */
    public static function getGoogle(&$module)
    {
        if (!Config::get('CHECKOUTCOM_GOOGLE_ENABLED')) {
            return;
        }

        // Load Context
        $context = \Context::getContext();

        $context->smarty->assign([
            'module' => $module->name,
            'CHECKOUTCOM_PUBLIC_KEY' => Config::get('CHECKOUTCOM_PUBLIC_KEY'),
            'merchantid' => Config::get('CHECKOUTCOM_GOOGLE_ID'),
            'live' => Config::get('CHECKOUTCOM_LIVE_MODE'),
            'invoiceid' => $context->cart->id_address_invoice,
        ]);

        $option = new PaymentOption();
        $option->setForm($context->smarty->fetch($module->getLocalPath() . 'views/templates/front/payments/google.tpl'))
                ->setModuleName($module->name . '-google-form')
                ->setLogo(\Media::getMediaPath(_PS_MODULE_DIR_ . $module->name . '/views/img/googlepay.svg'))
                ->setCallToActionText($module->l(Config::get('CHECKOUTCOM_GOOGLE_TITLE')));

        return $option;
    }
}