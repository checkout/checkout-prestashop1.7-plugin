<form name="checkoutapipayment_form" id="checkoutapipayment_form" action="{$link->getModuleLink('checkoutapipayment', 'validation', [], true)|escape:'html'}" method="post">

    <div class="payment-select-txt">{*{l s='Select your payment option' mod='checkoutapipayment'}*}</div>
    <div class="widget-container"  style="height: 50px;"></div>

        <script type="text/javascript">
            jQuery(function(){
                jQuery('#click_checkoutapipayment').attr('href','javascript:void(0)');
            });

            var reload = false;
            window.checkoutIntegrationCurrentConfig= {
                debugMode: false,
                renderMode: '{$renderMode}',
                namespace: 'CheckoutIntegration',
                publicKey: '{$publicKey}',
                value: '{$amount}',
                currency: '{$currencyIso}',
                paymentMode: 'cards',
                showMobileIcons : true,
                forceMobileRedirect: false,
                widgetContainerSelector: '.widget-container',
                enableIframePreloading:false
            };

            window.checkoutIntegrationIsReady = window.checkoutIntegrationIsReady || false;
            if (!window.checkoutIntegrationIsReady) {

                window.CKOConfig = {
                    ready: function () {

                        if (window.checkoutIntegrationIsReady) {
                            return false;
                        }

                        if (typeof CKOAPIJS == 'undefined') {
                            return false;
                        }
                        CKOAPIJS.render(window.checkoutIntegrationCurrentConfig);
                        window.checkoutIntegrationIsReady = true;
                    }
                };

                var mode = '{$mode}';
                if(mode == 'sandbox'){
                    src = 'https://cdn.checkout.com/sandbox/js/checkout.js';
                } else {
                    src = 'https://cdn.checkout.com/js/checkout.js';
                }

                var script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.setAttribute('data-namespace', 'CKOAPIJS');
                document.head.appendChild(script);
            } else {
                CKOAPIJS.render(checkoutIntegrationCurrentConfig);
            }

        </script>
</form>