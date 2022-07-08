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

 <form name="{$module|escape:'htmlall':'UTF-8'}" id="{$module|escape:'htmlall':'UTF-8'}-card-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'htmlall':'UTF-8'}" method="POST">

    <input id="{$module|escape:'htmlall':'UTF-8'}-card-source" type="hidden" name="source" value="card" required>
    <input id="{$module|escape:'htmlall':'UTF-8'}-card-token" type="hidden" name="token" value="" required>
    <input id="{$module|escape:'htmlall':'UTF-8'}-card-bin" type="hidden" name="bin" value="">

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
                                <input  class="{$module|escape:'htmlall':'UTF-8'}-saved-card-mada" type="radio" name="{$module|escape:'htmlall':'UTF-8'}-saved-card" value="{$entity_id|escape:'htmlall':'UTF-8'}"/>
                                <img class="card-logo" src="{$img_dir|escape:'htmlall':'UTF-8'}{$card_scheme|lower|escape:'htmlall':'UTF-8'}.svg"> ●●●● {$last_four|escape:'htmlall':'UTF-8'}
                            </label>
                        </li>

                    {else}
                        <li>
                            <label>
                                <input  class="{$module|escape:'htmlall':'UTF-8'}-saved-card" type="radio" name="{$module|escape:'htmlall':'UTF-8'}-saved-card" value="{$entity_id|escape:'htmlall':'UTF-8'}"/>
                                <img class="card-logo" src="{$img_dir|escape:'htmlall':'UTF-8'}{$card_scheme|lower|escape:'htmlall':'UTF-8'}.svg"> ●●●● {$last_four|escape:'htmlall':'UTF-8'}
                            </label>
                        </li>
                    {/if}

                {/foreach}

                <li>
                    <label>
                        <input  class= "{$module|escape:'htmlall':'UTF-8'}-new-card" type="radio" name="{$module|escape:'htmlall':'UTF-8'}-saved-card"  value="new_card"/>
                        <img class="card-logo" src="{$img_dir|escape:'htmlall':'UTF-8'}addcard.svg"> {l s='New card' mod='checkoutcom' }
                    </label>
                </li>

            {/if}
        </ul>
    {/if}

    {if $isSingleIframe}
        {*frames will be added here*}
        <div id="{$module|escape:'htmlall':'UTF-8'}-card-frame" class="card-frame" data-key="{$CHECKOUTCOM_PUBLIC_KEY|escape:'htmlall':'UTF-8'}" data-billing="{$billingId|escape:'htmlall':'UTF-8'}" data-debug="{$debug|escape:'htmlall':'UTF-8'}" data-lang="{$lang|escape:'htmlall':'UTF-8'}" data-module="{$module|escape:'htmlall':'UTF-8'}" data-saveCard="{$save_card_option|escape:'htmlall':'UTF-8'}"  ></div>
    {else}
        <div id="{$module|escape:'htmlall':'UTF-8'}-multi-frame" class="multi-frame" data-key="{$CHECKOUTCOM_PUBLIC_KEY|escape:'htmlall':'UTF-8'}" data-billing="{$billingId|escape:'htmlall':'UTF-8'}" data-debug="{$debug|escape:'htmlall':'UTF-8'}" data-lang="{$lang|escape:'htmlall':'UTF-8'}" data-module="{$module|escape:'htmlall':'UTF-8'}" data-saveCard="{$save_card_option|escape:'htmlall':'UTF-8'}" data-imagedir="{$img_dir|escape:'htmlall':'UTF-8'}">
            {*frames will be added here*}
            <div class="input-container card-number">
                <div class="icon-container">
                    <img id="icon-card-number"
                        src="{$img_dir|escape:'htmlall':'UTF-8'}card-icons/card.svg"
                        alt="PAN" />
                </div>
                <div class="card-number-frame"></div>
                <div class="icon-container payment-method">
                    <img id="logo-payment-method" />
                </div>
                <div class="icon-container">
                    <img id="icon-card-number-error"
                        src="{$img_dir|escape:'htmlall':'UTF-8'}card-icons/error.svg">
                </div>
            </div>

            <div class="date-and-code">
                <div>
                    <div class="input-container expiry-date">
                        <div class="icon-container">
                            <img id="icon-expiry-date"
                                src="{$img_dir|escape:'htmlall':'UTF-8'}card-icons/exp-date.svg"
                                alt="Expiry date" />
                        </div>
                        <div class="expiry-date-frame"></div>
                        <div class="icon-container">
                            <img id="icon-expiry-date-error"
                                src="{$img_dir|escape:'htmlall':'UTF-8'}card-icons/error.svg" />
                        </div>
                    </div>
                </div>

                <div>
                    <div class="input-container cvv">
                        <div class="icon-container">
                            <img id="icon-cvv"
                                src="{$img_dir|escape:'htmlall':'UTF-8'}card-icons/cvv.svg"
                                alt="CVV" />
                        </div>
                        <div class="cvv-frame"></div>
                        <div class="icon-container">
                            <img id="icon-cvv-error"
                                src="{$img_dir|escape:'htmlall':'UTF-8'}card-icons/error.svg" />
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
            <label for="{$module|escape:'htmlall':'UTF-8'}-cko-cvv">
                <em>* </em>{l s='Card Verification Number' mod='checkoutcom'}
                <input id="{$module|escape:'htmlall':'UTF-8'}-cko-cvv" type="number" name="cko-cvv" min="3" max="4" autocomplete="off"/>
            </label>
        </div>
    {/if}
</form>
<script type="text/javascript" src="{$js_dir|escape:'htmlall':'UTF-8'}card.js"></script>
<script type="text/javascript" async src="https://cdn.checkout.com/js/framesv2.min.js" onload="CheckoutcomFramesPay(document.getElementById('checkoutcom-card-form'));"></script>
<br>