/**
 * Very import line.
 */
var $el = document.getElementById('payment-confirmation');
if($el) { // Confirmation page
    window.checkoutcom.$confirmation = $el;
}

/**
 * On document ready.
 */
(function() {

	const MODULE_NAME = 'checkoutcom';

    var lastOption = '';
	// Override place order button behaviour
	$('input:radio[name="payment-option"]').change(function(){

        // Trigger custom form events every "change" event once, even for other payment methods.
        if(lastOption) {
            document.getElementById(lastOption).dispatchEvent(new Event("form:hide"));
            lastOption = ''; // Prevent duplicate triggers.
        }

		const name = $(this).attr('data-module-name');
        const formButtonId = $(this).attr("id");

        let $submit = $("div#payment-confirmation button[type=submit]");

		if (name.indexOf(MODULE_NAME) === 0) {
          $submit.click(function (e) {
            $("#" + name).submit();
            return false;
          });

          // Trigger "show" event.
          document.getElementById(name).dispatchEvent(new Event("form:show"));
          lastOption = name;
        } else {
          $submit.click(function (e) {
            $("button#pay-with-" + formButtonId)
              .parent("form")
              .submit();
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
