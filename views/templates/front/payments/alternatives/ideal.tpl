<form name="{$module}" id="{$module}-{$key}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">
    <input id="{$module}-{$key}-source" type="hidden" name="source" value="{$key}" required>
    <ul class="form-list" >
        <li>
            <label for="name" class="required">{l s='Bank Identifier Code (BIC)' mod='checkoutcom'}</label>
            <input type="text" id="bic" name="bic" placeholder="" class="form-control input-text cvv required-entry validate-cc-cvn" required>
        </li>
    </ul>
</form>