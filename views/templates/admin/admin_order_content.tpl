{*
 * Checkout.com
 * Authorised and regulated as an electronic money institution
 * by the UK Financial Conduct Authority (FCA) under number 900816.
 *
 * PrestaShop v1.7
 *
 * @category  prestashop-module
 * @package   Checkout.com
 * @author    Platforms Development Team <platforms@checkout.com>
 * @copyright 2010-2022 Checkout.com
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://docs.checkout.com/
 *}

{literal}
<script>

    /**
     * On document ready.
     */
    $(document).ready( function () {
        var is_capture = "{$is_capture}" ? true : false;

        // Show refund buttons only when payment is captured
        if (is_capture == true) {
            jQuery('#desc-order-standard_refund').show();
            jQuery('#desc-order-partial_refund').show();
        } else {
            jQuery('#desc-order-standard_refund').hide();
            jQuery('#desc-order-partial_refund').hide();
        }
    });
</script>
{/literal}