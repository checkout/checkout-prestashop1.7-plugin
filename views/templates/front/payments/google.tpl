<form name="{$module}" id="{$module}-google-form" action="{$link->getModuleLink($module, 'payment', [], true)|escape:'html'}" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-merchantid="{$merchantid}" data-live="{$live}" method="POST">
	<input type="hidden" id="cko-google-signature" name="cko-google-signature" value="" />
    <input type="hidden" id="cko-google-protocolVersion" name="cko-google-protocolVersion" value="" />
    <input type="hidden" id="cko-google-signedMessage" name="cko-google-signedMessage" value="" />
</form>

{literal}

<script type="text/javascript">


	const $gForm = document.getElementById('checkoutcom-google-form');
	const baseRequest = {
		apiVersion: 2,
	  	apiVersionMinor: 0
	},
		tokenizationSpecification = {
		  type: 'PAYMENT_GATEWAY',
		  parameters: {
		    'gateway': 'checkoutltd',
		    'gatewayMerchantId': $gForm.dataset.key
		  }
	},
		allowedPaymentMethods = ['CARD', 'TOKENIZED_CARD'],
		allowedCardNetworks = ['MASTERCARD', 'VISA'],
		allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"],
		baseCardPaymentMethod = {
			type: 'CARD',
			parameters: {
				allowedAuthMethods: allowedCardAuthMethods,
				allowedCardNetworks: allowedCardNetworks
			}
	},
		cardPaymentMethod = Object.assign({tokenizationSpecification: tokenizationSpecification}, baseCardPaymentMethod);




	function ckoGPay() {

		const paymentsClient = new google.payments.api.PaymentsClient({environment: $gForm.dataset.live ? 'PRODUCTION' : 'TEST'}),
			  isReadyToPayRequest = Object.assign({}, baseRequest);


		isReadyToPayRequest.allowedPaymentMethods = [baseCardPaymentMethod];

		// Init GooglePay
		paymentsClient.isReadyToPay(isReadyToPayRequest).then(function(response) {

			if (response.result) {
				// add a Google Pay payment button



			}

	    }).catch(function(err) {

			// show error in developer console for debugging
			console.error(err);

	    });


	}


</script>
<script type="text/javascript" async src="https://pay.google.com/gp/p/js/pay.js" onload="ckoGPay()"></script>











<script >




















// 	var g = {

// 		info: function () {

// 			return {
// 				currencyCode: prestashop.currency.iso_code,
// 		        totalPriceStatus: 'FINAL',
// 		        totalPrice: prestashop.cart.totals.total_including_tax.amount
// 			};

// 		},


// 		init: function () {

// 			var paymentsClient = (new google.payments.api.PaymentsClient({ environment: $gForm.dataset.live ? 'PRODUCTION': 'TEST' }));
//             paymentsClient.isReadyToPay({ allowedPaymentMethods: ['CARD', 'TOKENIZED_CARD'] })
//             			  .then(function (response) {
// console.log(response);
// 			                    // if (response.result) {
// 			                    //     addGooglePayButton();
// 			                    //     prefetchGooglePaymentData();
// 			                    // }

//                 				}
//                 			)
//                 		  .catch(function (err) {
// 			                    // show error in developer console for debugging
// 			                    console.error(err);
//                 			}
//                 		  );

// 		}

// 	}

// 	$gForm.onsubmit = function(e) {


// 		console.log('pay with google');

// 		g.init();



// 		return false;

//     };

</script>
{/literal}
