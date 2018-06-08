<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<form name="checkoutapipayment_form" id="checkoutapipayment_form" action="{$link->getModuleLink('checkoutapipayment', 'validation', [], true)|escape:'html'}" method="post">
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
                        <input id="checkoutapipayment-new-card" class= "checkoutapipayment-new-card" type="radio" name="checkoutapipayment-new-card"  value="new_card"/>Use New card</label>
                    </li>
                {/if}
            {/if}
        </ul>
    <input type="hidden" name="cko_cc_paymenToken" id="cko-cc-paymenToken" value="">
    <input type="hidden" name="cko-card-token" id="cko-card-token" value="">
    <input type="hidden" name='new-card' id="new-card" value="">

    {if $saveCard == 'yes' }
        <div class="save-card-checkbox"  style="display:none">
            <div class="out">
                <label for="save-card-checkbox" style="padding-left: 20px;">Save card for future payment
                    <input type="checkbox" name="save-card-checkbox" id="save-card-checkbox" value="1"></input>
                </label>
            </div>
        </div>
    {/if}

    <div id="widget-container" style="height: 50px;"></div>
     <script type="text/javascript">
                
        var reload = false;
        window.checkoutIntegrationCurrentConfig= {
            debugMode: false,
            localisation: '{$localisation}',
            renderMode: 2,
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
                ready: function () { console.log('ready 2');

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
            document.getElementById('widget-container').appendChild(script);
        } else {
            CKOAPIJS.render(checkoutIntegrationCurrentConfig);
        }
     </script> 
</form>



{literal} 
    <script type="application/javascript"> 
        checkoutHideNewNoPciCard();

        function checkoutHideNewNoPciCard() {
            jQuery('.checkoutapipayment-new-card').attr("checked",false);
            jQuery('.widget-container').hide();
            jQuery('.save-card-checkbox').hide();
            jQuery('#widget-container').hide();
        }

        function checkoutShowNewNoPciCard() {
            jQuery('.checkoutapipayment-saved-card').attr("checked",false);
            jQuery('.widget-container').show();
            jQuery('.save-card-checkbox').show();
            jQuery('#widget-container').show();
        } 

        jQuery('.checkoutapipayment-saved-card').on("click", function() {
            checkoutHideNewNoPciCard();
        });

        jQuery('.checkoutapipayment-new-card').on("click", function() {
            checkoutShowNewNoPciCard();
        });

    </script>
{/literal}

{if $isGuest == 1 }
    <script type="text/javascript">
            jQuery('.widget-container').show();
    </script>
{/if}

{if $isGuest eq 0 and $saveCard eq 'no' }
        <script type="text/javascript"> 
            jQuery('#widget-container').show();
            jQuery('.save-card-checkbox').hide();
        </script>

    {elseif $isGuest eq 0 and $saveCard eq 'yes'}
        {if empty($cardLists)}
            <script type="text/javascript"> 
                jQuery('#widget-container').show();
                jQuery('.save-card-checkbox').show();
            </script>
            {/if}
    {else}
        <script type="text/javascript"> 
                jQuery('#widget-container').show();
            </script>
{/if}

{literal}
    <script type="text/javascript">
        $( document ).ready(function() {
            jQuery('input:radio[name="payment-option"]').change(function(){
                if($(this).attr('data-module-name') == 'creditcard'){
                    jQuery('button.btn.btn-primary.center-block').click(function(event){
                        if(jQuery("input:radio[name='payment-option']:checked").attr('data-module-name') == "creditcard"){
                            event.stopPropagation();
                            if(jQuery('.checkoutapipayment-saved-card').length > 0 &&
                                jQuery('.checkoutapipayment-saved-card').is(':checked')){
                                document.getElementById('checkoutapipayment_form').submit();
                            } else {
                                CKOAPIJS.open();    
                            } 
                        }
                    });
                } else {
                    return;
                }
            });
        });
    </script>
{/literal}