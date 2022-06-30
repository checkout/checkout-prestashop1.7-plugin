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
        if(_PS_VERSION_ > '1.7.6.0'){
            global $kernel;

            if(!$kernel){ 
              require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
              
              $kernel = new \AppKernel('prod', (bool) \Configuration::get('CHECKOUTCOM_LIVE_MODE'));
              $kernel->boot(); 
            }
        }

        $post = file_get_contents('php://input');
        if (Utilities::getValueFromArray($_SERVER, 'HTTP_CKO_SIGNATURE', '') !== hash_hmac('sha256', $post, Configuration::get('CHECKOUTCOM_SECRET_KEY'))) {
            \PrestaShopLogger::addLog('Invalid inbound webhook.', 1, 0, 'CheckoutcomWebhookModuleFrontController' , 0, true);
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
            // $cart_id = str_replace( 'CART_', '', $event['data']['reference'] );
            // $sql = 'SELECT `reference` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart`='.$cart_id;
            // $order_reference = Db::getInstance()->getValue($sql);

            $payment_id =  $event['data']['id'] );
            $sql = 'SELECT `order_reference` FROM `'._DB_PREFIX_.'order_payments` WHERE `transaction_id`='.$payment_id;
            $order_reference = Db::getInstance()->getValue($sql);

            //log event details
            $this->module->logger->info('Channel Webhook -- Handle order -- Event Type : ' . $event['type']);
            $this->module->logger->info('Channel Webhook -- Handle order -- Event action id : ' . $event['data']['action_id']);
            $this->module->logger->info('Channel Webhook -- Handle order -- Event amount : ' . $event['data']['amount']);
            $this->module->logger->info('Channel Webhook -- Handle order -- Event currency : ' . $event['data']['currency']);
            $this->module->logger->info('Channel Webhook -- Handle order -- Cart id : ' .  $cart_id);
            $this->module->logger->info('Channel Webhook -- Handle order -- Order reference : ' .  order_reference);
  
            $orders = Order::getByReference($order_reference);
            $list = $orders->getAll();
            $status = +Utilities::getOrderStatus($event['type'], $order_reference, $event['data']['action_id']);

            if ($status) {
                //log status
                $this->module->logger->info('Channel Webhook -- Handle order -- Order status for  above order reference: ' .  $status);

                foreach ($list as $order) {

                    $currentStatus = $order->getCurrentOrderState()->id;

                    //log current status
                    $this->module->logger->info('Channel Webhook -- Begin order processing:'. $order->id);
                    $this->module->logger->info('Channel Webhook -- Handle order -- current order status : ' .   $currentStatus);
  
                    if($currentStatus !== $status && $this->preventAuthAfterCapture($currentStatus, $status)) {

                        $isPartial = $this->_isPartialAmount($event, $order);
                        $amount = Method::fixAmount($event['data']['amount'], $event['data']['currency'], true);
                        $currency = $event['data']['currency'];
                        
                        //log is partial
                        $this->module->logger->info('Channel Webhook -- Handle order -- is partial amount? : ' .   $isPartial);

                        if($isPartial) {

                            $message = $this->trans("An amount of %currency% %amount% ", 
                            ['%currency%' => $currency, '%amount%' => $amount], 
                            'Modules.Checkoutcom.Webhook.php');

                            if($event['type'] == 'payment_refunded'){
                                $message .= "has been partially refunded";
                                $status = \Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS');
                            }

                            if($event['type'] == 'payment_captured'){
                                $message .= "has been partially captured";
                            }

                            if(!Utilities::addMessageToOrder($message, $order)) {
                                $this->errors[] = $this->trans('An error occurred while saving message', [], 'Admin.Payment.Notification');
                            }

                        }

                        $history = new OrderHistory();
                        $history->id_order = $order->id;
                        $history->changeIdOrderState($status, $order->id, true);
                        $history->addWithemail();

                        //log new order history and status
                        $this->module->logger->info('Channel Webhook -- New order id : ' . $order->id);
                        $this->module->logger->info('Channel Webhook -- New order status : ' . $status);
                    }
                    //log end of processing for each order
                    $this->module->logger->info('-- -- Channel Webhook -- End of order processing:'. $order->id);
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
