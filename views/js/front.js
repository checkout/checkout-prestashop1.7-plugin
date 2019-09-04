// JQuery events
(function() {

	const MODULE_NAME = 'checkoutcom';

	// Override place order button behaviour
	$('input:radio[name="payment-option"]').change(function(){

		const name = $(this).attr('data-module-name');
console.log(name);
		if(name.indexOf(MODULE_NAME) === 0) {

			const $submit = $('div#payment-confirmation button[type=submit]');
			$submit.click(function(e) {
				$('#' + name).submit();
				return false;
			});

		}

	});




	// Klarna
	const $kForm = $('#' + MODULE_NAME + '-klarna-form');
	const $klarna = $('#' + MODULE_NAME + '-klarna-frame');

	/**
     * Verify if Klarna is available.
     */
    if(window.k != undefined && $kForm && window.Klarna != undefined) {

        $.ajax({type: 'POST',
                url: $klarna.data('url'),
                contentType: "application/json",
                dataType:'json',
                data: {},

                success: function (data) {

                    if(data.success) {
                        $klarna.html = '';
                        k.load( data,
                                k.getMethods(data.payment_method_categories));
                        return;

                    }

                    k.remove();
            },
            error: function(data) {console.log(data.responseText);}
        });

    }





    // Sepa
    window.loadMandate = function(e) {

        const $sForm = $('#' + MODULE_NAME + '-sepa-form');
        const $sepa = $('#' + MODULE_NAME + '-sepa-frame');
        const $div = $('#' + MODULE_NAME + '-sepa-div');

        $.ajax({type: 'POST',
                url: $sepa.data('url'),
                contentType: "application/json",
                dataType:'html',
                data: $sForm.serializeArray(),

                success: function (res) {

                    if(res) {
                        $div.remove();
                        $sepa.html(res);
                    } else {
// display error
                    }

                },
                error: function(data) {
                    console.log('error');
                }
        });

    }









})();
