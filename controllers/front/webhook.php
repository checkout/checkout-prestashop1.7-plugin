<?php

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;

class CheckoutcomWebhookModuleFrontController extends ModuleFrontController
{
    /**
     * List  of actions
     *
     * @var array
     */
    const ACTIONS = array('payment_approved' => 'CHECKOUTCOM_AUTH_ORDER_STATUS',
                            'payment_captured' => 'CHECKOUTCOM_CAPTURE_ORDER_STATUS',
                            'payment_voided' => 'CHECKOUTCOM_VOID_ORDER_STATUS',
                            'payment_refunded' => 'CHECKOUTCOM_REFUND_ORDER_STATUS', );

    protected $events = array();

    /**
     * Handle post data.
     */
    public function run()
    {
        Debug::write('Webhook.run()');
        $post = file_get_contents('php://input');
        if (Utilities::getValueFromArray($_SERVER, 'HTTP_CKO_SIGNATURE', '') !== hash_hmac('sha256', $post, Configuration::get('CHECKOUTCOM_SECRET_KEY'))) {
            \PrestaShopLogger::addLog('Invalid inbound webhook.', 1, 0, 'CheckoutcomWebhookModuleFrontController' , 0, false);
            die();
        }

        $data = null;
        parse_str($post, $data);
        if ($data) {
            foreach ($data as $key => $value) {
                $this->events[] = json_decode($key, true);
            }
        }

        $this->handleOrder();
    }

    /**
     * Update order status based on webhook.
     */
    public function handleOrder()
    {

        foreach ($this->events as $event) {
            $orders = Order::getByReference($event['data']['reference']);
            $list = $orders->getAll();
            $status = Utilities::getOrderStatus($event['type'], $event['data']['reference'], $event['data']['action_id']);
            if ($status) {

                foreach ($list as $order) {
                    if($order->getCurrentOrderState() !== $status) {
                        $history = new OrderHistory();
                        $history->id_order = $order->id;
                        $history->changeIdOrderState($status, $order->id);
                        break;
                    }
                }

            }

        }
    }
}
