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
            if(!isset($event['data']) || !isset($event['data']['id'])){
                die();
            }
            $this->module->logger->info('Channel Webhook -- event type : ' .$event['type']);
            $cart_id = str_replace( 'CART_', '', $event['data']['reference'] );
            $payment_id =  $event['data']['id'];
            

            $this->module->logger->info('Channel Webhook -- New payment : ' .$payment_id);
            $sql = 'SELECT `order_reference` FROM `'._DB_PREFIX_.'order_payment` WHERE `transaction_id`='.'"'.$payment_id.'"';
            $order_reference =  Db::getInstance()->getValue($sql);
            $this->module->logger->info('Channel Webhook -- New sql : ' .$sql);
            $this->module->logger->info('Channel Webhook -- New order reference from payment : ' .$order_reference);

            //Defensive code to handle missing payment mapping
            if(empty($order_reference)  && $event['type'] == "payment_captured"){
                $this->module->logger->info('Channel Webhook -- order reference not found for payment. Attempting to find by cart : ' .$cart_id);
                $sql = 'SELECT `id_order`,`reference`,`id_shop` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart`='.$cart_id;
                $order_result = Db::getInstance()->executeS($sql);
                $order_reference = $order_result[0]['reference'];
                $order_id = $order_result[0]['id_order'];
                $this->module->logger->info('Channel Webhook -- New order reference from payment : ' .$order_reference);
                if(!isset($order_reference)){
                    die();
                }

                //Update payment id to order incase of successful capture and missing payment info.
                $order = new Order($order_id);
                $payments = $order->getOrderPaymentCollection();
                $payments[0]->transaction_id = $payment_id;
                $payments[0]->update();
            }
           
            
            $sql = 'SELECT `id_shop` FROM `'._DB_PREFIX_.'orders` WHERE `reference`="'.$order_reference.'"';
            $this->module->logger->info('Channel Webhook -- New sql shop: ' .$sql);
            $order_id_shop = Db::getInstance()->getValue($sql);
            $this->module->logger->info('Channel Webhook -- shop: ' .$order_id_shop);
            $this->module->logger->info('Channel Webhook -- actionid: ' .$event['data']['action_id']);
            // $order_id_shop = $order_result[0][0];
            $orders = Order::getByReference($order_reference);
            $list = $orders->getAll();
            $status = +Utilities::getOrderStatus($event['type'], $order_reference, $event['data']['action_id'], $order_id_shop);

            if ($status) {

                foreach ($list as $order) {
                   
                    $currentStatus = $order->getCurrentOrderState()->id;
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
                       
                        if ($this->isOrderBackOrder($order->id)) {
                            $status = \Configuration::get('CHECKOUTCOM_CAPTURE_BACKORDER_STATUS');
                        }
                    }
                    else if($event['type'] == 'payment_refunded'){

                        //Check if all the items in order have been refunded
                        $isFullRefund = $this->_isFullRefund($order);
                        if($isFullRefund){
                            $status = \Configuration::get('CHECKOUTCOM_REFUND_ORDER_STATUS');
                        }
                        else{
                            $status = $currentStatus;
                        }
                        
                    }

                    $this->module->logger->info('Channel Webhook -- current status: ' .$currentStatus);
                    $this->module->logger->info('Channel Webhook --  status: ' . $status );

                    if($currentStatus !== $status && $this->preventAuthAfterCapture($currentStatus, $status)) {
                        $this->module->logger->info('Channel Webhook --  Valid state change: ');
                        $isPartial = $this->_isPartialAmount($event, $order);
                        $amount = Method::fixAmount($event['data']['amount'], $event['data']['currency'], true);
                        $currency = $event['data']['currency'];
                        $this->module->logger->info('Channel Webhook --  Valid state change: ');
                        if($isPartial) {

                            $message = $this->trans("An amount of %currency% %amount% ", 
                            ['%currency%' => $currency, '%amount%' => $amount], 
                            'Modules.Checkoutcom.Webhook.php');

                            // if($event['type'] == 'payment_refunded'){
                            //     $message .= "has been partially refunded";
                            //     $status = \Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS');
                            // }

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
                        $history->add();
                        $this->module->logger->info('Channel Webhook -- New order status : ' . $status);
                        // try{
                        //     $history->addWithemail();
                        // }
                        // catch(Exception $e) {
                        //     $this->module->logger->info('Channel Webhook -- Failed addWithemail : '.$e->getMessage());
                        //  }
                        // $this->module->logger->info('Channel Webhook -- Mail sent : ' . $order->id);
                        
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
        if(($current === +\Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS') || $current === +\Configuration::get('CHECKOUTCOM_CAPTURE_BACKORDER_STATUS')) && $target === +\Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS') ) {
            $allow = false;
        }

        if($current === +\Configuration::get('CHECKOUTCOM_REFUND_ORDER_STATUS') && $target === +\Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS') ) {
            $allow = false;
        }

        if((int)$current === (int)_PS_OS_ERROR_ && ($target === +\Configuration::get('CHECKOUTCOM_CAPTURE_ORDER_STATUS') || $target === +\Configuration::get('CHECKOUTCOM_AUTH_ORDER_STATUS') || $target === +\Configuration::get('CHECKOUTCOM_CAPTURE_BACKORDER_STATUS')) ) {
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

     /**
     * Check if the order is a back order
     * @param $orderId
     * @return bool
     */
    private function isOrderBackOrder($orderId)
    {
        $order = new Order($orderId);
        $orderDetails = $order->getOrderDetailList();
        /** @var OrderDetail $detail */
        foreach ($orderDetails as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            if (
                \Configuration::get('PS_STOCK_MANAGEMENT') &&
                ($orderDetail->getStockState() || $orderDetail->product_quantity_in_stock <= 0)
            ) {
                return true;
            }
        }

        return false;
    }

     /**
     * Check if all the items in the order are refunded (to mark the overall status of the order) 
     * @param $order
     * @return bool
     */
    private function _isFullRefund($order)
    {
        $refunded_products=0;
        $sql = 'SELECT * FROM '._DB_PREFIX_."order_detail  WHERE `id_order` = '" .$order->id . "'"; 
        $orderDetails = Db::getInstance()->executeS($sql);
        $totalProducts = 0;
        foreach($orderDetails as $orderdetail){

            $refunded_products += $orderdetail['product_quantity_return']+ $orderdetail['product_quantity_refunded'];
            $totalProducts += $orderdetail['product_quantity'];
        }
       
        if($totalProducts === $refunded_products){
            return true;
        }
        return false;
    }
}
