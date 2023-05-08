<?php

namespace CheckoutCom\PrestaShop\Models\Payments\Alternatives;

use Checkout\Payments\Request\Source\Apm\RequestFawrySource;


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

        $source = new RequestFawrySource();
        $source->type='fawry';
        $source->customer_mobile= $billing->phone;
        $source->customer_email = $context->customer->email;
        $source->products  = Fawry::getProducts($context);
        $source->description = \Configuration::get('PS_SHOP_NAME');
        $payment = static::makePaymentToken($source);

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
        $productPrice = 0;
        
        foreach ($context->cart->getProducts() as $item) {

            $productPrice += (int) ('' . (round($item['price_wt'], 2) * 100));
        }
   
        $discount = static::fixAmount($context->cart->getOrderTotal(true, \Cart::ONLY_DISCOUNTS));
        $totalProductPrice = $productPrice - $discount;
        
        $product = (object)[];
        $product->product_id = $context->cart->id;
        $product->quantity = 1;
        $product->price = $totalProductPrice;
        $product->description = \Configuration::get('PS_SHOP_NAME');

        $products[] = $product;
        $shipping = static::getShipping($context);
        if ($shipping && $shipping->price > 0) {
              $products [] = Fawry::getShipping($context);
        }
        return $products;
    }

    /**
     * Get shipping in Product format.
     * @param \Context $context
     * @return Product
     */
    public static function getShipping(\Context $context)
    {
        $description = 'No carrier';
        if($context->cart->id_carrier) {
            $carrier = new \Carrier($context->cart->id_carrier, $context->cart->id_lang);
            $description = $carrier->name . ' Fee';
        }
        $product = (object)[];
        $product->product_id = 0;
        $product->quantity = 1;
        $product->price = static::fixAmount($context->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING));//static::fixAmount($context->order->total_shipping);
        $product->description = $description;
        return $product;
    }

}
