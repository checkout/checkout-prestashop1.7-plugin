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