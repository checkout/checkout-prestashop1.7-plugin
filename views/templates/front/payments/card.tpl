{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<section>
  <div id="frames" class="frames-container" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-debug="{$debug}" data-theme="{$CHECKOUTCOM_CARD_FORM_THEME}" data-lang="{$lang}"></div>
  <br>
</section>


<script type="text/javascript" src="https://cdn.checkout.com/js/frames.js" asyc></script>
{literal}
<script type="text/javascript">


  const $frames = document.getElementById('frames');

  console.log($frames.dataset);

	Frames.init({
      publicKey: $frames.dataset.key || 'pk_test_10b309b8-904c-4db3-b79d-fb114ab15620', // @todo remove my key
      containerSelector: '.frames-container',
      theme: $frames.dataset.theme,
      debugMode: $frames.dataset.debug,
      //localisation:"{$config_configuration}",

      // customerName:'',
      // billingDetails:{},

      cardValidationChanged: function() {
        // if all fields contain valid information, the Pay now
        // button will be enabled and the form can be submitted
        // payNowButton.disabled = !Frames.isCardValid();
      },
      cardSubmitted: function() {

        // payNowButton.disabled = true;
        // display loader
        //
      },
      cardTokenised: function(event) {
        // var cardToken = event.data.cardToken;
        // Frames.addCardToken(paymentForm, cardToken)
        // paymentForm.submit()
      },
      cardTokenisationFailed: function(event) {
        // catch the error
      }
    });


</script>
{/literal}