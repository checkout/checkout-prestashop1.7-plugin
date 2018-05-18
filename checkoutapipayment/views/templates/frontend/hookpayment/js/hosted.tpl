<form name="checkoutapipayment_form" id="checkoutapipayment_form" action="{$link->getModuleLink('checkoutapipayment', 'validation', [], true)|escape:'html'}" method="post">
    <div class="payment-select-txt">{*{l s='Select your payment option' mod='checkoutapipayment'}*}</div>
        <ul class="payment_methods" style="padding-top: 10px;">
            {if $isGuest == 0 && $saveCard== 'yes'}
                {if !empty($cardLists)}
                    {foreach name=outer item=card_number from=$cardLists}
                      {foreach key=key item=item from=$card_number}
                        {if $key == 'card_number'}
                            {assign var="card_number" value="{$item}"}
                        {/if}

                        {if $key == 'card_type'}
                            {assign var="card_type" value="{$item}"}
                        {/if}

                        {if $key == 'entity_id'}
                            {assign var="entity_id" value="{$item}"}
                        {/if}
                    {/foreach}
                    <li>  
                        <label>
                        <input id="checkoutapipayment-saved-card" class="checkoutapipayment-saved-card" type="radio" name="checkoutapipayment-saved-card" value="{$entity_id}">xxxx-{$card_number}-{$card_type}</label>   
                    </li>
                    {/foreach}

                     <li>
                        <label>
                        <input id="checkoutapipayment-new-card" class= "checkoutapipayment-new-card" type="radio" name="checkoutapipayment-new-card"  value="new_card">Use New card</label>
                    </li>
                {/if}
            {/if}
        </ul>
    <div class="widget-container"  style="height: 50px;"></div>
    <input type="hidden" name="cko_cc_paymenToken" id="cko-cc-paymenToken" value="">
    <input type="hidden" name="cko-card-token" id="cko-card-token" value="">

    {if $saveCard == 'yes' }
        <div class="save-card-checkbox"  style="display:none">
            <div class="out">
                <input type="checkbox" name="save-card-checkbox" id="save-card-checkbox" value="1"></input>
                <label for="save-card-checkbox" style="padding-left: 20px;" >Save card for future payment</label>
            </div>
        </div>
    {/if}

    <div class="checkout-non-pci-new-card-row">
        {if $paymentToken && $success }
            <script type="text/javascript">
                jQuery(function(){
                    jQuery('#click_checkoutapipayment').attr('href','javascript:void(0)');
                });

                var reload = false;
                window.checkoutIntegrationCurrentConfig= {
                    debugMode: false,
                    localisation: '{$localisation}',
                    renderMode: '{$renderMode}',
                    namespace: 'CheckoutIntegration',
                    publicKey: '{$publicKey}',
                    paymentToken: "{$paymentToken}",
                    value: '{$amount}',
                    currency: '{$currencyIso}',
                    customerEmail: '{$mailAddress}',
                    customerName: '{$name}',
                    paymentMode: '{$paymentMode}',
                    title: '{$title}',
                    showMobileIcons : true,
                    forceMobileRedirect: false,
                    useCurrencyCode: '{$usecurrencycode}',
                    cardFormMode: 'cardTokenisation',
                    widgetContainerSelector: '.widget-container',
                    enableIframePreloading:false,
                    styling: {
                        themeColor: '{$themecolor}',
                        buttonColor: '{$buttoncolor}',
                        logoUrl: '{$logourl}',
                        iconColor: '{$iconcolor}'
                    },

                    paymentTokenExpired: function(){
                        window.location.reload();
                        reload = true;
                    },

                    lightboxDeactivated: function() {
                        if(reload) {
                            window.location.reload();
                        }
                    },

                    cardTokenised: function(event){
                        if(document.getElementById('cko-card-token').value.length === 0 || document.getElementById('cko-card-token').value !== event.data.cardToken) {
                            document.getElementById('cko-card-token').value = event.data.cardToken;

                            if(jQuery('#uniform-checkoutapipayment-new-card span').hasClass('checked')){
                                document.getElementById('new-card').value = 1;
                            } 
                            
                            document.getElementById('checkoutapipayment_form').submit();
                        }
                        
                    },

                    cardTokenisationFailed: function() {
                        reload = true;
                    }
                };

                window.checkoutIntegrationIsReady = window.checkoutIntegrationIsReady || false;
                if (!window.checkoutIntegrationIsReady) {

                    window.CKOConfig = {
                        ready: function () {

                            if (window.checkoutIntegrationIsReady) {
                                return false;
                            }

                            if (typeof CKOAPIJS == 'undefined') {
                                return false;
                            }
                            CKOAPIJS.render(window.checkoutIntegrationCurrentConfig);
                            window.checkoutIntegrationIsReady = true;
                        }
                    };

                    var mode = '{$mode}';
                    if(mode == 'sandbox'){
                        src = 'https://cdn.checkout.com/sandbox/js/checkout.js';
                    } else {
                        src = 'https://cdn.checkout.com/js/checkout.js';
                    }

                    var script = document.createElement('script');
                    script.src = src;
                    script.async = true;
                    script.setAttribute('data-namespace', 'CKOAPIJS');
                    document.head.appendChild(script);
                } else {
                    CKOAPIJS.render(checkoutIntegrationCurrentConfig);
                }

            </script>
    </div>
    {else}
        {$message}
        {l s='Event id' mod='checkoutapipayment'}: {$eventId}
    {/if}
</form>

    <div class="save-card-pay" align="right">
        <button class="save-card-pay-button" type="button" style="margin-right: 20px; display: none">PAY NOW</button>
    </div>
</div>

{literal} 
    <script type="application/javascript"> 
        checkoutHideNewNoPciCard();

        function checkoutHideNewNoPciCard() {
            jQuery('#uniform-checkoutapipayment-new-card span').removeClass('checked');
            jQuery('.checkout-non-pci-new-card-row').hide();
            jQuery('.widget-container').hide();
            jQuery('.save-card-checkbox').hide();
            var submitButton = document.getElementsByClassName('save-card-pay-button')[0];

            submitButton.onclick = function(){
                   document.getElementById('checkoutapipayment_form').submit();
            };
        }

        function checkoutShowNewNoPciCard() {
            jQuery('#uniform-checkoutapipayment-saved-card span').removeClass('checked');
            jQuery('.checkout-non-pci-new-card-row').show();
            jQuery('.widget-container').show();
            jQuery('.save-card-pay-button').show();
            jQuery('.save-card-checkbox').show();

            var submitButton = document.getElementsByClassName('save-card-pay-button')[0];

            submitButton.onclick = function(){

                if(jQuery('#uniform-save-card-checkbox span').hasClass('checked')){
                    document.cookie = "saveCardCheckbox=1";
                }

                document.getElementById('payment-form').submit();
            };
        } 

        jQuery('.checkoutapipayment-saved-card').on("click", function() {
            jQuery('.save-card-pay-button').show();
            checkoutHideNewNoPciCard();
        });

        jQuery('.checkoutapipayment-new-card').on("click", function() {      
            checkoutShowNewNoPciCard();
        });

    </script>
{/literal}

{if $isGuest == 1 || $saveCard == 'no'}
    <script type="text/javascript">
        jQuery('.checkout-non-pci-new-card-row').show();
        jQuery('.widget-container').show();
        jQuery('.save-card-pay-button').show();
        
        var submitButton = document.getElementsByClassName('save-card-pay-button')[0];
        submitButton.onclick = function(){
               document.getElementById('payment-form').submit();
        };
    </script>
{/if}

{if $isGuest == 1 }
    <script type="text/javascript">
        jQuery('.checkout-non-pci-new-card-row').show();
        jQuery('.widget-container').show();
        jQuery('.save-card-pay-button').show();
        
        var submitButton = document.getElementsByClassName('save-card-pay-button')[0];
        submitButton.onclick = function(){
               document.getElementById('payment-form').submit();
        };
    </script>
{/if}

{if $isGuest eq 0 and $saveCard eq 'no' }
        <script type="text/javascript">
            jQuery('.widget-container').show();
            jQuery('.save-card-pay-button').show();
            
            var submitButton = document.getElementsByClassName('save-card-pay-button')[0];
            submitButton.onclick = function(){
                   document.getElementById('payment-form').submit();
            };
        </script>

    {elseif $isGuest eq 0 and $saveCard eq 'yes'}
        {if empty($cardLists)}
            <script type="text/javascript">
                jQuery('.widget-container').show();
                jQuery('.save-card-pay-button').show();
                jQuery('.save-card-checkbox').show();
                
                var submitButton = document.getElementsByClassName('save-card-pay-button')[0];
                submitButton.onclick = function(){
                       document.getElementById('payment-form').submit();
                };
            </script>
        {/if}
{/if}

{if $buttoncolor}
    {literal}
        <script type="text/javascript"> 
            var buttonColor = '{/literal}{$buttoncolor}{literal}';
            jQuery('.save-card-pay-button').css("background-color",buttonColor);
        </script>
    {/literal}
{/if}