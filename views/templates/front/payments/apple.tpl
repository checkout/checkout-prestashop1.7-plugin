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

 <form name="{$module|escape:'htmlall':'UTF-8'}" id="{$module|escape:'htmlall':'UTF-8'}-apple-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html':'UTF-8'}" data-key="{$CHECKOUTCOM_PUBLIC_KEY|escape:'htmlall':'UTF-8'}"  data-merchant_country_iso="{$merchant_country_iso|escape:'htmlall':'UTF-8'}" data-merchantid="{$merchantid|escape:'htmlall':'UTF-8'}" data-live="{$live|escape:'htmlall':'UTF-8'}" data-invoiceid="{$invoiceid|escape:'htmlall':'UTF-8'}" data-module="{$module|escape:'htmlall':'UTF-8'}" method="POST">
    <input id="{$module|escape:'htmlall':'UTF-8'}-apple-source" type="hidden" name="source" value="apple" required />
    <input type="hidden" id="{$module|escape:'htmlall':'UTF-8'}-apple-token" name="token" value="" />
</form>

{literal}
<script type="text/javascript">
	if(window.ApplePaySession){
		 	//applePayOptionForm.style.display = "block";
		 }else{
			 document.getElementById('checkoutcom-apple-form').parentElement.previousElementSibling.style.display = 'none';
		 }
	var applePayForm = document.querySelector('#checkoutcom-apple-form');
	applePayForm.addEventListener("form:show", function(event) {
		// Hide core confirmation
		window.checkoutcom.$confirmation.childNodes[1].style.display = 'none';
		var button = document.createElement('div');
		button.id = 'apple-pay-div';
		// Create Apple Pay button
		button.innerHTML = '<a id="checkoutcom-apple-pay" lang="us" class="applePayButton" style="-webkit-appearance: -apple-pay-button; -apple-pay-button-type: plain; -apple-pay-button-style: black; height: 60px; width: 275px;" title="Start Apple Pay" role="link" tabindex="0"/>';
		// button.id = 'checkoutcom-apple-pay';
		window.checkoutcom.$confirmation.appendChild(button);
		var applePayButton = document.querySelector('.applePayButton');
		var applePayOptionForm = document.querySelector('.payment-option-checkoutcom-apple-form');
		 
		applePayButton.addEventListener('click', function(){
			var terms = document.getElementById("conditions_to_approve[terms-and-conditions]");
			if (terms.checked) {
				var paymentReq = {
					countryCode: applePayForm.dataset.merchant_country_iso,
					currencyCode: prestashop.currency.iso_code,
					merchantCapabilities: [
						"supports3DS"
					],
					supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
					total: {
						label: prestashop.shop.name,
						amount: prestashop.cart.totals.total.amount
					},
					lineItems: [
						{
							label: "VAT",
							amount: prestashop.cart.subtotals.tax  == null ? 0 : prestashop.cart.subtotals.tax.amount,
							type: "final"
						}
					]
				};

				var apple_pay_ = new ApplePaySession(6, paymentReq);
				apple_pay_.begin();
				// var apple_validate_url = "module/" + applePayForm.dataset.module + "/applepay";
				var apple_validate_url = "index.php?fc=module&module="+applePayForm.dataset.module+"&controller=applepay";
				apple_pay_.onpaymentauthorized = function onpaymentauthorized(event) {
					var token = event.payment.token;
					if(token){
						const $token = document.getElementById('checkoutcom-apple-token');
						$token.value = JSON.stringify(token.paymentData);
						CheckoutcomApplePay(document.getElementById('checkoutcom-apple-form'));
						apple_pay_.completePayment(ApplePaySession.STATUS_SUCCESS);
					}else{
						apple_pay_.completePayment(ApplePaySession.STATUS_FAILURE);
					}
				};

				apple_pay_.onvalidatemerchant = function onvalidatemerchant(event) {
					var appleValidationUrl = event.validationURL;
					//@todo: use XMLHttpRequest instead of jQuery post
					$.post(apple_validate_url,{ url: appleValidationUrl },
						function( data ) {
							apple_pay_.completeMerchantValidation(JSON.parse(data));
						}, 'text')
						.fail(function(xhr, textStatus, errorThrown) {
							apple_pay_.abort();
						});
				};
			}
			
		});
		document.getElementById('apple-pay-div').scrollIntoView();
	});

	// Apple Pay Form Exit
	applePayForm.addEventListener("form:hide", function(event) {
		// Show confirmation button
		window.checkoutcom.$confirmation.childNodes[1].style.display = 'inline-block';
		document.getElementById('checkoutcom-apple-pay').remove();
	});
	/**
	 * Checkout Apple Pay Class.
	 *
	 * @class      CheckoutcomApplePay (name)
	 * @param      {<type>}    $form   The form
	 * @return     {Function}  { description_of_the_return_value }
	 */
	function CheckoutcomApplePay($form) {
		const $token = document.getElementById('checkoutcom-apple-token');
		const $source = document.getElementById('checkoutcom-apple-source');
		var submitted = false;
		$form.onsubmit = function(e) {
			e.preventDefault();
			if($token.value && !submitted) {
				submitted = true;
				$source.value = 'apple';
				$form.submit();
			}
		};
		$form.submit();
	}
</script>
{/literal}
