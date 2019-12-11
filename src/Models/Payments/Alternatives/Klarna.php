<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Product;
use CheckoutCom\PrestaShop\Helpers\Debug;
use Checkout\Models\Payments\KlarnaSource;
use Checkout\Models\Address as KlarnaAddress;

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

        $source = new KlarnaSource($params['authorization_token'],
                                $address->country,
                                $context->language->locale,
                                $address,
                                $tax,
                                static::getProducts($context));

        $payment = static::makePayment($source);

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

        $address = new KlarnaAddress();
        $address->given_name = $billing->firstname;
        $address->family_name = $billing->lastname;
        $address->email = $context->customer->email;
        //$address->title = $billing->getPrefix();
        $address->street_address = $billing->address1;
        $address->street_address2 = $billing->address2;
        $address->postal_code = $billing->postcode;
        $address->city = $billing->city;
        $address->region = \State::getNameById($billing->id_state);
        $address->phone = $billing->phone_mobile;
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
