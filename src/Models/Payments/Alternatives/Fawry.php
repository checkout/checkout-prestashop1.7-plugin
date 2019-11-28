<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Models\Payments\FawrySource;
use Checkout\Models\Product;

class Fawry extends Alternative
{

    /**
     * @param array $params
     * @return \Checkout\Models\Response|mixed
     * @throws \Exception
     */
    public static function pay(array $params)
    {

        $context = \Context::getContext();
        $order = new \Order($context->controller->module->currentOrder);
        $billing = new \Address((int) $context->cart->id_address_invoice);

        $source = new FawrySource(  $context->customer->email,
                                    $billing->phone,
                                    \Configuration::get('PS_SHOP_NAME') . ' ' . $order->getUniqReference(),
                                    Fawry::getProducts($order));

        $payment = static::makePayment($source);

        return static::request($payment);
    }

    /**
     * @param \Order $context
     * @return array
     * @throws \Exception
     */
    public static function getProducts(\Order $order)
    {
        $products = array();
        foreach ($order->getProducts() as $item) {
            $product = new Product();
            $product->product_id = $item['product_id'];
            $product->quantity = 1;
            $product->price = (int) ('' . ($item['total_price_tax_incl'] * 100));
            $product->description = $item['product_quantity'] . 'x ' . $item['product_name'];

            $products[] = $product;
        }

        if(+$order->total_shipping){
            $products [] = Fawry::getShipping($order);
        }

        return $products;
    }

    /**
     * Get shipping in Product format.
     * @param \Order $order
     * @return Product
     */
    public static function getShipping(\Order $order)
    {
        $description = 'No carrier';
        if($order->id_carrier) {
            $carrier = new \Carrier($order->id_carrier, $order->id_lang);
            $description = $carrier->name . ' Fee';
        }

        $product = new Product();
        $product->product_id = 0;
        $product->quantity = 1;
        $product->price = static::fixAmount($order->total_shipping);
        $product->description = $description;

        return $product;
    }

}
