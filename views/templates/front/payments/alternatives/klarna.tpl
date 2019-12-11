<form name="{$module}" id="{$module}-{$key}-form" action="{$link->getModuleLink($module, 'placeorder', [], true)|escape:'html'}" method="POST">
    <div id="{$module}-{$key}-frame" data-url="{$link->getModuleLink($module, 'klarna', [], true)|escape:'html'}" data-module="{$module}"></div>
    <input id="{$module}-{$key}-source" type="hidden" name="source" value="{$key}" required>
    <input id="{$module}-{$key}-auth-token" type="hidden" name="authorization_token" value="" required>
</form>
{literal}
<script type="text/javascript" src="https://x.klarnacdn.net/kp/lib/v1/api.js"></script>
<script type="text/javascript">

    const $kForm = document.getElementById('checkoutcom-klarna-form');
    const $kToken = document.getElementById('checkoutcom-klarna-auth-token');

    var k = {
        /**
         * Remove Klarna elements.
         */
        remove: function() {
            // $("#klarna").remove();
            // $("#body-klarna").remove();
        },
        /**
         * Load Klarna object.
         *
         * @param      {Obj}  data     The data
         * @param      {Array}  methods  The methods
         */
        load: function(data, methods) {
console.log(data);
            var self = this;
            try {
              console.log(data);
                Klarna.Payments.init({client_token: data.client_token}); // Initialize Klarna
                Klarna.Payments.load({
                    container: "#checkoutcom-klarna-frame",
                    payment_method_categories: methods,
                    instance_id: "checkoutcom-klarna-payments-instance"
                },
                {
                    purchase_country:   prestashop.customer.addresses[data.id_address_invoice].country_iso,
                    purchase_currency:  prestashop.currency.iso_code,
                    locale:             prestashop.language.locale,
                    order_amount:       +data.order_amount,
                    order_tax_amount:   +data.order_tax_amount,
                    order_lines:        data.order_lines,
                    billing_address:    {
                        given_name:     prestashop.customer.addresses[data.id_address_invoice].firstname,
                        family_name:    prestashop.customer.addresses[data.id_address_invoice].lastname,
                        email:          prestashop.customer.email,
                        //title:          data.billing.email,
                        street_address: prestashop.customer.addresses[data.id_address_invoice].address1,
                        street_address2:prestashop.customer.addresses[data.id_address_invoice].address2,
                        postal_code:    prestashop.customer.addresses[data.id_address_invoice].postcode,
                        city:           prestashop.customer.addresses[data.id_address_invoice].city,
                        region:         prestashop.customer.addresses[data.id_address_invoice].state,
                        phone:          prestashop.customer.addresses[data.id_address_invoice].phone,
                        country:        prestashop.customer.addresses[data.id_address_invoice].country_iso
                    }
                },
                function (response) {
console.log(response);
                    if (!response.show_form) {
                        self.remove();
                    }
                });

            } catch(er) {
console.log(er);
                this.remove();
            }

        },
        /**
         * Get methods from source.
         *
         * @param      {Array}  [methods=[]]  The methods
         * @return     {Array}   The methods.
         */
        getMethods: function(methods = []) {

            var list = [];
                methods.forEach(function(el) {
                    list.push(el.identifier);
                });

            return list;

        }
    };



    $kForm.onsubmit = function(e) {
        e.preventDefault();
        console.log('submit klarna');

        Klarna.Payments.authorize({ instance_id: "checkoutcom-klarna-payments-instance",
                                    auto_finalize: true},
                                  {},
                                  function (response) {
                                    $kToken.value = response.authorization_token;
                                    $kForm.submit();
                                  });
    };


</script>
{/literal}
<br>