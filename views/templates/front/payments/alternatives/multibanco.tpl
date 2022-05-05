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
	{assign var="id_address_invoice" value=$cart.id_address_invoice}
	<input type="hidden" name="payment_country" value="{$customer.addresses.$id_address_invoice.country_iso|escape:'htmlall':'UTF-8'}" required>
	<div class="additional-information">
		<ul class="form-list" >
			<li>
				<label for="account_holder_name" class="required">{l s='Account holder name*' mod='checkoutcom'}</label>
				<input type="text" id="account_holder_name" name="account_holder_name" class="form-control input-text required-entry" value="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}" required>
			</li>
		</ul>
	</div>
</form>
{literal}
<script type="text/javascript">
	/**
	 * Self executable
	 */
	(function($form){

		var submitted = false; // Prevent multiple submit

		/**
		 * Add form validation.
		 *
		 * @param      {Event}  e
		 */
		$form.onsubmit = function(e) {
		  e.preventDefault();
		  if(!submitted) {
			submitted = true;
			$form.submit();
		  }

		};

	})(document.getElementById('checkoutcom-multibanco-form'));
</script>
{/literal}