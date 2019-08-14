// JQuery events
(function() {

	// Override place order button behaviour
	$('input:radio[name="payment-option"]').change(function(){

		const name = $(this).attr('data-module-name');
		if(name.indexOf('checkoutcom_') === 0) {

			const $submit = $('div#payment-confirmation button[type=submit]');
			$submit.click(function(e) {
				$('#' + name).submit();
				return false;
			});

		}

	});

})();
