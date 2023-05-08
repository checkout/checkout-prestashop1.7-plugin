<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use CheckoutCom\PrestaShop\Helpers\Debug;
use Checkout\Payments\Request\Source\Apm\RequestKlarnaSource;
use Checkout\Common\Address;
use Checkout\Common\Product;

class Klarna extends Alternative
{
    /**
     * Process payment.
     *
     * @param array $params The parameters
     *
     * @return Response ( description_of_the_return_value )
     */
    public static function pay(array $params)
    {
        $context = \Context::getContext();
        $address = static::getBillingAddress($context);

        // Float workaround
        $total = (int) ('' . $context->cart->getOrderTotal(true, \Cart::BOTH) * 100);
        $tax = (int) ('' . ($total - $context->cart->getOrderTotal(false, \Cart::BOTH) * 100));

        $source = new RequestKlarnaSource();
        $source->account_holder = (object)[];
        $source->account_holder->billing_address = $address;
        $source->account_holder->first_name = $address->given_name;
        $source->account_holder->last_name = $address->family_name;
        $source->account_holder->email = $address->email;
        $source->account_holder->phone = (object)[];
        $source->account_holder->phone->country_code = $address->country;
        $source->account_holder->phone->number = $address->phone;
        $source->authorization_token = $params['authorization_token'];
        $source->purchase_country    = $address->country;
        $source->locale              = strtolower($address->country).'-'.$address->country;
        $source->tax_amount          = $tax;
        $source->products            = static::getProducts($context);
        $source->billing_address     =  $address;
        
        $payment = static::makePaymentToken($source);

        return static::request($payment);
    }

    /**
     * Gets the products.
     *
     * @param \Context $context The context
     *
     * @return array the products
     */
    public static function getProducts(\Context $context)
    {
        $products = array();
        foreach ($context->cart->getProducts() as $item) {
            $product = new Product();
            $product->name = $item['name'];
            $product->quantity = (int) $item['cart_quantity'];
            $product->unit_price = (int) ('' . ($item['price_wt'] * 100));
            $product->tax_rate = (int) ('' . ($item['rate'] * 100));
            $product->total_amount = (int) ('' . ($item['total_wt'] * 100));
            $product->total_tax_amount = (int) ('' . (($item['total_wt'] - $item['total']) * 100));

            $products[] = $product;
        }

        $shipping = static::getShipping($context);
        if($shipping) {
            $products []= $shipping;
        }
        return $products;
    }

    /**
     * Gets the billing address.
     *
     * @param \Context $context The context
     *
     * @return KlarnaAddress the billing address
     */
    public static function getBillingAddress(\Context $context)
    {
        $billing = new \Address((int) $context->cart->id_address_invoice);

        $address = new \Address();
        $address->given_name = $billing->firstname;
        $address->family_name = $billing->lastname;
        $address->email = $context->customer->email;
        //$address->title = $billing->getPrefix();
        $address->street_address = $billing->address1;
        $address->street_address2 = $billing->address2;
        $address->postal_code = $billing->postcode;
        $address->zip = $billing->postcode?$billing->postcode:'12344';
        $address->city = $billing->city;
        $address->region = \State::getNameById($billing->id_state);
        $address->phone = $billing->phone_mobile?$billing->phone_mobile:'123456';
        $address->country = \Country::getIsoById($billing->id_country);

        return $address;
    }

    /**
     * Gets the billing address.
     *
     * @param \Context $context The context
     *
     * @return KlarnaAddress the billing address
     */
    public static function getShipping(\Context $context)
    {

        $product = null;
        if($context->cart->id_carrier) {

            $carrier = new \Carrier($context->cart->id_carrier, $context->cart->id_lang);
            $product = new Product();
            $product->name = $carrier->name;
            $product->quantity = 1;
            $product->tax_rate = (int) $carrier->getTaxesRate(new \Address((int) $context->cart->id_address_delivery)) * 100;
            $product->unit_price = static::fixAmount($context->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING));
            $product->total_amount = $product->unit_price;
            $product->total_tax_amount = $product->unit_price - static::fixAmount($context->cart->getOrderTotal(false, \Cart::ONLY_SHIPPING));
            $product->type = 'shipping_fee';

        }

        return $product;

    }

}
