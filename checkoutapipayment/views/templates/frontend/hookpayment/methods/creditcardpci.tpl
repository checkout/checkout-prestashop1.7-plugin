<ul class="payment_methods">
{if $isGuest == 0 && $saveCard == 'yes'}
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

<div class="checkout-non-pci-new-card-row">
<ul class="form-list" id="payment_form_creditcardpic" >
    <li>
        <label for="creditcardpic_cc_owner" class="required">Name on Card</label>
            <input type="text" title="Name on Card" class="input-text required-entry form-control" id="creditcardpic_cc_owner" name="cc_owner" value="{$cc_owner}" />
    </li>
    <li>
        <label for="creditcardpic_cc_type" class="required">Credit Card Type</label>
            <select id="creditcardpic_cc_type" name="cc_type" class="required-entry validate-cc-type-select form-control">
                <option value="">--Please Select--</option>
                {foreach from=$cards  item=card }
                    {if $card.selected ==1}
                        <option value="{$card.id}"  {if $card.id==$ccType}: ?> selected="selected"{/if}>{$card.label}</option>
                    {/if}
                {/foreach}
            </select>
    </li>
    <li>
        <label for="creditcardpic_cc_number" class="required">Credit Card Number</label>
            <input type="text" id="creditcardpic_cc_number" name="cc_number" title="Credit Card Number" class=" form-control input-text validate-cc-number validate-cc-type" value="" />
    </li>
    <li id="creditcardpic_cc_type_exp_div">
        <label for="creditcardpic_expiration" class="required">Expiration Date</label>
            <div class="v-fix">
                <select id="creditcardpic_expiration" name="cc_exp_month" class="form-control month validate-cc-exp required-entry">
                    <option value="">--Month--</option>
                    {foreach from=$months key=i item=month }
                        <option value="{$i}" {if $i==$cc_exp_month} selected="selected" {/if} >{$i}-{$month}</option>
                    {/foreach}
                </select>
            </div>
            <div class="v-fix">
                <select id="creditcardpic_expiration_yr" name="cc_exp_year" class="form-control year required-entry">.
                    <option value="">--Year--</option>
                    {foreach from=$years key=i item=year }
                        <option value="{$year}" {if $year== $cc_exp_year} selected="selected"{/if}>{$year}</option>
                    {/foreach}
                </select>
            </div>
    </li>
    <li id="creditcardpic_cc_type_cvv_div">
        <label for="creditcardpic_cc_cid" class="required">Card Verification Number</label>
                <input type="text" title="Card Verification Number" class="form-control input-text cvv required-entry validate-cc-cvn" id="creditcardpic_cc_cid" name="cc_cid" value="" />
        <a href="#" class="cvv-what-is-this">What is this?</a>
        <div class="tool-tip-content">
            <img src="{$module_dir}skin/img/card/cvv.gif" alt="Card Verification Number Visual Reference" title="Card Verification Number Visual Reference">
        </div>
    </li>
    <li>
        <input type="hidden" name='isSavedCard' id="isSavedCard" value="">
    </li>


    <?php endif; ?>
</ul>

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
            checkoutHideNewNoPciCard();
        });

        jQuery('.checkoutapipayment-new-card').on("click", function() {
            checkoutShowNewNoPciCard();
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

