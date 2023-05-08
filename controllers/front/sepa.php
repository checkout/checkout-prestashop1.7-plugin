<?php

use Checkout\Sources\Previous\SepaSourceRequest;
use Checkout\Sources\Previous\SourceData;
use Checkout\Sources\Previous\SourcesClient;
use Checkout\Common\CustomerRequest;
use Checkout\Common\Address as SepaAddress;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Models\Config;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Checkout\CheckoutConfiguration;
use Monolog\Logger;
use Checkout\CheckoutStaticKeysSdkBuilder;
use Checkout\Environment;
use Checkout\StaticKeysSdkCredentials;
use Checkout\ApiClient;
use Checkout\Previous\CheckoutApi;
use Checkout\AuthorizationType;

class CheckoutcomSepaModuleFrontController extends ModuleFrontController
{
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

        if ($iban && $bic) {
            $this->generateMandate($iban, $bic);
        }

        die('');
    }

    /**
     * Handle post data.
     */
    public function postProcess()
    {
        $post = file_get_contents('php://input');
        parse_str($post, $this->data);
    }

    /**
     * Generate mandate.
     *
     * @param <type> $iban The iban
     * @param <type> $bic The bic
     */
    protected function generateMandate($iban, $bic)
    {
        $addresses = $this->getAddresses();
        $mandate = $this->getMandate($iban, $bic, $addresses);

        if ($mandate) {
            $this->context->smarty->assign($addresses + $mandate);
            die($this->context->smarty->fetch(CHECKOUTCOM_ROOT . '/views/templates/front/payments/alternatives/sepa/mandate.tpl'));
        }
    }

    /**
     * Gets the addresses.
     *
     * @return int the addresses
     */
    protected function getAddresses()
    {
        $billing = new \Address((int) $this->context->cart->id_address_invoice);
        $country = \Country::getIsoById($billing->id_country);

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
            'shop_name' => Config::get('PS_SHOP_NAME'),
        );

        return $customer + $shop;
    }

    /**
     * Generate mandate.
     *
     * @param <type> $iban The iban
     * @param <type> $bic The bic
     * @param <type> $address The address
     *
     * @return array the mandate
     */
    protected function getMandate($iban, $bic, &$address)
    {
        $mandate = array();
        $sAddress = new SepaAddress();
        $sAddress->address_line1 = $address['customer_address1'];
        $sAddress->city = $address['customer_city'];
        $sAddress->zip =  $address['customer_postcode'];
        $sAddress->country =  $address['customer_country'];
        $data = new SourceData();
        $data->first_name = $address['customer_firstname'];
        $data->last_name =  $address['customer_lastname'];
        $data->account_iban=  $iban;
        $data->bic = $bic;
        $data->billing_descriptor=  $address['shop_name'];
        $data->mandate_type = 'single';
        $source = new SepaSourceRequest();
       /// $source->type = 'sepa';
        $source->billing_address = $sAddress;
        $customer_request        = new CustomerRequest();
		$customer_request->email = $this->context->customer->email;
		$customer_request->name  = $address['customer_firstname'] . ' ' .$address['customer_lastname'];
        $source->source_data= $data;
        $source->customer        = $customer_request;
        $details = $this->requestSource($source);

        if ($details['response_code']==10000) {
            $mandate = array(
                'customer_id' => $details['customer']['id'],
                'mandate_reference' => $details['response_data']['mandate_reference'],
                'mandate_src' => $details['id'],
            );
        }

        return $mandate;
    }

    /**
     * Safely request source.
     *
     * @param \Checkout\Models\Sources\Sepa $sepa The sepa
     *
     * @return Response
     */
    protected function requestSource($sepa)
    {

                //$response = new Response();
        $sdk = new  CheckoutStaticKeysSdkBuilder();
        $response =[];

        if(\Configuration::get('CHECKOUTCOM_SERVICE') != 0){
            // $credentials = new StaticKeysSdkCredentials(\Configuration::get('CHECKOUTCOM_SECRET_KEY'),\Configuration::get('CHECKOUTCOM_PUBLIC_KEY'));
            // $sdk->secretKey(\Configuration::get('CHECKOUTCOM_SECRET_KEY'));
            // $sdk->publicKey(\Configuration::get('CHECKOUTCOM_PUBLIC_KEY'));  
            // $configuration = new CheckoutConfiguration($credentials,Environment::sandbox(), $sdk->httpClientBuilder,$sdk->logger); 
            // $api = new ApiClient($configuration);   
            // $checkoutapi = new CheckoutApi($api,$configuration);
            try {
            // $sourceclient = new SourcesClient($api,$configuration);
                $response = CheckoutApiHandler::api()->getSourcesClient()->createSepaSource( $sepa );
                // print_r($response);
                // exit;
            } catch (CheckoutHttpException $ex) {
                $response->http_code = $ex->getCode();
                $response->message = $ex->getMessage();
                $response->errors = $ex->getErrors();
                \PrestaShopLogger::addLog($ex->getBody(), 3, $ex->getCode(), 'CheckoutcomSepaModuleFrontController' , 0, true);
            }
        }

        return $response;
    }

}
