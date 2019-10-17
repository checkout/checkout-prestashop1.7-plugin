<form name="{$module}" id="{$module}-google-form" action="{$link->getModuleLink($module, 'payment', [], true)|escape:'html'}" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-merchantid="{$merchantid}" data-live="{$live}" method="POST">
	<input type="hidden" id="cko-google-signature" name="cko-google-signature" value="" />
    <input type="hidden" id="cko-google-protocolVersion" name="cko-google-protocolVersion" value="" />
    <input type="hidden" id="cko-google-signedMessage" name="cko-google-signedMessage" value="" />
</form>

{literal}
<script type="text/javascript" src="https://pay.google.com/gp/p/js/pay.js"></script>
<script type="text/javascript">

	const $gForm = document.getElementById('checkoutcom-google-form');

	var g = {

		info: function () {

			return {
				currencyCode: prestashop.currency.iso_code,
		        totalPriceStatus: 'FINAL',
		        totalPrice: prestashop.cart.totals.total_including_tax.amount
			};

		},


		init: function () {

			var paymentsClient = (new google.payments.api.PaymentsClient({ environment: $gForm.dataset.live ? 'PRODUCTION': 'TEST' }));
            paymentsClient.isReadyToPay({ allowedPaymentMethods: ['CARD', 'TOKENIZED_CARD'] })
            			  .then(function (response) {
console.log(response);
			                    // if (response.result) {
			                    //     addGooglePayButton();
			                    //     prefetchGooglePaymentData();
			                    // }

                				}
                			)
                		  .catch(function (err) {
			                    // show error in developer console for debugging
			                    console.error(err);
                			}
                		  );

		}

	}

	$gForm.onsubmit = function(e) {


		console.log('pay with google');

		g.init();



		return false;

    };

</script>
{/literal}
