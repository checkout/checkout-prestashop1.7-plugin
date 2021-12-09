<form name="{$module}" id="{$module}-{$key}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">
	<input id="{$module}-{$key}-source" type="hidden" name="source" value="{$key}" required>
	{assign var="id_address_invoice" value=$cart.id_address_invoice}
	<input type="hidden" name="payment_country" value="{$customer.addresses.$id_address_invoice.country_iso}" required>
	<div class="additional-information">
		<ul class="form-list" >
			<li>
				<label for="account_holder_name" class="required">{l s='Account holder name*' mod='checkoutcom'}</label>
				<input type="text" id="account_holder_name" name="account_holder_name" class="form-control input-text required-entry" value="{$customer.firstname} {$customer.lastname}" required>
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