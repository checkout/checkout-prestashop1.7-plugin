<?php

use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Helpers\Utilities;

class CheckoutcomWebhookModuleFrontController extends ModuleFrontController
{

    /**
     * List  of actions
     *
     * @var        array
     */
    const ACTIONS = array(  'payment_approved' => 'CHECKOUTCOM_AUTH_ORDER_STATUS',
                            'payment_captured' => 'CHECKOUTCOM_CAPTURE_ORDER_STATUS',
                            'payment_voided' => 'CHECKOUTCOM_VOID_ORDER_STATUS',
                            'payment_refunded' => 'CHECKOUTCOM_REFUND_ORDER_STATUS');

	protected $events = array();

    /**
     * Handle post data.
     */
    public function run()
    {

       $post = file_get_contents("php://input");
        if(Utilities::getValueFromArray($_SERVER, 'HTTP_CKO_SIGNATURE', '') !== hash_hmac('sha256', $post, Configuration::get('CHECKOUTCOM_SECRET_KEY'))) {
			Debug::write('Invalid Webhook.');
			die();
        }

        $data = null;
        parse_str($post, $data);
        if($data) {
        	foreach ($data as $key => $value) {
	        	$this->events []= json_decode($key, true);
	        }
        }

        $this->handleOrder();

    }

    /**
     * Initialize the page.
     */
    public function handleOrder()
    {

    	foreach ($this->events as $event) {

    		$orders = Order::getByReference($event['data']['reference']);
            $list = $orders->getAll();

            $status = Configuration::get('CHECKOUTCOM_FLAGGED_ORDER_STATUS');
            if(isset(static::ACTIONS[$event['type']])) {
                $status = Configuration::get(static::ACTIONS[$event['type']]);
            }

            foreach ($list as $order) {
                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->changeIdOrderState($status, $order->id);
                break;
            }

    	}

    }

}