/**
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
 */

$(document).ready(function () {
	var webhook_url = $('.webhook-url-container').html();
	$("#CHECKOUTCOM_PUBLIC_KEY").parent().parent().parent().append(webhook_url);
	$('.webhook-url-container').remove();

	$('#CHECKOUTCOM_AUTHENTIFICATION_KEY').attr('readonly', true);
	$('#CHECKOUTCOM_SIGNATURE_KEY').attr('readonly', true);
	
	checkCardEnabled();
	checkDeferredPayment();
	checkDelayedPayment();
	checkServiceName();

	$('.trigger-statuses').select2();

	$("input[name=CHECKOUTCOM_CARD_ENABLED]").on('change', function(){
		checkCardEnabled();
	});

	$("#CHECKOUTCOM_PAYMENT_ACTION").on('change', function(){
		checkDeferredPayment();
	});

	$("input[name=CHECKOUTCOM_PAYMENT_EVENT]").on('change', function(){
		checkDelayedPayment();
	});

	$(".multilang-field").on('change', function(){
		var langIso = $(this).parent().parent().find('select').val();
		$(".multilang-hidden[data-lang="+langIso+"]").val($(this).val());
	});

	$(".multilang-select").on('change', function(){
		var langIso = $(this).parent().find('select').val();
		var optionSelected = $("option:selected", this);
		var hiddenValue = $(this).parent().find(".multilang-hidden[data-lang="+langIso+"]").val();
		$(this).parent().parent().find(".multilang-field").val(hiddenValue);
	});

	$("#CHECKOUTCOM_SERVICE").on('change', function(){
		checkServiceName();

		if ( $("#CHECKOUTCOM_SERVICE").val() == "1" ) {
			$("#CHECKOUTCOM_SECRET_KEY").val( $("#CHECKOUTCOM_SECRET_KEY_ABC").val() );
			$("#CHECKOUTCOM_PUBLIC_KEY").val( $("#CHECKOUTCOM_PUBLIC_KEY_ABC").val() );
		}else{
			$("#CHECKOUTCOM_SECRET_KEY").val( $("#CHECKOUTCOM_SECRET_KEY_NAS").val() );
			$("#CHECKOUTCOM_PUBLIC_KEY").val( $("#CHECKOUTCOM_PUBLIC_KEY_NAS").val() );
		}
	});

	function checkCardEnabled(){
		if ( $("#CHECKOUTCOM_CARD_ENABLED_on").is(':checked') ) {
			$(".card-enabled-container").slideDown();
		}else{
			$(".card-enabled-container").slideUp();
		}
	}

	function checkDeferredPayment(){
		if ( $("#CHECKOUTCOM_PAYMENT_ACTION").val() == "1" ) {
			$(".deferred-payment-container").slideUp();
		}else{
			$(".deferred-payment-container").slideDown();
		}
	}

	function checkDelayedPayment(){
		if ( $("#CHECKOUTCOM_PAYMENT_EVENT_delay").is(':checked') ) {
			$(".delayed-payment-container").slideDown();
			$(".status-payment-container").slideUp();
		}else{
			$(".delayed-payment-container").slideUp();
			$(".status-payment-container").slideDown();
		}
	}

	function checkServiceName(){
		var services = [
			'SEPA',
			'KLARNA',
			'KNET',
			'BOLETO'
		];

		if ( $("#CHECKOUTCOM_SERVICE").val() == "1" ) {
			$("#CHECKOUTCOM_SIGNATURE_KEY").parent().parent().slideUp();
			$("#CHECKOUTCOM_AUTHENTIFICATION_KEY").parent().parent().slideUp();
			$(".set-webhook-container").slideUp();

			$.each(services, function(index, value) {
				$("#CHECKOUTCOM_ALTERNATIVE_"+value+"_on").parent().parent().parent().show();
			});
		}else{
			$("#CHECKOUTCOM_SIGNATURE_KEY").parent().parent().slideDown();
			$("#CHECKOUTCOM_AUTHENTIFICATION_KEY").parent().parent().slideDown();
			$(".set-webhook-container").slideDown();

			$.each(services, function(index, value) {
				$("#CHECKOUTCOM_ALTERNATIVE_"+value+"_on").removeAttr('checked').parent().parent().parent().hide();
				$("#CHECKOUTCOM_ALTERNATIVE_"+value+"_off").attr('checked', 'checked');
			});
		}
	}
});