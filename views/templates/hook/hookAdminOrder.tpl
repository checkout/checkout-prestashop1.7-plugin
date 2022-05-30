{*
* 2021 Checkout.com
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License 3.0 (AFL-3.0).
* It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
*
* @author    PrestaShop / PrestaShop partner
* @copyright 2021 Checkout.com
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}

<div id="checkoutcom-admin-order" class="card panel">
	<h3 class="card-header panel-heading">
		<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="checkoutcom-logo" style="width:14px;display:inline-block;">
		{l s='Checkout.com' mod='checkoutcom'}
	</h3>
	<div class="card-body">
		{if isset($transactionError)}
			<div class="alert alert-danger">
				<p class="text-danger">{$transactionError|escape:'htmlall':'UTF-8'}</p>
			</div>
		{/if}
		{if isset($capture_confirmation) && $capture_confirmation}
			<div class="alert alert-success">
				<p class="text-success">{l s='Funds have been captured successfully' mod='checkoutcom'}</p>
			</div>
		{/if}
		{if isset($refund_confirmation) && $refund_confirmation}
			<div class="alert alert-success">
				<p class="text-success">{l s='Refund done successfully' mod='checkoutcom'}</p>
			</div>
		{/if}
		<div class="row">
			<div class="col-md-12">
				<div class="info-block">
					<div class="row">
						<div class="col-sm col-sm-4 text-center">
							<p class="text-muted mb-0"><strong>{l s='Transaction number' mod='checkoutcom'}</strong></p>
							<strong id="">{$transaction.transaction_id|escape:'htmlall':'UTF-8'}</strong>
						</div>
						<div id="" class="col-sm col-sm-4 text-center">
							<p class="text-muted mb-0"><strong>{l s='Total' mod='checkoutcom'}</strong></p>
							<strong id="">{displayPrice price=$transaction.amount|floatval currency=$transaction.id_currency|intval}</strong>
						</div>
						<div id="" class="col-sm col-sm-4 text-center">
							<p class="text-muted mb-0"><strong>{l s='Payment method' mod='checkoutcom'}</strong></p>
							<strong id="">{$transaction.payment_method|escape:'htmlall':'UTF-8'}</strong>
						</div>
						{* <div id="" class="col-sm text-center">
							<p class="text-muted mb-0"><strong>{l s='3D Secure' mod='checkoutcom'}</strong></p>
							<strong id="">
								{if $transaction.guarantee3DS == 1}{l s='Yes' mod='checkoutcom'}{else}{l s='No' mod='checkoutcom'}{/if}
							</strong>
						</div> *}
					</div>
				</div>
			</div>
		</div>
		<p></p>
                
		{if isset($transaction.isCapturable)}
			<div class="row">
				<div class="col-xl-12">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col">
									<h4 class="text-center">{l s='Capture' mod='checkoutcom'}</h4>
									<div class="row mb-1">
										<div class="col-6 col-sm-6 text-right">{l s='Amount captured' mod='checkoutcom'}</div>
										<div class="col-6 col-sm-6">{displayPrice price=$transaction.amountCaptured|floatval currency=$transaction.id_currency|intval}</div>
									</div>
									<div class="row mb-1">
										<div class="col-6 col-sm-6 text-right">{l s='Amount that can be captured' mod='checkoutcom'}</div>
										<div class="col-6 col-sm-6">{displayPrice price=$transaction.capturableAmount|floatval currency=$transaction.id_currency|intval}</div>
									</div>
									{if $transaction.isCapturable}
										<hr>
										<form class="form-horizontal"
													action=""
													name="checkoutcom_capture"
													id="checkoutcom-capture-form"
													method="post"
													enctype="multipart/form-data">
											<div class="form-group row">
												<div class="col-lg-2 col-lg-offset-5 offset-lg-5">
													<div class="input-group money-type">
														<input type="text"
																	 id=""
																	 name="amountToCapture"
																	 class="form-control"
																	 onchange="this.value = parseFloat(this.value.replace(/,/g, '.')) || 0"
																	 value="{$transaction.capturableAmount|floatval|string_format:'%.2f'}">
														<div class="input-group-append input-group-addon">
															<span class="input-group-text"> €</span>
														</div>
													</div>
													<br>
													<div class="text-center">
														<button class="btn btn-primary btn-sm ml-2">
															{l s='Submit' mod='checkoutcom'}
														</button>
													</div>
													<input type="hidden" name="transaction_id" value="{$transaction.transaction_id|intval}"/>
												</div>
											</div>
										</form>
										{* {if $order_total != $transaction.amount}
											<div class="alert alert-warning">
												<p class="text-warning">
													{capture name="txt_amount_capture"}
														{displayPrice price=$transaction.capturableAmount|floatval currency=$id_currency_euro|intval}
													{/capture}
													{capture name="txt_amount_total"}
														{displayPrice price=$order_total|floatval currency=$id_currency_euro|intval}
													{/capture}
													{l s='Be careful, you\'re about to capture [1]%s[/1] while the total of order is [1]%s[/1]' sprintf=[$smarty.capture.txt_amount_capture, $smarty.capture.txt_amount_total] tags=['<b>'] mod='checkoutcom'}
												</p>
											</div>
										{/if} *}
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
				{* <div class="col-xl-6">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col">
									<h4>{l s='Refund' mod='checkoutcom'}</h4>
									<div class="row mb-1">
										<div class="col-6 text-right">{l s='Amount refunded' mod='checkoutcom'}</div>
										<div class="col-6">{displayPrice price=$transaction.amountRefunded|floatval currency=$id_currency_euro|intval}</div>
									</div>
									<div class="row mb-1">
										<div class="col-6 text-right">{l s='Amount that can be refunded' mod='checkoutcom'}</div>
										<div class="col-6">{displayPrice price=$transaction.refundableAmount|floatval currency=$id_currency_euro|intval}</div>
									</div>
									{if $transaction.isRefundable}
										<hr>
										<form class="form-horizontal"
													action=""
													name="checkoutcom_refund"
													id="checkoutcom-refund-form"
													method="post"
													enctype="multipart/form-data">
											<div class="form-group row">
												<div class="col-sm">
													<div class="input-group money-type">
														<input type="text"
																	 id=""
																	 name="transaction[amountToRefund]"
																	 class="form-control"
																	 onchange="this.value = parseFloat(this.value.replace(/,/g, '.')) || 0"
																	 value="{$transaction.refundableAmount|floatval|string_format:'%.2f'}">
														<div class="input-group-append">
															<span class="input-group-text"> €</span>
														</div>
														<button class="btn btn-primary btn-sm ml-2">
															{l s='Make refund' mod='checkoutcom'}
														</button>
													</div>
													<input type="hidden" name="transaction[id]" value="{$transaction.id|intval}"/>
													<input type="hidden" name="transaction[idOrder]" value="{$transaction.idOrder|intval}"/>
												</div>
											</div>
										</form>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div> *}
			</div>
		{/if}
	</div>
</div>
