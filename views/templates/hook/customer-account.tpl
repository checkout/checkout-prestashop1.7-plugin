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

<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="identity-link" href="{$link->getModuleLink('checkoutcom', 'customer', [], true)|escape:'html'}">
	<span class="link-item">
	  <i class="material-icons">credit_card</i>
        {l s='My Saved Card' mod='checkoutcom'}
	</span>
</a>