$(document).ready(function () {
	var webhook_url = $('.webhook-url-container').html();
	$("#CHECKOUTCOM_PUBLIC_KEY").parent().parent().parent().append(webhook_url);
	$('.webhook-url-container').remove();
	
	checkCardEnabled();
	checkDeferredPayment();
	checkDelayedPayment();

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
});