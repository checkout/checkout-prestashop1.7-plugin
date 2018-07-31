{extends 'customer/page.tpl'}

{block name='page_title'}
  {l s='My Saved Card' mod='checkoutapipayment'}
{/block}

{block name='page_content'}
<div class="content" style="box-shadow: 2px 2px 8px 0 rgba(0,0,0,.2);background: #fff;padding: 1rem;font-size: .875rem;color: #7a7a7a;">
	<form name="checkoutapipayment_form" id="checkoutapipayment_form" action="{$link->getModuleLink('checkoutapipayment', 'customer', [], true)|escape:'html'}" method="post">
		<ul class="payment_methods">
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

				    <div class="out">
					    <li>  
					        <input id="{$entity_id}" class="checkoutapipayment-saved-card" type="checkbox" name="checkoutapipayment-saved-card[]" value="{$entity_id}"/>
					        <label for="{$entity_id}" style="padding-left: 25px;">xxxx-{$card_number}-{$card_type}</label>  
					    </li>
					</div>
				{/foreach}
				<button class="save-card-pay-button" type="button" >Remove Card</button>
			{/if}
		</ul>
	</form>

	<script type="text/javascript">
		var submitButton = document.getElementsByClassName('save-card-pay-button')[0];
	        submitButton.onclick = function(){
	               document.getElementById('checkoutapipayment_form').submit();
	        };
	</script>

</div>

{/block}