<p class="payment_module" >
    {if $hasError == 1}
        <p class="error">
            {if !empty($smarty.get.message)}
                {l s='Error detail from Checkout.com : ' mod='checkoutapipayment'}
                {$smarty.get.message|htmlentities}
            {else}
                {l s='Error, please verify the card details' mod='checkoutapipayment'}
            {/if}

        </p>
    {/if}

    <div class="stripe-payment-errors">{if isset($stripe_error)}{$stripe_error|escape:htmlall:'UTF-8'}{/if}</div>

    {if $integrationType == 'js'}
            <div style="" class="checkoutapi-info">
                <a id="click_checkoutprestashop" href="javascript:void(0)" title="{l s='Pay with Checkout.com' mod='checkoutapipayment'}" style="">
                    <img src="https://www.checkout.com/signature.jpg" alt="Pay through Checkout.com" border="0" align="absmiddle" class="img-logo"/>

                    <span class="span-desc">{l s='' mod='checkoutapipayment'}</span>
                    {if isset($template) }
                         {include file="../hookpayment/js/$template"}
                    {/if}

                </a>
            </div>
        {elseif $integrationType == 'hosted'}
            <div style="" class="checkoutapi-info">
                <a id="click_checkoutprestashop" href="javascript:void(0)" title="{l s='Pay with Checkout.com' mod='checkoutapipayment'}" style="">
                    <img src="https://www.checkout.com/signature.jpg" alt="Pay through Checkout.com" border="0" align="absmiddle" class="img-logo"/>

                    <span class="span-desc">{l s='' mod='checkoutapipayment'}</span>
                    {if isset($template) }
                         {include file="../hookpayment/js/$template"}
                    {/if}

                </a>
                <form id="payment-form" action="{$hppUrl}" method="POST">
                    <input type="hidden" name="publicKey" value="{$publicKey}"/>
                    <input type="hidden" name="paymentToken" value="{$paymentToken}"/>
                    <input type="hidden" name="customerEmail" value="{$mailAddress}"/>
                    <input type="hidden" name="value" value="{$amount}"/>
                    <input type="hidden" name="currency" value="{$currencyIso}"/>
                    <input type="hidden" name="cardFormMode" value="cardTokenisation"/></input>
                    <input type="hidden" name="paymentMode" value="{$paymentMode}"/>
                    <input type="hidden" name="redirectUrl" value="{$returnUrl}"/>
                    <input type="hidden" name="cancelUrl" value="{$cancelUrl}"/>
                    <input type="hidden" name="themeColor" value="{$themecolor}"/></input>
                    <input type="hidden" name="title" value="{$title}"/></input>
                    <input type="hidden" name="useCurrencyCode" value="{$usecurrencycode}"/></input>
                    <input type="hidden" name="logoUrl" value="{$logourl}"/></input>
                    <input type="hidden" name="iconColor" value="{$iconcolor}"/></input>
                    <input type="hidden" name="localisation" value="{$localisation}"/></input>
                    <input type="hidden" name="theme" value="standard">
                </form>

                <script>
                    function submitForm(){
                        $('#payment-form').submit();
                    }
                </script>

            </div>

        {else}
            <div style="" class="checkoutapi-info">
                <a id="click_checkoutprestashop" href="{$link->getModuleLink('checkoutapipayment', 'payment', [], true)|escape:'html'}" title="{l s='Pay with Checkout.com' mod='checkoutapipayment'}" style="">
                    <img src="https://www.checkout.com/signature.jpg" alt="Pay through Checkout.com" border="0" align="absmiddle" class="img-logo"/>

                    <span class="span-desc">{l s='' mod='checkoutapipayment'}</span>
                    {if isset($template) }
                         {include file="../hookpayment/js/$template"}
                    {/if}

                </a>
            </div>
    {/if}
</p>