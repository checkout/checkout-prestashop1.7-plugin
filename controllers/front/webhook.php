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
            if (strpos(Configuration::get('CHECKOUTCOM_SECRET_KEY'), 'Bearer ') === false) {
                die();
            }
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
            $cart_id = str_replace( 'CART_', '', $event['data']['reference'] );
            $sql = 'SELECT `reference`,`id_shop` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart`='.$cart_id;
            $order_result = Db::getInstance()->executeS($sql);
            $order_reference = $order_result[0]['reference'];
            $order_id_shop = $order_result[0]['id_shop'];

            $orders = Order::getByReference($order_reference);
            $list = $orders->getAll();
            $status = +Utilities::getOrderStatus($event['type'], $order_reference, $event['data']['action_id'], $order_id_shop);

            if ($status) {

                foreach ($list as $order) {

                    if($event['type'] == 'payment_captured'){
                        $sql = 'SELECT * FROM '._DB_PREFIX_."checkoutcom_adminorder WHERE `transaction_id` = '".$event['data']['reference']."'"; 
                        $row = Db::getInstance()->getRow($sql);

                        if ( empty($row) ) {
                            $sql  = "INSERT INTO "._DB_PREFIX_."checkoutcom_adminorder (`transaction_id`, `amount_captured`, `amount_refunded`)";
                            $sql .= "VALUES ('".$event['data']['reference']."', ".($event['data']['balances']['total_captured']/100).", 0)";
                            Db::getInstance()->execute($sql);
                        }else{
                            $sql  = "UPDATE "._DB_PREFIX_."checkoutcom_adminorder";
                            $sql .= " SET `amount_captured`=".($event['data']['balances']['total_captured']/100);
                            $sql .= " WHERE `transaction_id`='".$event['data']['reference']."'";
                            Db::getInstance()->execute($sql);
                        }
                    }

                    $currentStatus = $order->getCurrentOrderState()->id;
                    if($currentStatus !== $status && $this->preventAuthAfterCapture($currentStatus, $status)) {

                        $isPartial = $this->_isPartialAmount($event, $order);
                        $amount = Method::fixAmount($event['data']['amount'], $event['data']['currency'], true);
                        $currency = $event['data']['currency'];

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
                        $this->module->logger->info('Channel Webhook -- New order status : ' . $order->id);
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
