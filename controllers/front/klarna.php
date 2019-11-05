<?php

use Checkout\Models\Response;
use Checkout\Models\Sources\Klarna;
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

        $klarna = new Klarna(
                $country,
                $this->context->currency->iso_code,
                $this->context->language->locale,
                $total,
                $tax,
                KlarnaModel::getProducts($this->context)
            );

        $result = $this->requestSource($klarna);

        if ($result->isSuccessful()) {

            if(!$result->payment_method_categories) {
                \PrestaShopLogger::addLog('Klarna `payment_method_categories` not available for ' . $this->context->currency->iso_code . '-' . $country . ' pair.', 2, 0, 'CheckoutcomKlarnaModuleFrontController' , 0, false);
                die(json_encode($response));
            }

            $response = array(
                'success' => true,
                'client_token' => $result->client_token,
                'payment_method_categories' => $result->payment_method_categories,
                'order_amount' => $total,
                'order_tax_amount' => $tax,
                'order_lines' => $klarna->products,
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
    protected function requestSource(Klarna $klarna)
    {

        $response = new Response();

        try {
            $response = CheckoutApiHandler::api()->sources()->add($klarna);
        } catch (CheckoutHttpException $ex) {
            $response->http_code = $ex->getCode();
            $response->message = $ex->getMessage();
            $response->errors = $ex->getErrors();
            \PrestaShopLogger::addLog($ex->getBody(), 3, $ex->getCode(), 'CheckoutcomKlarnaModuleFrontController' , 0, true);
        }

        return $response;
    }

}
