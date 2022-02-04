<form name="{$module}" id="{$module}-{$key}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">
    <input id="{$module}-{$key}-source" type="hidden" name="source" value="{$key}" required>
    <ul class="form-list" >
        <li>
            <label for="name" class="required">{l s='Select iDeal Bank' mod='checkoutcom'}</label>
            <select class="form-control cvv required-entry validate-cc-cvn" id="{$module}-{$key}-bic" name="bic">
                {foreach $idealBanks as $bank}
                    <option value="{$bank['bic']|escape:'html':'UTF-8'}">
                            {$bank['name']|escape:'html':'UTF-8'}
                    </option>
                {/foreach}
            </select>
            {* <input type="text" id="{$module}-{$key}-bic" name="bic" class="form-control input-text cvv required-entry validate-cc-cvn" required >*}
        </li>
    </ul>
</form>
{literal}
<script type="text/javascript">
    /**
     * Self executable
     */
    (function($form){

    	const $bic = document.getElementById('checkoutcom-ideal-bic');
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

    })(document.getElementById('checkoutcom-ideal-form'));
</script>
{/literal}