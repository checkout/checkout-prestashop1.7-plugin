<form name="{$module}" id="{$module}-{$key}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">
    <input id="{$module}-{$key}-source" type="hidden" name="source" value="{$key}" required>
    <div id="{$module}-{$key}-div">
        <ul class="form-list" >
            <li>
                <label for="name" class="required">{l s='International Bank Account Number (IBAN)' mod='checkoutcom'}</label>
                <input type="text" id="iban" name="iban" placeholder="" class="form-control input-text cvv required-entry validate-cc-cvn" required>
            </li>
            <li>
                <label for="name" class="required">{l s='Bank Identifier Code (BIC)' mod='checkoutcom'}</label>
                <input type="text" id="bic" name="bic" placeholder="" class="form-control input-text cvv required-entry validate-cc-cvn" required>
            </li>
        </ul>
        <input type="button" id="{$module}-{$key}-generator" name="mandate" placeholder="" value="{l s='GENERATE MANDATE' mod='checkoutcom'}" class="btn btn-primary center-block" style="margin-bottom: 14px;">
    </div>
    <div id="{$module}-{$key}-frame" data-url="{$link->getModuleLink($module, 'sepa', [], true)|escape:'html'}" data-module="{$module}"></div>
</form>
{literal}
<script type="text/javascript">

    const $sForm = document.getElementById('checkoutcom-sepa-form');
    const $button = document.getElementById('checkoutcom-sepa-generator');

    $button.onclick = function(e) {
        window.loadMandate(e);
    };

    $sForm.onsubmit = function(e) {
      e.preventDefault();

    console.log('sepa aqui');

    };
</script>
{/literal}

