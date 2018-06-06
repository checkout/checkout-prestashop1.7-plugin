<?php

class CheckoutapipaymentWebhookModuleFrontController extends ModuleFrontController
{
	//Process webhook
	public function run()
	{
		$stringCharge     = file_get_contents("php://input");
		
		if (version_compare(PHP_VERSION, '5.4.0', '<')) {
			$http_response_code_200 = header("HTTP/1.1 200 OK");
		} else {
			$http_response_code_200 = http_response_code(200);
		}

		if(empty($stringCharge)){ 
			return http_response_code(400);
        }

        $data = json_decode($stringCharge);
        $eventType          = $data->eventType;

		$Api    =    CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE')));
		$objectCharge = $Api->chargeToObj($stringCharge);
		$dbLog = models_FactoryInstance::getInstance( 'models_DataLayer' );
        $transaction = $dbLog->getOrderId($objectCharge->getId());
		$id_order = $objectCharge->getTrackId();

		$order = new Order($id_order);
		$history = new OrderHistory();
		$history->id_order = $id_order;
		$current_order_state = $order->getCurrentOrderState();

		if($eventType == 'charge.succeeded'){
			return $http_response_code_200;

		} elseif($eventType == 'charge.captured'){
			if(!$order->hasBeenPaid()) {
				$order_state = new OrderState(Configuration::get('PS_OS_PAYMENT'));
				if (!Validate::isLoadedObject($order_state)) {
					echo sprintf(Tools::displayError('Order status #%d cannot be loaded'), Configuration::get('PS_OS_PAYMENT'));
					 return $http_response_code_200;
				}else {
					$current_order_state = $order->getCurrentOrderState();

					if ($current_order_state->id == $order_state->id ) {
						echo  sprintf ( Tools::displayError ( 'Order #%d has already been captured.' ) , $id_order);
						return $http_response_code_200;
					} else {
						$order->setCurrentState(Configuration::get('PS_OS_PAYMENT')); 
						echo  sprintf ( Tools::displayError ( 'Order #%d has  been captured.' ) ,
							$id_order);
						$message = 'Order has been captured . captured ChargeId - '.$objectCharge->getId();
						$this->_addNewPrivateMessage((int) $id_order, $message);
						return $http_response_code_200;
					}
				}
			} else {
				echo 'Payment was already captured for Transaction ID '.$objectCharge->getId();
				return $http_response_code_200;
			}
		} elseif($eventType == 'charge.refunded'){
			$order_state = new OrderState(Configuration::get('PS_OS_REFUND'));

			if ($current_order_state->id == $order_state->id ) {
				echo  sprintf ( Tools::displayError ( 'Order #%d has already been refunded.' ) , $id_order );
				return $http_response_code_200;
			}else {

				$responseAmount = $data->message->value;
				$totalPaid = number_format((float)$order->total_paid, 2, '.', '');
				$currency = $data->message->currency;
				$totalPaidCent = $Api->valueToDecimal($totalPaid,$currency);

				if($responseAmount != $totalPaidCent){
					$order->setCurrentState(Configuration::get('PS_OS_PARTIAL_REFUND'));

					$message = 'Order has been partially refunded. Refunded ChargeId - '.$data->message->id;
					$this->_addNewPrivateMessage((int) $id_order, $message);

					echo  sprintf ( Tools::displayError ( 'Order #%d has been partially refunded.' ) , $id_order );
				} else {
					$history->changeIdOrderState ( Configuration::get ( 'PS_OS_REFUND' ) , (int)$id_order );
					$history->addWithemail ();
					echo  sprintf ( Tools::displayError ( 'Order #%d has been refunded.' ) , $id_order );
				}

				return $http_response_code_200;
			}
		} elseif ($eventType == 'charge.voided' || $eventType == 'invoice.cancelled') {
			$order_state = new OrderState(Configuration::get('PS_OS_CANCELED'));

			if ($current_order_state->id == $order_state->id ) {
				echo  sprintf ( Tools::displayError ( 'Order #%d has already been '.$objectCharge->getStatus() ) , $id_order );
				return $http_response_code_200;
			}elseif(!$objectCharge->getAuthorised ()){
				$history->changeIdOrderState ( Configuration::get ( 'PS_OS_CANCELED' ) , (int)$id_order );
				$history->addWithemail ();
				echo  sprintf ( Tools::displayError ( 'Order #%d has  been '.$objectCharge->getStatus() ) , $id_order );
				return $http_response_code_200;
			}
		} else { 
			$logger = new FileLogger(0);
			$logger->setFilename(_PS_ROOT_DIR_."/modules/checkoutapipayment/webhook.log");
			$logger->logDebug($data);
			return $http_response_code_200;
		}
	}

	public function _addNewPrivateMessage($id_order, $message)
    {
        if (!(bool) $id_order) {
            return false;
        }

        $new_message = new Message();
        $message = strip_tags($message, '<br>');

        if (!Validate::isCleanHtml($message)) {
            $message = $this->l('Payment message is not valid, please check your module.');
        }

        $new_message->message = $message;
        $new_message->id_order = (int) $id_order;
        $new_message->private = 1;

        return $new_message->add();
    }
}