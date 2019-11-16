<?php

namespace CheckoutCom\PrestaShop\Helpers;

use CheckoutCom\PrestaShop\Models\Config;

class Utilities
{
    /**
     * Gets the value from array.
     *
     * @param array $arr The arr
     * @param <type> $field The field
     * @param <type> $default The default
     *
     * @return <type> The value from array
     */
    public static function getValueFromArray(array $arr, $field, $default = null)
    {
        return isset($arr[$field]) ? $arr[$field] : $default;
    }

    /**
     * Gets the configuration.
     *
     * @param string $name The name
     *
     * @return <type> The configuration
     */
    public static function getConfig($name)
    {
        return json_decode(static::getFile(Config::CHECKOUTCOM_CONFIGS . $name . '.json'), true);
    }

    /**
     * Gets the file.
     *
     * @param string $path The path
     *
     * @return <type> The file
     */
    public static function getFile($path)
    {
        return is_readable($path) ? file_get_contents($path) : null;
    }

    /**
     * Format timestamp to gateway-like format.
     *
     * @param int $timestamp The timestamp
     *
     * @return string
     */
    public static function formatDate($timestamp)
    {
        return gmdate("Y-m-d\TH:i:s\Z", $timestamp);
    }


    /**
     * Return order status based in webhook event.
     *
     * @param      string  $event  The event
     * @param      string $reference
     *
     * @return     mixed  The order status.
     */
    public static function getOrderStatus($event, $reference, $id) {

        switch ($event) {
            case 'card_verified':
            case 'payment_approved':
                return \Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS');
            case 'card_verification_declined':
            case 'payment_declined':
            case 'payment_expired':
            case 'payment_capture_declined':
            case 'payment_void_declined':
            case 'payment_refund_declined':
                \PrestaShopLogger::addLog('The `' . $event .'` was triggered for order ' . $reference . '.', 2, 0, 'CheckoutcomWebhookModuleFrontController' , $id, false);
                return _PS_OS_ERROR_;
            case 'payment_voided':
                return \Configuration::get('CHECKOUTCOM_VOID_ORDER_STATUS');
            case 'payment_canceled':
                 \PrestaShopLogger::addLog('The `' . $event .'` was triggered for order ' . $reference . '.', 2, 0, 'CheckoutcomWebhookModuleFrontController' , $id, false);
                return _PS_OS_CANCELED_;
            case 'payment_captured':
                return \Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS');
            case 'payment_refunded':
                return \Configuration::get('CHECKOUTCOM_REFUND_ORDER_STATUS');
            default:
                return null;
        }
    }
}
