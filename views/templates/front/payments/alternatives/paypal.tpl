<form name="{$module}" id="{$module}-{$key}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">
    <input id="{$module}-{$key}-source" type="hidden" name="source" value="{$key}" required>
    {$context = Context::getContext()}
    <input type="hidden" name="cart_id" value="{$context->cart->id}" required>
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

    })(document.getElementById('checkoutcom-paypal-form'));
</script>
{/literal}