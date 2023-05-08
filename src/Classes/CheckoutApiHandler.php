<?php

namespace CheckoutCom\PrestaShop\Classes;

use Checkout\CheckoutApi;
use Checkout\CheckoutSdk;
use Checkout\Environment;
use CheckoutCom\PrestaShop\Models\Config;

class CheckoutApiHandler
{
    /**
     * Checkout.com SDK Instance
     *
     * @var        CheckoutApi
     */
    protected static $api = null;

    protected static $token = null;
    /**
     * Initialize API.
     */
    public static function init()
    {
        
        if(\Configuration::get('CHECKOUTCOM_SERVICE') == 0){
            CheckoutApiHandler::$api = CheckoutSdk::builder()->staticKeys()
            ->publicKey(\Configuration::get('CHECKOUTCOM_PUBLIC_KEY_NAS')) // optional, only required for operations related with tokens
            ->secretKey(\Configuration::get('CHECKOUTCOM_SECRET_KEY_NAS'))
            ->environment(Environment::sandbox()) // or production()
            //->logger($logger) //optional, for a custom Logger
           // ->httpClientBuilder($client) // optional, for a custom HTTP client
            ->build();
           
        }
        else{
            CheckoutApiHandler::$api = CheckoutSdk::builder()
            ->previous()
            ->staticKeys()
            ->environment(Environment::sandbox()) // or production()
            ->publicKey(\Configuration::get('CHECKOUTCOM_PUBLIC_KEY_ABC')) // optional, only required for operations related with tokens
            ->secretKey(\Configuration::get('CHECKOUTCOM_SECRET_KEY_ABC'))
            //->logger($logger) //optional, for a custom Logger
         //   ->httpClientBuilder($client) // optional, for a custom HTTP client
            ->build();
           
        }
        
    }

    public static function inittoken()
    {
        

        if(\Configuration::get('CHECKOUTCOM_SERVICE') == 0){
            CheckoutApiHandler::$token = CheckoutSdk::builder()->staticKeys()
            ->publicKey(\Configuration::get('CHECKOUTCOM_PUBLIC_KEY_NAS')) // optional, only required for operations related with tokens
            ->secretKey(\Configuration::get('CHECKOUTCOM_SECRET_KEY_NAS'))
            ->environment(Environment::sandbox()) // or production()
            //->logger($logger) //optional, for a custom Logger
           // ->httpClientBuilder($client) // optional, for a custom HTTP client
            ->build();
           
        }
        else{
            CheckoutApiHandler::$token = CheckoutSdk::builder()
            ->previous()
            ->staticKeys()
            ->environment(Environment::sandbox()) // or production()
            ->publicKey(\Configuration::get('CHECKOUTCOM_PUBLIC_KEY_ABC')) // optional, only required for operations related with tokens
            ->secretKey(\Configuration::get('CHECKOUTCOM_SECRET_KEY_ABC'))
            //->logger($logger) //optional, for a custom Logger
         //   ->httpClientBuilder($client) // optional, for a custom HTTP client
            ->build();
           
        }
    }

    /**
     * Access API.
     *
     * @return CheckoutApi
     */
    public static function api()
    {
        if (!CheckoutApiHandler::$api) {
            CheckoutApiHandler::init();
        }

        return CheckoutApiHandler::$api;
    }

    public static function token()
    {
        if (!CheckoutApiHandler::$token) {
            CheckoutApiHandler::inittoken();
        }

        return CheckoutApiHandler::$token;
    }
}
