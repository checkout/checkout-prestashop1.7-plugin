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



    public static function getOrderStatus($event) {

        switch ($event) {
            case 'card_verified':
            case 'payment_approved':
            case 'payment_pending':
                return _PS_OS_PREPARATION_;

            case 'dispute_canceled':
            case 'dispute_evidence_required':
            case 'dispute_expired':
            case 'dispute_lost':
            case 'dispute_resolved':
            case 'dispute_won':
            case 'payment_retrieval':
            case 'source.updated':
                return '';


            case 'card_verification_declined':
            case 'payment_declined':
            case 'payment_expired':
            case 'payment_capture_declined':
                return _PS_OS_ERROR_;
            case 'payment_voided':
                return '';
            case 'payment_canceled':
                return _PS_OS_CANCELED_;
            case 'payment_void_declined':
                return _PS_OS_DELIVERED_;
            case 'payment_captured':
                return _PS_OS_DELIVERED_;

            case 'payment_capture_pending':
                return _PS_OS_PREPARATION_;
            case 'payment_refunded':
                return _PS_OS_DELIVERED_;
            case 'payment_refund_declined':
                return _PS_OS_DELIVERED_;


            case 'payment_refund_pending':
                return _PS_OS_PREPARATION_;









        }

    }
}
