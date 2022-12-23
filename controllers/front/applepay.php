<?php
/**
 * Checkout.com
 * Authorised and regulated as an electronic money institution
 * by the UK Financial Conduct Authority (FCA) under number 900816.
 *
 * PrestaShop v1.7
 *
 * @category  prestashop-module
 * @package   Checkout.com
 * @author    Platforms Development Team <platforms@checkout.com>
 * @copyright 2010-2020 Checkout.com
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://docs.checkout.com/
 */

use Checkout\Models\Response;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Classes\CheckoutcomCustomerCard;
use CheckoutCom\PrestaShop\Classes\CheckoutcomPaymentHandler;

class CheckoutcomApplepayModuleFrontController extends ModuleFrontController
{
    public function validate($url){
        $merchant_id = Configuration::get('CHECKOUTCOM_APPLE_MERCHANT_ID');
        $certificate = Configuration::get('CHECKOUTCOM_APPLE_CERTIFICATE');
        $key = Configuration::get('CHECKOUTCOM_APPLE_KEY');
        $domain = Configuration::get('PS_SHOP_DOMAIN_SSL');
        $name = Configuration::get('PS_SHOP_NAME');
        $curl = curl_init();
        $json = json_encode([
            'merchantIdentifier'=> $merchant_id,
            'initiativeContext'=> $domain,
            'initiative'=> 'web',
            'displayName'=> $name,
        ]);
        $curl_opt = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSLKEY => $key,
            CURLOPT_SSLCERT => $certificate,
            CURLOPT_POSTFIELDS =>$json,
            CURLOPT_POST=>1,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
            ),
        );
        // $this->module->logger->info($curl_opt);
        $this->module->logger->info($json);
        curl_setopt_array($curl, $curl_opt);
        $response = curl_exec($curl);
        $err = curl_error( $curl );
        $this->module->logger->info($err);
        return $response;
    }
    public function postProcess()
    {
        $this->module->logger->info('Apple pay session url');
        $url = Tools::getAllValues()['url'];
        $this->module->logger->info('Apple pay session url'. $url);
        header("Content-Type: application/json");
        if(filter_var($url, FILTER_VALIDATE_URL)){
            echo $this->validate($url);
        }else{
            echo json_encode([
                'error'=>406,
                'message'=>'URL parameter must be set and a valid Apple Pay session URL'
            ]);
        }
        die;
    }
}