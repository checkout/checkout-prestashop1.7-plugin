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
*  @copyright 2007-2022 PrestaShop SA
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
	{* <li><a href="#template_5" role="tab" data-toggle="tab">{l s='Recurring Payments' mod='checkoutcom'}</a></li> *}
</ul>

<!-- Tab panes -->
<div class="tab-content">
	<div class="tab-pane active" id="template_1">
		{$config_main}
		<div class="webhook-url-container">
			<input type="hidden" name="webhook_url" value="{$webhook_url|escape:'htmlall':'UTF-8'}">
			<div class="form-group set-webhook-container">
				<label class="col-lg-3"></label>
				<div class="col-lg-9">
					<button type="submit" name="set_webhook">Set webhook</button>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">
					Webhook
				</label>
				<div class="col-lg-9">
					<span class="webhook-url">
						{$webhook_url|escape:'htmlall':'UTF-8'}
					</span>
				</div>
			</div>
		</div>
		<input type="hidden" id="CHECKOUTCOM_SECRET_KEY_ABC" value="{if 'CHECKOUTCOM_SECRET_KEY_ABC'|array_key_exists:$fields_value}{$fields_value.CHECKOUTCOM_SECRET_KEY_ABC}{/if}"/>
		<input type="hidden" id="CHECKOUTCOM_PUBLIC_KEY_ABC" value="{if 'CHECKOUTCOM_PUBLIC_KEY_ABC'|array_key_exists:$fields_value}{$fields_value.CHECKOUTCOM_PUBLIC_KEY_ABC}{/if}"/>
		<input type="hidden" id="CHECKOUTCOM_SECRET_KEY_NAS" value="{if 'CHECKOUTCOM_SECRET_KEY_NAS'|array_key_exists:$fields_value}{$fields_value.CHECKOUTCOM_SECRET_KEY_NAS}{/if}"/>
		<input type="hidden" id="CHECKOUTCOM_PUBLIC_KEY_NAS" value="{if 'CHECKOUTCOM_PUBLIC_KEY_NAS'|array_key_exists:$fields_value}{$fields_value.CHECKOUTCOM_PUBLIC_KEY_NAS}{/if}"/>
	</div>
	<div class="tab-pane" id="template_2">
		<form id="module_form_1" class="defaultForm form-horizontal" action="" method="post" enctype="multipart/form-data" novalidate>
			<input type="hidden" name="submitCheckoutComModule" value="1" />
			<div class="panel" id="fieldset_0_1">
				<div class="panel-heading"></div>
				<div class="form-wrapper">
					<div class="form-group">
						<label class="control-label col-lg-3">
							Enable Card Payments
						</label>
						<div class="col-lg-9">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="CHECKOUTCOM_CARD_ENABLED" id="CHECKOUTCOM_CARD_ENABLED_on" value="1" {if $fields_value.CHECKOUTCOM_CARD_ENABLED=="1"}checked="checked"{/if}/>
								<label  for="CHECKOUTCOM_CARD_ENABLED_on">Oui</label>
								<input type="radio" name="CHECKOUTCOM_CARD_ENABLED" id="CHECKOUTCOM_CARD_ENABLED_off" value="0" {if $fields_value.CHECKOUTCOM_CARD_ENABLED=="0"}checked="checked"{/if}/>
								<label  for="CHECKOUTCOM_CARD_ENABLED_off">Non</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
					<div class="card-enabled-container">
						<div class="form-group">
							<label class="control-label col-lg-3">
								Type of payment
							</label>
							<div class="col-lg-9">
								<select name="CHECKOUTCOM_PAYMENT_ACTION" class=" fixed-width-xl" id="CHECKOUTCOM_PAYMENT_ACTION">
									<option value="0" {if $fields_value.CHECKOUTCOM_PAYMENT_ACTION=="0"}selected="selected"{/if}>Deferred (Authorize)</option>
									<option value="1" {if $fields_value.CHECKOUTCOM_PAYMENT_ACTION=="1"}selected="selected"{/if}>Immediate (Authorize + Capture)</option>
								</select>
							</div>
						</div>
						<div class="deferred-payment-container">
							<div class="form-group">
								<label class="control-label col-lg-3">
									Event that will trigger remittance to bank
								</label>
								<div class="col-lg-9 regular-radio-container">
									<input class="regular-radio-btn" type="radio" name="CHECKOUTCOM_PAYMENT_EVENT" id="CHECKOUTCOM_PAYMENT_EVENT_delay" value="1" {if $fields_value.CHECKOUTCOM_PAYMENT_EVENT=="1"}checked="checked"{/if}/>
									<label class="regular-radio-label" for="CHECKOUTCOM_PAYMENT_EVENT_delay">Delay</label><br>
									<input class="regular-radio-btn" type="radio" name="CHECKOUTCOM_PAYMENT_EVENT" id="CHECKOUTCOM_PAYMENT_EVENT_immediate" value="0" {if $fields_value.CHECKOUTCOM_PAYMENT_EVENT=="0"}checked="checked"{/if}/>
									<label class="regular-radio-label" for="CHECKOUTCOM_PAYMENT_EVENT_immediate">Order status</label>
									<p class="help-block">
										<b>Delay: </b>Automatically triggered after a delay<br>
										<b>Order status: </b>Automatically triggered on order status change<br>
										Please note that order status option allows to trigger remittance also manually by using the action button in order details.
									</p>
								</div>
							</div>
							<div class="delayed-payment-container">
								<div class="form-group">
									<label class="control-label col-lg-3">
										Delay (hours before remittance to bank)
									</label>
									<div class="col-lg-2">
										<input type="number" class="form-control" name="CHECKOUTCOM_CAPTURE_TIME" id="CHECKOUTCOM_CAPTURE_TIME" value="{$fields_value.CHECKOUTCOM_CAPTURE_TIME|escape:'htmlall':'UTF-8'}"/>
									</div>
								</div>
							</div>
							<div class="status-payment-container">
								<div class="form-group">
									<label class="control-label col-lg-3">
										Order statuses that trigger capture
									</label>
									<div class="col-lg-9">
										<input type="hidden" name="trigger_statuses" value="no_status">
										<select name="trigger_statuses[]" class="trigger-statuses" id="CHECKOUTCOM_TRIGGER_STATUS" multiple="multiple">
											{foreach from=$order_states item=order_state}
												<option value="{$order_state.id_order_state|escape:'htmlall':'UTF-8'}" {if $order_state.id_order_state|in_array:$trigger_statuses}selected{/if}>{$order_state.name|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
						</div>
						<br>
						<h3>
							Payment display
						</h3>
						<div class="form-group">
							<label class="control-label col-lg-3 required">
								Payment Option Title
							</label>
							{assign var=lang_val value=$fields_value.CHECKOUTCOM_CARD_TITLE|json_decode:1}
							<div class="col-lg-2">
								<input class="multilang-field" type="text" name="CHECKOUTCOM_CARD_TITLE" id="CHECKOUTCOM_CARD_TITLE" {if $lang_val}value="{$lang_val|reset}{/if}"/>
							</div>
							<div class="col-lg-1">
								<select class="multilang-select">
									{foreach from=$languages item=language}
										<option value="{$language.iso_code}">{$language.iso_code}</option>
									{/foreach}
								</select>
								{foreach from=$languages item=language}
									{assign var="iso_code" value=$language.iso_code}
									<input class="multilang-hidden" type="hidden" name="CHECKOUTCOM_CARD_TITLE[{$iso_code}]" data-lang="{$iso_code}" {if $lang_val}value="{$lang_val.$iso_code}{/if}">
								{/foreach}
								<input type="hidden" name="CHECKOUTCOM_CARD_TITLE[default]" value="Pay by Card with Checkout.com">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								Iframe Style
							</label>
							<div class="col-lg-9">
								<select name="CHECKOUTCOM_CARD_IFRAME_STYLE" class=" fixed-width-xl" id="CHECKOUTCOM_CARD_IFRAME_STYLE">
									<option value="singleIframe" {if $fields_value.CHECKOUTCOM_CARD_IFRAME_STYLE=="singleIframe"}selected{/if}>Single Iframe</option>
									<option value="multiIframe" {if $fields_value.CHECKOUTCOM_CARD_IFRAME_STYLE=="multiIframe"}selected{/if}>Multiple Iframe</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								Language Fallback
							</label>
							<div class="col-lg-9">
								<select name="CHECKOUTCOM_CARD_LANG_FALLBACK" class=" fixed-width-xl" id="CHECKOUTCOM_CARD_LANG_FALLBACK">
									<option value="EN-GB" {if $fields_value.CHECKOUTCOM_CARD_LANG_FALLBACK=="EN-GB"}selected{/if}>English</option>
									<option value="ES-ES" {if $fields_value.CHECKOUTCOM_CARD_LANG_FALLBACK=="ES-ES"}selected{/if}>Spanish</option>
									<option value="DE-DE" {if $fields_value.CHECKOUTCOM_CARD_LANG_FALLBACK=="DE-DE"}selected{/if}>German</option>
									<option value="KR-KR" {if $fields_value.CHECKOUTCOM_CARD_LANG_FALLBACK=="KR-KR"}selected{/if}>Korean</option>
									<option value="FR-FR" {if $fields_value.CHECKOUTCOM_CARD_LANG_FALLBACK=="FR-FR"}selected{/if}>French</option>
									<option value="IT-IT" {if $fields_value.CHECKOUTCOM_CARD_LANG_FALLBACK=="IT-IT"}selected{/if}>Italian</option>
									<option value="NL-NL" {if $fields_value.CHECKOUTCOM_CARD_LANG_FALLBACK=="NL-NL"}selected{/if}>Dutch</option>
								</select>
							</div>
						</div>
						<h3>
							Advanced settings
						</h3>
						<div class="form-group">
							<label class="control-label col-lg-3">
								Use 3D Secure
							</label>
							<div class="col-lg-9">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CHECKOUTCOM_CARD_USE_3DS" id="CHECKOUTCOM_CARD_USE_3DS_on" value="1" {if $fields_value.CHECKOUTCOM_CARD_USE_3DS=="1"}checked="checked"{/if}/>
									<label  for="CHECKOUTCOM_CARD_USE_3DS_on">Oui</label>
									<input type="radio" name="CHECKOUTCOM_CARD_USE_3DS" id="CHECKOUTCOM_CARD_USE_3DS_off" value="0" {if $fields_value.CHECKOUTCOM_CARD_USE_3DS=="0"}checked="checked"{/if}/>
									<label  for="CHECKOUTCOM_CARD_USE_3DS_off">Non</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								Attempt non-3D Secure
							</label>
							<div class="col-lg-9">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D" id="CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D_on" value="1" {if $fields_value.CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D=="1"}checked="checked"{/if}/>
									<label  for="CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D_on">Oui</label>
									<input type="radio" name="CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D" id="CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D_off" value="0" {if $fields_value.CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D=="0"}checked="checked"{/if}/>
									<label  for="CHECKOUTCOM_CARD_USE_3DS_ATTEMPT_N3D_off">Non</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								Save Card Option
							</label>
							<div class="col-lg-9">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CHECKOUTCOM_CARD_SAVE_CARD_OPTION" id="CHECKOUTCOM_CARD_SAVE_CARD_OPTION_on" value="1" {if $fields_value.CHECKOUTCOM_CARD_SAVE_CARD_OPTION=="1"}checked="checked"{/if}/>
									<label  for="CHECKOUTCOM_CARD_SAVE_CARD_OPTION_on">Oui</label>
									<input type="radio" name="CHECKOUTCOM_CARD_SAVE_CARD_OPTION" id="CHECKOUTCOM_CARD_SAVE_CARD_OPTION_off" value="0" {if $fields_value.CHECKOUTCOM_CARD_SAVE_CARD_OPTION=="0"}checked="checked"{/if}/>
									<label  for="CHECKOUTCOM_CARD_SAVE_CARD_OPTION_off">Non</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3">
								Enable MADA BIN Check
							</label>
							<div class="col-lg-9">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CHECKOUTCOM_CARD_MADA_CHECK_ENABLED" id="CHECKOUTCOM_CARD_MADA_CHECK_ENABLED_on" value="1" {if $fields_value.CHECKOUTCOM_CARD_MADA_CHECK_ENABLED=="1"}checked="checked"{/if}/>
									<label  for="CHECKOUTCOM_CARD_MADA_CHECK_ENABLED_on">Oui</label>
									<input type="radio" name="CHECKOUTCOM_CARD_MADA_CHECK_ENABLED" id="CHECKOUTCOM_CARD_MADA_CHECK_ENABLED_off" value="0" {if $fields_value.CHECKOUTCOM_CARD_MADA_CHECK_ENABLED=="0"}checked="checked"{/if}/>
									<label  for="CHECKOUTCOM_CARD_MADA_CHECK_ENABLED_off">Non</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<button type="submit" value="1" id="module_form_submit_btn_1" name="submitCheckoutComModule" class="btn btn-default pull-right">
						<i class="process-icon-save"></i> Enregistrer
					</button>
				</div>
			</div>
		</form>
	</div>
	<div class="tab-pane" id="template_3">{$config_alternatives}</div>
	<div class="tab-pane" id="template_4">
		<form id="module_form_2" class="defaultForm form-horizontal" action="" method="post" enctype="multipart/form-data" novalidate>
			<input type="hidden" name="submitCheckoutComModule" value="1" />
			<div class="panel" id="fieldset_0_2">
				<div class="panel-heading"></div>
				<div class="form-wrapper">
					<div class="form-group">
						<label class="control-label col-lg-3 required">
							Payment Option Title
						</label>
						{assign var=lang_val value=$fields_value.CHECKOUTCOM_GOOGLE_TITLE|json_decode:1}
						<div class="col-lg-2">
							<input class="multilang-field" type="text" name="CHECKOUTCOM_GOOGLE_TITLE" id="CHECKOUTCOM_GOOGLE_TITLE" {if $lang_val}value="{$lang_val|reset}{/if}"/>
						</div>
						<div class="col-lg-1">
							<select class="multilang-select">
								{foreach from=$languages item=language}
									<option value="{$language.iso_code}">{$language.iso_code}</option>
								{/foreach}
							</select>
							{foreach from=$languages item=language}
								{assign var="iso_code" value=$language.iso_code}
								<input class="multilang-hidden" type="hidden" name="CHECKOUTCOM_GOOGLE_TITLE[{$iso_code}]" data-lang="{$iso_code}" {if $lang_val}value="{$lang_val.$iso_code}{/if}">
							{/foreach}
							<input type="hidden" name="CHECKOUTCOM_GOOGLE_TITLE[default]" value="Pay by Google Pay with Checkout.com">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-3">
							Enable Google Pay
						</label>
						<div class="col-lg-9">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="CHECKOUTCOM_GOOGLE_ENABLED" id="CHECKOUTCOM_GOOGLE_ENABLED_on" value="1" {if $fields_value.CHECKOUTCOM_GOOGLE_ENABLED=="1"}checked="checked"{/if}/>
								<label  for="CHECKOUTCOM_GOOGLE_ENABLED_on">Oui</label>
								<input type="radio" name="CHECKOUTCOM_GOOGLE_ENABLED" id="CHECKOUTCOM_GOOGLE_ENABLED_off" value="0" {if $fields_value.CHECKOUTCOM_GOOGLE_ENABLED=="0"}checked="checked"{/if}/>
								<label  for="CHECKOUTCOM_GOOGLE_ENABLED_off">Non</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-3 required">
							Merchant ID
						</label>
						<div class="col-lg-2">
							<input class="multilang-field" type="text" name="CHECKOUTCOM_GOOGLE_ID" id="CHECKOUTCOM_GOOGLE_ID" value="{$fields_value.CHECKOUTCOM_GOOGLE_ID}"/>
							<p class="help-block">
								Provide your Google Merchant ID.
							</p>
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<button type="submit" value="1" id="module_form_submit_btn_2" name="submitCheckoutComModule" class="btn btn-default pull-right">
						<i class="process-icon-save"></i> Enregistrer
					</button>
				</div>
			</div>
		</form>
	</div>
	<div class="tab-pane" id="template_5">{$config_apple}</div>
</div>