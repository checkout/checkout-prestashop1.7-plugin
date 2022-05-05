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

{extends 'customer/page.tpl'}

{block name='page_title'}
    {l s='My Saved Card' mod='checkoutcom'}
{/block}

{block name='page_content'}
    <div class="content" style="box-shadow: 2px 2px 8px 0 rgba(0,0,0,.2);background: #fff;padding: 1rem;font-size: .875rem;color: #7a7a7a;">
        <form name="checkoutcom_form" id="checkoutcom_form" action="{$link->getModuleLink('checkoutcom', 'customer', [], true)|escape:'htmlall':'UTF-8'}" method="post">
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

                        {/foreach}

                        <div class="out">
                            <li>
                                <input id="{$entity_id|escape:'htmlall':'UTF-8'}" type="checkbox" name="checkoutcom-saved-card[]" value="{$entity_id|escape:'htmlall':'UTF-8'}"/>
                                <label for="{$entity_id|escape:'htmlall':'UTF-8'}" style="padding-left: 15px;">xxxx-{$last_four|escape:'htmlall':'UTF-8'}-{$card_scheme|escape:'htmlall':'UTF-8'}</label>
                            </li>
                        </div>
                    {/foreach}
                    <button class="checkoutcom-remove-btn" type="button">{l s='Remove Card' mod='checkoutcom'}</button>
                {else}
                    <div class="out">
                        {l s='You do not have any saved card' mod='checkoutcom'}
                    </div>
                {/if}
            </ul>
        </form>

        <script type="text/javascript">
            var submitButton = document.getElementsByClassName('checkoutcom-remove-btn')[0];
            submitButton.onclick = function(){
                document.getElementById('checkoutcom_form').submit();
            };
        </script>

    </div>

{/block}
