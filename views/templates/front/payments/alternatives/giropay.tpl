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
    <ul class="form-list" >
        <li>
            <label for="name" class="required">{l s='Bank Identifier Code (BIC)' mod='checkoutcom'}</label>
            <input type="text" id="{$module|escape:'htmlall':'UTF-8'}-{$key|escape:'htmlall':'UTF-8'}-bic" name="bic" placeholder="" class="form-control input-text cvv required-entry validate-cc-cvn" required>
        </li>
    </ul>
</form>
{literal}
<script type="text/javascript">
    /**
     * Self executable
     */
    (function($form){

    	const $bic = document.getElementById('checkoutcom-giropay-bic');
        var submitted = false; // Prevent multiple submit

        /**
	     * Add form validation.
	     *
	     * @param      {Event}  e
	     */
        $form.onsubmit = function(e) {
          e.preventDefault();
          if($bic.value && !submitted) {
            submitted = true;
            $form.submit();
          }

        };

    })(document.getElementById('checkoutcom-giropay-form'));
</script>
{/literal}