<ul class="payment_methods">
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
                <input id="checkoutapipayment-saved-card" class="checkoutapipayment-saved-card" type="radio" name="checkoutapipayment-saved-card" value="{$entity_id}"/>xxxx-{$card_number}-{$card_type}</label>   
            </li>
        {/foreach}
             <li>
                <label>
                <input id="checkoutapipayment-new-card" class= "checkoutapipayment-new-card" type="radio" name="checkoutapipayment-new-card"  value="new_card"/>Use New card</label>
            </li>
    {/if}
{/if}
</ul>

<div class="checkout-non-pci-new-card-row">
    <script async src="{$hppUrl}"></script>
    <script type="text/javascript">
        var style = {$customCss}

        window.CKOConfig = {
            publicKey: '{$publicKey}',
            theme: '{$theme}',
            style: style,
            localisation:'{$localisation}',
            cardTokenised: function(event) {
                if (document.getElementById('cko-card-token').value.length === 0) {
                    document.getElementById('cko-card-token').value = event.data.cardToken;

                    if(jQuery('#uniform-checkoutapipayment-new-card span').hasClass('checked')){
                        document.getElementById('new-card').value = 1;
                    } 

                    document.getElementById('checkoutapipayment_form').submit();
                }
            },
            frameActivated: function(){
                    document.getElementById('cko-iframe-id').style.position = "relative";
                    $('.cko-md-overlay').remove();
            },
            cardValidationChanged: function (event) { console.log('cardValidationChanged');
                document.getElementsByClassName('button btn btn-default button-medium')[1].disabled = !Frames.isCardValid();
            },
            ready: function(){
                var submitButton = document.getElementsByClassName('button btn btn-default button-medium')[1];
                submitButton.disabled = true;
               
                submitButton.addEventListener("click", function () {
                    if (Frames.isCardValid()) Frames.submitCard();
                });
            }
        };
    </script>
    <form id="checkoutapipayment_form">
        <input type="hidden" name="cko-card-token" id="cko-card-token" value="">
        <input type="hidden" name='new-card' id="new-card" value="">
    </form>
</div>

{if $saveCard == 'yes' }
    <div class="save-card-checkbox"  style="display:none">
        <div class="out">
            <input type="checkbox" name="save-card-checkbox" id="save-card-checkbox" value="1"></input>
            <label for="save-card-checkbox" style="padding-left: 20px;">Save card for future payment</label>
        </div>
    </div>
{/if}
</div>

{literal} 
    <script type="application/javascript"> 
        checkoutHideNewNoPciCard();
        function checkoutHideNewNoPciCard() {
            jQuery('#uniform-checkoutapipayment-new-card span').removeClass('checked');
            jQuery('.checkout-non-pci-new-card-row').hide();
        }

        function checkoutShowNewNoPciCard() {
            jQuery('#uniform-checkoutapipayment-saved-card span').removeClass('checked');
            jQuery('.checkout-non-pci-new-card-row').show();
            jQuery('.save-card-checkbox').show();
        } 

        jQuery('.checkoutapipayment-saved-card').on("click", function() {
            jQuery('.save-card-checkbox').hide();
            checkoutHideNewNoPciCard();
            var submitButton = document.getElementsByClassName('button btn btn-default button-medium')[1];
            submitButton.disabled = false;

            submitButton.onclick = function(){
                document.getElementById('checkoutapipayment_form').submit();
            };

        });

        jQuery('.checkoutapipayment-new-card').on("click", function() {          
            checkoutShowNewNoPciCard();
            var submitButton = document.getElementsByClassName('button btn btn-default button-medium')[1];
            submitButton.disabled = false;  

            submitButton.onclick = function(){
                    if (Frames.isCardValid()) Frames.submitCard();
            };
        });

    </script>
{/literal}

{if $isGuest == 1 }
    <script type="text/javascript">
        jQuery('.checkout-non-pci-new-card-row').show();
    </script>
{/if}

{if $isGuest eq 0 and $saveCard eq 'no' }
        <script type="text/javascript"> 
            jQuery('.checkout-non-pci-new-card-row').show();
            jQuery('.save-card-checkbox').hide();
        </script>

    {elseif $isGuest eq 0 and $saveCard eq 'yes'}
        {if empty($cardLists)}
            <script type="text/javascript"> 
                jQuery('.checkout-non-pci-new-card-row').show();
                jQuery('.save-card-checkbox').show();
            </script>
            {/if}
{/if}