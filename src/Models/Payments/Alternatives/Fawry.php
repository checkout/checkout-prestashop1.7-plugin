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
        $billing = new \Address((int) $context->cart->id_address_invoice);

        $source = new FawrySource($context->customer->email,
            $billing->phone,
            \Order::getOrderByCartId((int)($context->cart->id)),
            static::getProducts($context)
            );
        $payment = static::makePayment($source);
        return static::request($payment);
    }

    /**
     * @param \Context $context
     * @return array
     * @throws \Exception
     */
    public static function getProducts(\Context $context)
    {
        $products = array();
        foreach ($context->cart->getProducts() as $item) {
            $product = new Product();
            $product->product_id = $item['id_product'];
            $product->quantity = 1;
            $product->price = (int) ('' . ($item['total_wt'] * 100));
            $product->description = $item['id_product'];

            $products[] = $product;
        }

        if(static::fixAmount($context->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING)) > 0){
            $shipping = static::getShipping($context);
            if($shipping) {
                $products []= $shipping;
            }
        }

        return $products;
    }

    /**
     * @param \Context $context
     * @return Product|null
     * @throws \Exception
     */
    public static function getShipping(\Context $context)
    {
        $product = null;
        if($context->cart->id_carrier) {
            $product = new Product();
            $product->product_id = 1;
            $product->quantity = 1;
            $product->price = static::fixAmount($context->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING));
            $product->description = 'Shipping';
        }

        return $product;
    }
}
