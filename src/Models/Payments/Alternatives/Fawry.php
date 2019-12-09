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
        $billing = new \Address($context->order->id_address_invoice);

        $source = new FawrySource(  $context->customer->email,
                                    $billing->phone,
                                    \Configuration::get('PS_SHOP_NAME') . ' ' . $context->order->getUniqReference(),
                                    Fawry::getProducts($context));

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
        $productPrice = 0;

        foreach ($context->order->getProducts() as $item) {

            $productPrice += (int) ('' . ($item['total_price_tax_incl'] * 100));
        }

        $discount = static::fixAmount($context->order->total_discounts);
        $totalProductPrice = $productPrice - $discount;

        $product = new Product();
        $product->product_id = \Configuration::get('PS_SHOP_NAME'). ' - Order reference :'. $context->order->getUniqReference();
        $product->quantity = 1;
        $product->price = $totalProductPrice;
        $product->description = \Configuration::get('PS_SHOP_NAME'). ' - Order reference :'. $context->order->getUniqReference();

        $products[] = $product;

        if(+$context->order->total_shipping){
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
        if($context->order->id_carrier) {
            $carrier = new \Carrier($context->order->id_carrier, $context->order->id_lang);
            $description = $carrier->name . ' Fee';
        }

        $product = new Product();
        $product->product_id = 0;
        $product->quantity = 1;
        $product->price = static::fixAmount($context->order->total_shipping);
        $product->description = $description;

        return $product;
    }

}
