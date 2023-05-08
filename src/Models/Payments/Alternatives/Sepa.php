<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Common\CustomerRequest;
use Checkout\Payments\Request\Source\RequestIdSource as IdSource;
use CheckoutCom\PrestaShop\Helpers\Utilities;

class Sepa extends Alternative
{

	/**
     * Process payment.
     *
     * @param array $params The parameters
     *
     * @return Response
     */
    public static function pay(array $params)
    {
    	$response = parent::pay($params);

    	$id = Utilities::getValueFromArray($params, 'id', false);
    	if($id) {
            $source  = (object)[];
            $source->type = 'id';
            $source->id = $id;
	        $payment = static::makePaymentToken($source, $params);
	        $response = static::request($payment);
    	}

    	return $response;

    }


    /**
     * Get Customer information.
     *
     * @param \Context $context The context
     *
     * @return Customer the metadata
     */
    protected static function getCustomer(\Context $context, array $params)
    {
    	$customer = new CustomerRequest();
    	$id = Utilities::getValueFromArray($params, 'customer_id', false);
    	if($id) {
	        $customer->id = $id;
    	} else {
    		$customer->id = $context->customer->email;
	        $customer->name = $context->customer->firstname . ' ' . $context->customer->lastname;
    	}

    	return $customer;
    }

}
