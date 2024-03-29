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

    var $token = document.getElementById('checkoutcom-card-token');
    var $bin = document.getElementById('checkoutcom-card-bin');
    var $source = document.getElementById('checkoutcom-card-source');
    var $imageDir = $frames.dataset.imagedir;
    var submitted = false; // Prevent multiple submit
    $frames.insertAdjacentHTML('beforeend',
    '<div style="display:none" id="toolmsg">Select your preferred card brand <a class="" data-toggle="tooltip" data-placement="top" title="">i <span>Your card has two brands, and you can choose your preferred one for this payment. If you do not, then the merchant preferred brand will be selected</span></a></div>')
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
        },
        schemeChoice:true,
        modes: [ Frames.modes.FEATURE_FLAG_SCHEME_CHOICE],
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
     * Add card bin changed event
     */

    Frames.addEventHandler(Frames.Events.CARD_BIN_CHANGED, function ( event ) {

        // Show hide co badged label.
        if ( event?.isCoBadged ) {
            document.getElementById('toolmsg').classList.add("show");
            document.getElementById('toolmsg').style.removeProperty("display");
        }
        else{
            document.getElementById('toolmsg').style="display:none";
        }
  
    })

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

        if (!pm && document.getElementById("checkoutcom-multi-frame") !== null) {
            clearPaymentMethodIcon(container);
        } else if(document.getElementById("checkoutcom-multi-frame") !== null) {
            clearErrorIcon("card-number");
            showPaymentMethodIcon(container, pm);
        }
        
        if(event.paymentMethod== "Cartes Bancaires"){
            document.getElementById('toolmsg').classList.add("show");
            document.getElementById('toolmsg').style.removeProperty("display");
        }
        else{
            document.getElementById('toolmsg').style="display:none";
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