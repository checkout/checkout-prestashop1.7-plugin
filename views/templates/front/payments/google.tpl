<form name="{$module}" id="{$module}-google-form" action="{$link->getModuleLink($module, 'payment', [], true)|escape:'html'}" method="POST">
	<input id="{$module}-card-source" type="hidden" name="source" value="card" required>
	<input id="{$module}-card-token" type="hidden" name="token" value="" required>

	<input type="hidden" id="cko-google-signature" name="cko-google-signature" value="" />
    <input type="hidden" id="cko-google-protocolVersion" name="cko-google-protocolVersion" value="" />
    <input type="hidden" id="cko-google-signedMessage" name="cko-google-signedMessage" value="" />
</form>

{literal}
<script type="text/javascript">

            googlePayUiController = (function () {
                var DOMStrings = {
                    buttonId: 'ckocom_googlePay',
                    buttonClass: 'google-pay-button',
                    googleButtonArea: 'method_wc_checkout_com_google_pay',
                    buttonArea: '.form-row.place-order',
                    placeOrder: '.place_order',
                    paymentOptionLabel: '#dt_method_checkoutcomgooglepay > label:nth-child(2)',
                    iconSpacer: 'cko-wallet-icon-spacer',
                    token: 'google-cko-card-token',
                }

                return {
                    hideDefaultPlaceOrder: function () {
                        jQuery(DOMStrings.placeOrder).hide();
                    },
                    addGooglePayButton: function (type) {
                        // Create the GooglePayButton
                        var button = document.createElement('button');
                        button.id = DOMStrings.buttonId;
                        // Add button class based on the user configuration
                        button.className = DOMStrings.buttonClass + " " + type
                        // Append the GooglePay button to the GooglePay area
                        jQuery('#payment').append(button);
                        // hide google pay button
                        jQuery('#ckocom_googlePay').hide();
                    },
                    addIconSpacer: function () {
                        jQuery(DOMStrings.paymentOptionLabel).append("<div class='" + iconSpacer + "'></div>")
                    },
                    getElements: function () {
                        return {
                            googlePayButtonId: jQuery(DOMStrings.buttonId),
                            googlePayButtonClass: jQuery(DOMStrings.buttonClass),
                            placeOrder: jQuery(DOMStrings.defaultPlaceOrder),
                            buttonArea: jQuery(DOMStrings.buttonArea),
                        };
                    },
                    getSelectors: function () {
                        return {
                            googlePayButtonId: DOMStrings.buttonId,
                            googlePayButtonClass: DOMStrings.buttonClass,
                            placeOrder: DOMStrings.defaultPlaceOrder,
                            buttonArea: DOMStrings.buttonArea,
                            token: DOMStrings.token,
                        };
                    }
                }
            })();

            googlePayTransactionController = (function (googlePayUiController) {
                var environment = '<?php echo $environment ?>' === false ? "PRODUCTION" : "TEST";
                var publicKey = '<?php echo $core_settings['ckocom_pk'] ?>';
                var merchantId = '<?php echo $this->get_option( 'ckocom_google_merchant_id' ) ?>';
                var currencyCode = '<?php echo $currencyCode ?>';
                var totalPrice = '<?php echo $totalPrice ?>';
                var buttonType = '<?php echo $this->get_option( 'ckocom_google_style' ) ?>';

                var generateTokenPath = '<?php echo $generate_token_url; ?>';
                var allowedPaymentMethods = ['CARD', 'TOKENIZED_CARD'];
                var allowedCardNetworks = ["AMEX", "DISCOVER", "JCB", "MASTERCARD", "VISA"];

                var _setupClickListeners = function () {
                    jQuery(document).on('click', '#' + googlePayUiController.getSelectors().googlePayButtonId, function (e) {
                        e.preventDefault();
                        _startPaymentDataRequest();
                    });
                }

                var _getGooglePaymentDataConfiguration = function () {
                    return {
                        merchantId: merchantId,
                        paymentMethodTokenizationParameters: {
                            tokenizationType: 'PAYMENT_GATEWAY',
                            parameters: {
                                'gateway': 'checkoutltd',
                                'gatewayMerchantId': publicKey
                            }
                        },
                        allowedPaymentMethods: allowedPaymentMethods,
                        cardRequirements: {
                            allowedCardNetworks: allowedCardNetworks
                        }
                    };
                }

                var _getGoogleTransactionInfo = function () {
                    return {
                        currencyCode: currencyCode,
                        totalPriceStatus: 'FINAL',
                        totalPrice: totalPrice
                    };
                }

                var _getGooglePaymentsClient = function () {
                    return (new google.payments.api.PaymentsClient({ environment: environment }));
                }

                var _generateCheckoutToken = function (token, callback) {
                    var data = JSON.parse(token.paymentMethodToken.token);
                    jQuery.ajax({
                        type: 'POST',
                        url : generateTokenPath,
                        data: {
                            token: {
                                protocolVersion: data.protocolVersion,
                                signature: data.signature,
                                signedMessage: data.signedMessage
                            }
                        },
                        success: function (outcome) {
                            callback(outcome);
                        },
                        error: function (err) {
                            console.log(err);
                        }
                    });
                }

                var _startPaymentDataRequest = function () {
                    var paymentDataRequest = _getGooglePaymentDataConfiguration();
                    paymentDataRequest.transactionInfo = _getGoogleTransactionInfo();

                    var paymentsClient = _getGooglePaymentsClient();
                    paymentsClient.loadPaymentData(paymentDataRequest)
                        .then(function (paymentData) {
                            document.getElementById('cko-google-signature').value = JSON.parse(paymentData.paymentMethodToken.token).signature;
                            document.getElementById('cko-google-protocolVersion').value = JSON.parse(paymentData.paymentMethodToken.token).protocolVersion;
                            document.getElementById('cko-google-signedMessage').value = JSON.parse(paymentData.paymentMethodToken.token).signedMessage;

                            jQuery('#place_order').prop("disabled",false);
                            jQuery('#place_order').trigger('click');
                        })
                        .catch(function (err) {
                            console.error(err);
                        });
                }

                return {
                    init: function () {
                        _setupClickListeners();
                        googlePayUiController.hideDefaultPlaceOrder();
                        googlePayUiController.addGooglePayButton(buttonType);
                    }
                }

            })(googlePayUiController);

            // Initialise google pay
            jQuery( document ).ready(function() {
                googlePayTransactionController.init();
            });

            // check if google pay method is check
            if(jQuery('#wc_checkout_com_google_pay').is(':checked')){
                // disable place order button
                jQuery('#place_order').prop("disabled",true);
            } else {
                // enable place order button if not google pay
                jQuery('#place_order').prop("disabled",false);
            }

            // On payment radio button click
            jQuery("input[name='payment_method']"). click(function(){
                // Check if payment method is google pay
                if(this.value == 'wc_checkout_com_google_pay'){
                    // Show google pay button
                    // disable place order button
                    jQuery('#ckocom_googlePay').show();
                    jQuery('#place_order').prop("disabled",true);
                } else {
                    // hide google pay button
                    // enable place order button
                    jQuery('#ckocom_googlePay').hide();
                    jQuery('#place_order').prop("disabled",false);
                }
            })

</script>
{/literal}
<br>