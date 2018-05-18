{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Go back to the Checkout.com' mod='checkoutapipayment'}">{l s='Checkout' mod='checkoutapipayment'}</a>
    <span class="navigation-pipe">{$navigationPipe}</span>{l s='Checkout.com payment' mod='checkoutapipayment'}
{/capture}

<h2>{l s='Order summary' mod='checkoutapipayment'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='Checkout.com credit card payment' mod='checkoutapipayment'}</h3>

<form name="checkoutapipayment_form" id="checkoutapipayment_form" action="{$link->getModuleLink('checkoutapipayment', 'validation', [], true)|escape:'html'}" method="post">
    <div class="box cheque-box">
    {include file="../frontend/hookpayment/methods/$methodType.tpl"}
    </div>

    <p id="cart_navigation" class="cart_navigation clearfix">
        <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true)}?step=3">
            <i class="icon-chevron-left"></i>Other payment methods
        </a>
        <button class="button btn btn-default button-medium" type="submit">
            <span>I confirm my order<i class="icon-chevron-right right"></i></span>
        </button>
    </p>
</form>