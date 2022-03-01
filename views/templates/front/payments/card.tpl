<form name="{$module}" id="{$module}-card-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">

    <input id="{$module}-card-source" type="hidden" name="source" value="card" required>
    <input id="{$module}-card-token" type="hidden" name="token" value="" required>
    <input id="{$module}-card-bin" type="hidden" name="bin" value="">

    {if $save_card_option == '1' and $is_guest == 0  }

        <ul class="payment_methods">
            {if !empty($cardLists)}
                {foreach name=outer item=last_four from=$cardLists}
                    {foreach key=key item=item from=$last_four}
                        {if $key == 'last_four'}
                            {assign var="last_four" value="{$item}"}
                        {/if}

                        {if $key == 'card_scheme'}
                            {assign var="card_scheme" value="{$item}"}
                        {/if}

                        {if $key == 'entity_id'}
                            {assign var="entity_id" value="{$item}"}
                        {/if}

                        {if $key == 'is_mada'}
                            {assign var ="is_mada" value="{$item}"}
                        {/if}

                    {/foreach}

                    {if $is_mada}
                        <li>
                            <label>
                                <input  class="{$module}-saved-card-mada" type="radio" name="{$module}-saved-card" value="{$entity_id}"/>
                                <img class="card-logo" src="{$img_dir}{strtoLower($card_scheme)}.svg"> ●●●● {$last_four}
                            </label>
                        </li>

                    {else}
                        <li>
                            <label>
                                <input  class="{$module}-saved-card" type="radio" name="{$module}-saved-card" value="{$entity_id}"/>
                                <img class="card-logo" src="{$img_dir}{strtoLower($card_scheme)}.svg"> ●●●● {$last_four}
                            </label>
                        </li>
                    {/if}

                {/foreach}

                <li>
                    <label>
                        <input  class= "{$module}-new-card" type="radio" name="{$module}-saved-card"  value="new_card"/>
                        <img class="card-logo" src="{$img_dir}addcard.svg"> {l s='New card' mod='checkoutcom' }
                    </label>
                </li>

            {/if}
        </ul>
    {/if}

    {if $isSingleIframe}
        {*frames will be added here*}
        <div id="{$module}-card-frame" class="card-frame" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-billing="{$billingId}" data-debug="{$debug}" data-lang="{$lang}" data-module="{$module}" data-saveCard="{$save_card_option}"  ></div>
    {else}
        <div id="{$module}-multi-frame" class="multi-frame" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-billing="{$billingId}" data-debug="{$debug}" data-lang="{$lang}" data-module="{$module}" data-saveCard="{$save_card_option}" data-imagedir="{$img_dir}">
            {*frames will be added here*}
            <div class="input-container card-number">
                <div class="icon-container">
                    <img id="icon-card-number"
                        src="{$img_dir}card-icons/card.svg"
                        alt="PAN" />
                </div>
                <div class="card-number-frame"></div>
                <div class="icon-container payment-method">
                    <img id="logo-payment-method" />
                </div>
                <div class="icon-container">
                    <img id="icon-card-number-error"
                        src="{$img_dir}card-icons/error.svg">
                </div>
            </div>

            <div class="date-and-code">
                <div>
                    <div class="input-container expiry-date">
                        <div class="icon-container">
                            <img id="icon-expiry-date"
                                src="{$img_dir}card-icons/exp-date.svg"
                                alt="Expiry date" />
                        </div>
                        <div class="expiry-date-frame"></div>
                        <div class="icon-container">
                            <img id="icon-expiry-date-error"
                                src="{$img_dir}card-icons/error.svg" />
                        </div>
                    </div>
                </div>

                <div>
                    <div class="input-container cvv">
                        <div class="icon-container">
                            <img id="icon-cvv"
                                src="{$img_dir}card-icons/cvv.svg"
                                alt="CVV" />
                        </div>
                        <div class="cvv-frame"></div>
                        <div class="icon-container">
                            <img id="icon-cvv-error"
                                src="{$img_dir}card-icons/error.svg" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    {/if}

    {if $save_card_option == '1' and $is_guest == 0 }
        {*saved card checkbox0*}
        <div class="save-card-check">
            <label for="save-card-checkbox" >
                <input type="checkbox" name="save-card-checkbox" id="save-card-checkbox" />
                {l s='Save card for future payment' mod='checkoutcom'}
            </label>
        </div>

        <div class="cvvVerification">
            <label for="{$module}-cko-cvv">
                <em>* </em>{l s='Card Verification Number' mod='checkoutcom'}
                <input id="{$module}-cko-cvv" type="number" name="cko-cvv" min="3" max="4" autocomplete="off"/>
            </label>
        </div>
    {/if}
</form>
{literal}
<script type="text/javascript">
    /**
     * Checkout Frames Pay Class.
     *
     * @class      CheckoutcomFramesPay (name)
     * @param      {<type>}    $form   The form
     * @return     {Function}  { description_of_the_return_value }
     */
    function CheckoutcomFramesPay($form) {

        if ( document.getElementById("checkoutcom-card-frame") !== null ) {
            var $frames =  document.getElementById("checkoutcom-card-frame");
        }else{
            var $frames =  document.getElementById("checkoutcom-multi-frame");
        }

        const $token = document.getElementById('checkoutcom-card-token');
        const $bin = document.getElementById('checkoutcom-card-bin');
        const $source = document.getElementById('checkoutcom-card-source');
        const $imageDir = $frames.dataset.imagedir;
        var submitted = false; // Prevent multiple submit

        /**
         * Customer phone length check
         */
        var customerPhone = prestashop.customer.addresses[$frames.dataset.billing].phone;
        if ( customerPhone.length < 6 || customerPhone.length > 25 ) {
            customerPhone = '';
        }

        /**
         * Initialize frames.
         */
        Frames.init({
            publicKey: $frames.dataset.key,
            debug: Boolean(+$frames.dataset.debug),
            localization: $frames.dataset.lang.toUpperCase(),
            cardholder: {
                name: prestashop.customer.addresses[$frames.dataset.billing].firstname + ' ' + prestashop.customer.addresses[$frames.dataset.billing].lastname,
                billingAddress: {
                    addressLine1: prestashop.customer.addresses[$frames.dataset.billing].address1,
                    addressLine2: prestashop.customer.addresses[$frames.dataset.billing].address2,
                    postcode:     prestashop.customer.addresses[$frames.dataset.billing].postcode,
                    city:         prestashop.customer.addresses[$frames.dataset.billing].city,
                    state:        prestashop.customer.addresses[$frames.dataset.billing].state,
                    country:      prestashop.customer.addresses[$frames.dataset.billing].country_iso
                },
                phone: customerPhone,
            }
        });

        /**
         * Add card tokenization failed event.
         */
        Frames.addEventHandler(
            Frames.Events.CARD_TOKENIZATION_FAILED,
            function (event) {
                $token.value = '';
                $bin.value = '';
            }
        );

        /**
         * Add card validation changed event.
         */
        Frames.addEventHandler(
            Frames.Events.CARD_VALIDATION_CHANGED,
            function (event) {
                $token.value = '';
                $bin.value = '';
                if(Frames.isCardValid()) {
                    Frames.submitCard();
                }
            }
        );

        /**
         * Add card tokenized event.
         */
        Frames.addEventHandler(
            Frames.Events.CARD_TOKENIZED,
            function (event) {
                $bin.value = event.bin;
                $token.value = event.token;
                Frames.enableSubmitForm();
            }
        );

        Frames.addEventHandler(
            Frames.Events.PAYMENT_METHOD_CHANGED,
            paymentMethodChanged
        );

        function paymentMethodChanged(event) {
            var pm = event.paymentMethod;
            let container = document.querySelector(".icon-container.payment-method");

            if (!pm) {
                clearPaymentMethodIcon(container);
            } else {
                clearErrorIcon("card-number");
                showPaymentMethodIcon(container, pm);
            }
        }

        function clearPaymentMethodIcon(parent) {
            if (parent) parent.classList.remove("show");

            var logo = document.getElementById("logo-payment-method");
            logo.style.setProperty("display", "none");
        }

        function clearErrorIcon(el) {
            var logo = document.getElementById("icon-" + el + "-error");
            logo.style.removeProperty("display");
        }

        function showPaymentMethodIcon(parent, pm) {
            if (parent) parent.classList.add("show");

            var logo = document.getElementById("logo-payment-method");
            if (pm) {
                var name = pm.toLowerCase();
                var test = $imageDir + "card-icons/";
                logo.setAttribute("src", test + name + ".svg");
                logo.setAttribute("alt", pm || "payment method");
            }
            logo.style.removeProperty("display");
        }

        /**
         * Add form validation.
         *
         * @param      {Event}  e
         */
        $form.onsubmit = function(e) {
            e.preventDefault();
            if($token.value && !submitted) {
                submitted = true;
                $source.value = 'card';
                $form.submit();
            } else if($('.checkoutcom-saved-card').length > 0 || $('.checkoutcom-saved-card-mada').length > 0){
                if($('.checkoutcom-saved-card').is(':checked')){
                    $source.value = 'id';
                    $form.submit();
                }

                if($('.checkoutcom-saved-card-mada').is(':checked')) {
                    if($('#checkoutcom-cko-cvv').val().length > 0){
                        $source.value = 'id';
                        $form.submit();
                    }
                }
            }
        };
    }

</script>
<script type="text/javascript" async src="https://cdn.checkout.com/js/framesv2.min.js" onload="CheckoutcomFramesPay(document.getElementById('checkoutcom-card-form'));"></script>
{/literal}
<br>