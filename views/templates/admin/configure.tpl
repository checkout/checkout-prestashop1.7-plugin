{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
	<li class="active"><a href="#template_1" role="tab" data-toggle="tab">{l s='Configuration' mod='checkoutcom'}</a></li>
	<li><a href="#template_2" role="tab" data-toggle="tab">{l s='Card Payments' mod='checkoutcom'}</a></li>
	<li><a href="#template_3" role="tab" data-toggle="tab">{l s='Alternative Payments' mod='checkoutcom'}</a></li>
	<li><a href="#template_4" role="tab" data-toggle="tab">{l s='Google Pay' mod='checkoutcom'}</a></li>
	<li><a href="#template_5" role="tab" data-toggle="tab">{l s='Apple Pay' mod='checkoutcom'}</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
	<div class="tab-pane active" id="template_1">{$config_main}</div>
	<div class="tab-pane" id="template_2">{$config_card}</div>
	<div class="tab-pane" id="template_3">{$config_alternatives}</div>
	<div class="tab-pane" id="template_4">{$config_google}</div>
	<div class="tab-pane" id="template_5">{$config_apple}</div>
</div>
