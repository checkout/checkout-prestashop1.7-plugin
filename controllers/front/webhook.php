<?php

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use CheckoutCom\PrestaShop\Models\Payments\Method;

class CheckoutcomWebhookModuleFrontController extends ModuleFrontController
{

    /**
     * List of webhook events.
     *
     * @var        array
     */
    protected $events = array();

    /**
     * Handle post data.
     */
    public function run()
    {

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
    protected function handleOrder()
    {

        foreach ($this->events as $event) {

            $orders = Order::getByReference($event['data']['reference']);
            $list = $orders->getAll();
            $status = +Utilities::getOrderStatus($event['type'], $event['data']['reference'], $event['data']['action_id']);

            if ($status) {

                foreach ($list as $order) {

                    $currentStatus = $order->getCurrentOrderState()->id;
                    if($currentStatus !== $status && $this->preventAuthAfterCapture($currentStatus, $status)) {

                        $isPartial = $this->_isPartialAmount($event, $order);
                        $amount = Method::fixAmount($event['data']['amount'], $event['data']['currency'], true);
                        $currency = $event['data']['currency'];

                        if($isPartial) {

                            $message = $this->trans("An amount of {$currency}{$amount} ");

                            if($event['type'] == 'payment_refunded'){
                                $message .= "has been partially refunded";
                                $status = \Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS');
                            }

                            if($event['type'] == 'payment_captured'){
                                $message .= "has been partially captured";
                            }

                            $this->_addNewPrivateMessage($order, $message);
                        }

                        $history = new OrderHistory();
                        $history->id_order = $order->id;
                        $history->changeIdOrderState($status, $order->id);

                    }
                }
            }
        }
    }

    /**
     * Prevent set it back to Auth status when the webhook comes late (Capture first).
     * @note: no need to add refund or void, as these will never come before auth.
     */
    protected function preventAuthAfterCapture($current, $target) {

        $allow = true;
        if($current === +\Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS') && $target === +\Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS') ) {
            $allow = false;
        }

        return $allow;
    }

    /**
     * @param $order
     * @param $message
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function _addNewPrivateMessage($order, $message)
    {
        // load customer
        $customer = new Customer ($order->id_customer);

        $id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder($customer->email, $order->id);

        // load customer thread
        if (!$id_customer_thread) {
            $customer_thread = new CustomerThread();
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = (int) $order->id_customer;
            $customer_thread->id_shop = (int) $order->id_shop;
            $customer_thread->id_order = (int) $order->id;
            $customer_thread->id_lang = (int) $order->id_lang;
            $customer_thread->email = $customer->email;
            $customer_thread->status = 'open';
            $customer_thread->token = Tools::passwdGen(12);
            $customer_thread->add();
        } else {
            $customer_thread = new CustomerThread((int) $id_customer_thread);
        }

        // Set private note to order
        $customer_message = new CustomerMessage();
        $customer_message->id_customer_thread = $customer_thread->id;
        $customer_message->id_employee = 0;
        $customer_message->message = $message;
        $customer_message->private = 1;

        if (!$customer_message->add()) {
            $this->errors[] = $this->trans('An error occurred while saving message', array(), 'Admin.Payment.Notification');
        }

        return;
    }

    /**
     * @param $event
     * @param $order
     * @return bool
     */
    private function _isPartialAmount($event, $order)
    {
        $webhookAmount = $event['data']['amount'];
        $orderTotal = $order->total_paid;
        $amountTotalCent = Method::fixAmount($orderTotal, $event['data']['currency']);

        if($webhookAmount < $amountTotalCent){
            return true;
        }

        return false;
    }
}
