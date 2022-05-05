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

 <form name="{$module|escape:'htmlall':'UTF-8'}" id="{$module|escape:'htmlall':'UTF-8'}-{$key|escape:'htmlall':'UTF-8'}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'htmlall':'UTF-8'}" method="POST">
    <input id="{$module|escape:'htmlall':'UTF-8'}-{$key|escape:'htmlall':'UTF-8'}-source" type="hidden" name="source" value="{$key|escape:'htmlall':'UTF-8'}" required>
    <div id="{$module|escape:'htmlall':'UTF-8'}-{$key|escape:'htmlall':'UTF-8'}-div">
        <ul class="form-list" >
            <li>
                <label for="name" class="required">{l s='International Bank Account Number (IBAN)' mod='checkoutcom'}</label>
                <input type="text" id="{$module|escape:'htmlall':'UTF-8'}-{$key|escape:'htmlall':'UTF-8'}-iban" name="iban" placeholder="" class="form-control input-text cvv required-entry validate-cc-cvn" required>
            </li>
            <li>
                <label for="name" class="required">{l s='Bank Identifier Code (BIC)' mod='checkoutcom'}</label>
                <input type="text" id="{$module|escape:'htmlall':'UTF-8'}-{$key|escape:'htmlall':'UTF-8'}-bic" name="bic" placeholder="" class="form-control input-text cvv required-entry validate-cc-cvn" required>
            </li>
        </ul>
        <input type="button" id="{$module|escape:'htmlall':'UTF-8'}-{$key|escape:'htmlall':'UTF-8'}-generator" name="mandate" placeholder="" value="{l s='GENERATE MANDATE' mod='checkoutcom'}" class="btn btn-primary center-block" style="margin-bottom: 14px;">
    </div>
    <div id="{$module|escape:'htmlall':'UTF-8'}-{$key|escape:'htmlall':'UTF-8'}-frame" data-url="{$link->getModuleLink($module, 'sepa', [], true)|escape:'htmlall':'UTF-8'}" data-module="{$module|escape:'htmlall':'UTF-8'}"></div>
</form>
{literal}
<script type="text/javascript">
    /**
     * Self executable
     */
    (function($form){

        const $button = document.getElementById('checkoutcom-sepa-generator');
        const $bic = document.getElementById('checkoutcom-sepa-bic');
        const $iban = document.getElementById('checkoutcom-sepa-iban');
        var submitted = false; // Prevent multiple submit

        /**
         * On click generate mandate.
         *
         * @param      {<type>}  e       { parameter_description }
         */
        $button.onclick = function(e) {

            if($bic.value && $iban.value) {
                window.loadMandate(e);
            }

        };

        /**
         * Add form validation.
         *
         * @param      {Event}  e
         */
        $form.onsubmit = function(e) {
          e.preventDefault();

          const $terms = document.getElementById('checkoutcom-sepa-accept-terms');
          if($terms && $terms.checked && !submitted) {
            submitted = true;
            $form.submit();
          }

        };

    })(document.getElementById('checkoutcom-sepa-form'));
</script>
{/literal}