<?php

use Checkout\Models\Product;
use Checkout\Models\Sources\Sepa;
use Checkout\Models\Sources\Klarna;
use Checkout\Models\Sources\SepaData;
use Checkout\Models\Sources\SepaAddress;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;


class CheckoutcomSepaModuleFrontController extends ModuleFrontController {

    protected $data = array();

    /**
     * Initialize the page.
     */
    public function init()
    {
        header('Content-Type: text/html; charset=utf-8');
        parent::init();
    }

    /**
     * Initialize the page.
     */
    public function initContent()
    {

        $iban = Utilities::getValueFromArray($this->data, 'iban');
        $bic = Utilities::getValueFromArray($this->data, 'bic');

        if($iban && $bic) {
            $this->generateMandate($iban, $bic);
        }

        die('');

    }

    /**
     * Handle post data.
     */
    public function postProcess()
    {
        $post = file_get_contents("php://input");
        parse_str($post, $this->data);
    }


    /**
     * Generate mandate.
     *
     * @param      <type>  $iban   The iban
     * @param      <type>  $bic    The bic
     */
    protected function generateMandate($iban, $bic) {

        $addresses = $this->getAddresses();
        $mandate = $this->getMandate($iban, $bic, $addresses);

        if($mandate) {
            $this->context->smarty->assign($addresses + $mandate);
            die($this->context->smarty->fetch(CHECKOUTCOM_ROOT . '/views/templates/front/payments/alternatives/sepa/mandate.tpl'));
        }

    }

    /**
     * Gets the addresses.
     *
     * @return     integer  The addresses.
     */
    protected function getAddresses() {

        $billing = new Address((int) $this->context->cart->id_address_invoice);
        $country = Country::getIsoById($billing->id_country);

        $customer = array(
            'customer_country' => $country,
            'customer_address1' => $billing->address1,
            'customer_address2' => $billing->address2,
            'customer_postcode' => $billing->postcode,
            'customer_city' => $billing->city,
            'customer_firstname' => $billing->firstname,
            'customer_lastname' => $billing->lastname
        );

        $shop = array(
            'shop_country' => Country::getIsoById(Config::get('PS_SHOP_COUNTRY_ID')),
            'shop_address1' => Config::get('PS_SHOP_ADDR1'),
            'shop_address2' => Config::get('PS_SHOP_ADDR2'),
            'shop_postcode' => Config::get('PS_SHOP_CODE') . ' ' . Config::get('PS_SHOP_CITY'),
            'shop_name' => Config::get('PS_SHOP_NAME')
        );

        return $customer + $shop;

    }


    /**
     * Generate mandate.
     *
     * @param      <type>  $iban     The iban
     * @param      <type>  $bic      The bic
     * @param      <type>  $address  The address
     *
     * @return     array   The mandate.
     */
    protected function getMandate($iban, $bic, &$address) {

        $mandate = array();
        $sAddress = new SepaAddress($address['customer_address1'], $address['customer_city'], $address['customer_postcode'], $address['customer_country']);
        $data = new SepaData($address['customer_address1'], $address['customer_address1'], $iban, $bic, $address['shop_name'], 'single');
        $source = new Sepa($sAddress, $data);
        $details = CheckoutApiHandler::api()->sources()->add($source);

        if($details->isSuccessful()) {
            $mandate = array(
                'mandate_reference' => $details->getValue(array('response_data', 'mandate_reference')),
                'mandate_src' => $details->getId()
            );
        }

        return $mandate;

    }

}