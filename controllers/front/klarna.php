<?php

use Checkout\Models\Product;
use Checkout\Models\Response;
use Checkout\Models\Sources\Klarna;
use CheckoutCom\PrestaShop\Helpers\Debug;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;
use CheckoutCom\PrestaShop\Models\Payments\Alternatives\Klarna as KlarnaModel;



class CheckoutcomKlarnaModuleFrontController extends ModuleFrontController {

	/**
     * If set to true, page content and messages will be encoded to JSON before responding to AJAX request.
     *
     * @var bool
     */
    protected $json = true;

    /**
     * Initialize the page.
     */
    public function init()
    {
        header('Content-Type: application/json; charset=utf-8');
        parent::init();
    }

    /**
     * Initialize the page.
     */
    public function initContent()
    {

        $response = array('success' => false);
        $billing = new Address((int) $this->context->cart->id_address_invoice);

        // Float precision workaround
        $total = (int) (''.$this->context->cart->getOrderTotal(true, Cart::BOTH) *100);
        $tax = (int) (''.($total - $this->context->cart->getOrderTotal(false, Cart::BOTH) *100));

        $klarna = new Klarna(
                Country::getIsoById($billing->id_country),
                $this->context->currency->iso_code,
                $this->context->language->locale,
                $total,
                $tax,
                KlarnaModel::getProducts($this->context)
            );

        $result = $this->requestSource($klarna);
        if($result && $result->isSuccessful()) {

            $response = array(
                'success' => true,
                'client_token' => $result->client_token,
                'payment_method_categories' => $result->payment_method_categories,
                'order_amount' => $total,
                'order_tax_amount' => $tax,
                'order_lines' => $klarna->products,
                'id_address_invoice' => $this->context->cart->id_address_invoice
            );

        }

        die(json_encode($response));
    }

    /**
     * Safely request source.
     *
     * @param      \Checkout\Models\Sources\Klarna  $klarna  The klarna
     *
     * @return     Response
     */
    protected function requestSource(Klarna $klarna) {

        $result = null;

        try {
            $result = CheckoutApiHandler::api()->sources()->add($klarna);
        } catch (CheckoutHttpException $e) {
            //@todo: handle errors
        }

        return $result;

    }


}