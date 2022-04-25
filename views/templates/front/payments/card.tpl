<form name="{$module}" id="{$module}-card-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">

    <input id="{$module}-card-source" type="hidden" name="source" value="card" required>
    <input id="{$module}-card-token" type="hidden" name="token" value="" required>
    <input id="{$module}-card-bin" type="hidden" name="bin" value="">

    {if $save_card_option == '1' and $is_guest == 0  }

        <ul class="payment_methods">
            {if !empty($cardLists)}
                {foreach name=outer item=last_four from=$cardLists}
                    {foreach key=key item=item from=$last_four}
                        {if $key == 'last_four'}
                            {assign var="last_four" value="{$item}"}
                        {/if}

                        {if $key == 'card_scheme'}
                            {assign var="card_scheme" value="{$item}"}
                        {/if}

                        {if $key == 'entity_id'}
                            {assign var="entity_id" value="{$item}"}
                        {/if}

                        {if $key == 'is_mada'}
                            {assign var ="is_mada" value="{$item}"}
                        {/if}

                    {/foreach}

                    {if $is_mada}
                        <li>
                            <label>
                                <input  class="{$module}-saved-card-mada" type="radio" name="{$module}-saved-card" value="{$entity_id}"/>
                                <img class="card-logo" src="{$img_dir}{strtoLower($card_scheme)}.svg"> ●●●● {$last_four}
                            </label>
                        </li>

                    {else}
                        <li>
                            <label>
                                <input  class="{$module}-saved-card" type="radio" name="{$module}-saved-card" value="{$entity_id}"/>
                                <img class="card-logo" src="{$img_dir}{strtoLower($card_scheme)}.svg"> ●●●● {$last_four}
                            </label>
                        </li>
                    {/if}

                {/foreach}

                <li>
                    <label>
                        <input  class= "{$module}-new-card" type="radio" name="{$module}-saved-card"  value="new_card"/>
                        <img class="card-logo" src="{$img_dir}addcard.svg"> {l s='New card' mod='checkoutcom' }
                    </label>
                </li>

            {/if}
        </ul>
    {/if}

    {if $isSingleIframe}
        {*frames will be added here*}
        <div id="{$module}-card-frame" class="card-frame" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-billing="{$billingId}" data-debug="{$debug}" data-lang="{$lang}" data-module="{$module}" data-saveCard="{$save_card_option}"  ></div>
    {else}
        <div id="{$module}-multi-frame" class="multi-frame" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-billing="{$billingId}" data-debug="{$debug}" data-lang="{$lang}" data-module="{$module}" data-saveCard="{$save_card_option}" data-imagedir="{$img_dir}">
            {*frames will be added here*}
            <div class="input-container card-number">
                <div class="icon-container">
                    <img id="icon-card-number"
                        src="{$img_dir}card-icons/card.svg"
                        alt="PAN" />
                </div>
                <div class="card-number-frame"></div>
                <div class="icon-container payment-method">
                    <img id="logo-payment-method" />
                </div>
                <div class="icon-container">
                    <img id="icon-card-number-error"
                        src="{$img_dir}card-icons/error.svg">
                </div>
            </div>

            <div class="date-and-code">
                <div>
                    <div class="input-container expiry-date">
                        <div class="icon-container">
                            <img id="icon-expiry-date"
                                src="{$img_dir}card-icons/exp-date.svg"
                                alt="Expiry date" />
                        </div>
                        <div class="expiry-date-frame"></div>
                        <div class="icon-container">
                            <img id="icon-expiry-date-error"
                                src="{$img_dir}card-icons/error.svg" />
                        </div>
                    </div>
                </div>

                <div>
                    <div class="input-container cvv">
                        <div class="icon-container">
                            <img id="icon-cvv"
                                src="{$img_dir}card-icons/cvv.svg"
                                alt="CVV" />
                        </div>
                        <div class="cvv-frame"></div>
                        <div class="icon-container">
                            <img id="icon-cvv-error"
                                src="{$img_dir}card-icons/error.svg" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    {if $save_card_option == '1' and $is_guest == 0 }
        {*saved card checkbox0*}
        <div class="save-card-check">
            <label for="save-card-checkbox" >
                <input type="checkbox" name="save-card-checkbox" id="save-card-checkbox" />
                {l s='Save card for future payment.' mod='checkoutcom'}
            </label>
        </div>

        <div class="cvvVerification">
            <label for="{$module}-cko-cvv">
                <em>* </em>{l s='Card Verification Number' mod='checkoutcom'}
                <input id="{$module}-cko-cvv" type="number" name="cko-cvv" min="3" max="4" autocomplete="off"/>
            </label>
        </div>
    {/if}
</form>
<script type="text/javascript" src="{$js_dir|escape:'htmlall':'UTF-8'}card.js"></script>
<script type="text/javascript" async src="https://cdn.checkout.com/js/framesv2.min.js" onload="CheckoutcomFramesPay(document.getElementById('checkoutcom-card-form'));"></script>
<br>