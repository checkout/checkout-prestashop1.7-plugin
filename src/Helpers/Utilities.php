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

    /**
     * Add private message to order.
     *
     * @param      string  $message  The message
     * @param      Order  $order    The order
     */
    public static function addMessageToOrder($message, \Order $order)
    {

        // load customer
        $customer = new \Customer($order->id_customer);

        $id_customer_thread = \CustomerThread::getIdCustomerThreadByEmailAndIdOrder($customer->email, $order->id);

        // load customer thread
        if (!$id_customer_thread) {
            $customer_thread = new \CustomerThread();
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = (int) $order->id_customer;
            $customer_thread->id_shop = (int) $order->id_shop;
            $customer_thread->id_order = (int) $order->id;
            $customer_thread->id_lang = (int) $order->id_lang;
            $customer_thread->email = $customer->email;
            $customer_thread->status = 'open';
            $customer_thread->token = \Tools::passwdGen(12);
            $customer_thread->add();
        } else {
            $customer_thread = new \CustomerThread((int) $id_customer_thread);
        }

        // Set private note to order
        $customer_message = new \CustomerMessage();
        $customer_message->id_customer_thread = $customer_thread->id;
        $customer_message->id_employee = 0;
        $customer_message->message = $message;
        $customer_message->private = 1;

        return $customer_message->add();

    }

}
