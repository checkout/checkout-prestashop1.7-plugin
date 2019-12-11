<form name="{$module}" id="{$module}-{$key}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">
    <input id="{$module}-{$key}-source" type="hidden" name="source" value="{$key}" required>
    <ul class="form-list" >
        <li>
            <label for="cpf" class="required">{l s='Cadastro de Pessoas Físicas' mod='checkoutcom'} (CPF)</label>
            <input type="text" class="form-control input-text cvv required-entry validate-cc-cvn" id="{$module}-{$key}-cpf" name="cpf" value="" />
        </li>
        <li>
            <label for="birthDate" class="required">{l s='Birthdate' mod='checkoutcom'}</label>
            <input type="date" class="form-control input-text cvv required-entry validate-cc-cvn" id="{$module}-{$key}-birthdate" name="birthDate" value="" />
        </li>
    </ul>
</form>
{literal}
<script type="text/javascript">
    /**
     * Self executable
     */
    (function($form){

        const $cpf = document.getElementById('checkoutcom-boleto-cpf');
        const $birthdate = document.getElementById('checkoutcom-boleto-birthdate');
        var submitted = false; // Prevent multiple submit

        /**
         * Add form validation.
         *
         * @param      {Event}  e
         */
        $form.onsubmit = function(e) {
          e.preventDefault();
          if($cpf.value && $birthdate.value && !submitted) {
            submitted = true;
            $form.submit();
          }

        };

    })(document.getElementById('checkoutcom-boleto-form'));
</script>
{/literal}