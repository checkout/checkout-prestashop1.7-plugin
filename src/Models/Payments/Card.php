<?php

namespace CheckoutCom\PrestaShop\Models\Payments;

use CheckoutCom\PrestaShop\Classes\CheckoutcomCustomerCard;
use CheckoutCom\PrestaShop\Helpers\Utilities;
use Checkout\Models\Payments\TokenSource;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\ThreeDs;
use Checkout\Models\Payments\IdSource;

class Card extends Method
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

        if($params['source'] == 'id') {
            $context = \Context::getContext();
            $customerId = $context->customer->id;
            $entityId = $params['checkoutcom-saved-card'];

            $sourceId = CheckoutcomCustomerCard::getSourceId($entityId, $customerId);

            $source = new IdSource($sourceId);

            if(isset($params['cko-cvv']) && !empty($params['cko-cvv'])){
                $source->cvv = $params['cko-cvv'];
            }

        } else {
            $source = new TokenSource($params['token']);
        }

        $payment = static::makePayment($source);
        static::addMada($payment, Utilities::getValueFromArray($params, 'bin', 0));
        return static::request($payment);
    }

    /**
     * Add MADA to card payments.
     *
     * @param Payment $payment The payment
     * @param int $bin The bin
     */
    protected static function addMada(Payment $payment, $bin = 0)
    {
        if ($bin && \Configuration::get('CHECKOUTCOM_CARD_MADA_CHECK_ENABLED')) {
            $environment = \Configuration::get('CHECKOUTCOM_LIVE_MODE') ? 'production' : 'sandbox';
            $list = json_decode(Utilities::getFile(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . $environment . '.json'), true);

            foreach ($list as $value) {
                if ($value['bin'] == $bin) {
                    $payment->threeDs = new ThreeDs(true);
                    $payment->metadata->udf1 = 'mada';
                    unset($payment->capture);
                    unset($payment->capture_on);

                    if(\Configuration::get('CHECKOUTCOM_CARD_SAVE_CARD_OPTION')){
                        // Load Context
                        $context = \Context::getContext();
                        $context->cookie->__set('is_mada', 1);
                    }

                    return;
                }
            }
        }
    }
}
