<form name="{$module}" id="{$module}-{$key}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">
    <input id="{$module}-{$key}-source" type="hidden" name="source" value="{$key}" required>
    <ul class="form-list">
        <li>
            <label for="name" class="required">{l s='Name' mod='checkoutcom'}</label>
            <input type="text" class="form-control input-text cvv required-entry validate-cc-cvn" id="checkoutcom-bancontact-name" name="name" value="" />
        </li>
    </ul>
</form>
{literal}
<script type="text/javascript">
    /**
     * Self executable
     */
    (function($form){

    	const $bancontact = document.getElementById('checkoutcom-bancontact-name');
        var submitted = false; // Prevent multiple submit

        /**
	     * Add form validation.
	     *
	     * @param      {Event}  e
	     */
        $form.onsubmit = function(e) {
          e.preventDefault();
          if($bancontact.value && !submitted) {
            submitted = true;
            $form.submit();
          }

        };

    })(document.getElementById('checkoutcom-bancontact-form'));
</script>
{/literal}