<?php

use Checkout\Models\Response;
use Checkout\Apm\Previous\Klarna\CreditSessionRequest;
use CheckoutCom\PrestaShop\Classes\CheckoutApiHandler;
use Checkout\Library\Exceptions\CheckoutHttpException;
use CheckoutCom\PrestaShop\Models\Payments\Alternatives\Klarna as KlarnaModel;

class CheckoutcomKlarnaModuleFrontController extends ModuleFrontController
{
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
        $total = (int) ('' . KlarnaModel::fixAmount($this->context->cart->getOrderTotal(true, Cart::BOTH)));
        $tax = (int) ('' . ($total - KlarnaModel::fixAmount($this->context->cart->getOrderTotal(false, Cart::BOTH))));
        $country = Country::getIsoById($billing->id_country);
        $method                      = new CreditSessionRequest();
          //  $method->authorization_token = self::$post['cko-klarna-token'];
            $method->purchase_country    = Country::getIsoById($billing->id_country);
            $method->locale              = strtolower($country).'-'.$country;
            $method->tax_amount          = $tax;
            $method->products            = KlarnaModel::getProducts($this->context);
            $method->billing_address     = $billing;
            $method->amount = $total;
            $method->currency = $this->context->currency->iso_code;


        $result = $this->requestSource($method);
        if (isset($result['session_id'])) {

            if(!$result['payment_method_categories']) {
                \PrestaShopLogger::addLog('Klarna `payment_method_categories` not available for ' . $this->context->currency->iso_code . '-' . $country . ' pair.', 2, 0, 'CheckoutcomKlarnaModuleFrontController' , 0, false);
                die(json_encode($response));
            }

            $response = array(
                'success' => true,
                'client_token' => $result['client_token'],
                'payment_method_categories' => $result['payment_method_categories'],
                'order_amount' => $total,
                'order_tax_amount' => $tax,
                'order_lines' => $method->products,
                'id_address_invoice' => $this->context->cart->id_address_invoice,
            );
        }

        die(json_encode($response));
    }

    /**
     * Safely request source.
     *
     * @param \Checkout\Models\Sources\Klarna $klarna The klarna
     *
     * @return Response
     */
    protected function requestSource($klarna)
    {

        $response = (object)[];

        try {
            
            $response = CheckoutApiHandler::api()->getKlarnaClient()->createCreditSession($klarna);
        } catch (CheckoutHttpException $ex) {
            $response->http_code = $ex->getCode();
            $response->message = $ex->getMessage();
            $response->errors = $ex->getErrors();
            \PrestaShopLogger::addLog($ex->getBody(), 3, $ex->getCode(), 'CheckoutcomKlarnaModuleFrontController' , 0, true);
        }

        return $response;
    }

}
