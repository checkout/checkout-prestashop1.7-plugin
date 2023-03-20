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
 * On document ready.
 */
$(document).ready(function () {
  if ( document.getElementById("checkoutcom-card-frame") !== null ) {
    var $frames = document.getElementById("checkoutcom-card-frame");
  }else{
    var $frames = document.getElementById("checkoutcom-multi-frame");
  }

  if ($frames) {
    var savecard = $frames.dataset.savecard;

    hideFrames(savecard);

    if ($(".checkoutcom-saved-card").length > 0) {
      $("input[type=radio][name=checkoutcom-saved-card]").change(function () {
        if (this.value == "new_card") {
          showFrames(savecard);
        } else {
          hideFrames(savecard);

          if (this.className == "checkoutcom-saved-card-mada") {
            $(".cvvVerification").show();
          } else {
            $(".cvvVerification").hide();
          }
        }
      });
    } else {
      showFrames(savecard);
    }
  }
});

function hideFrames(savecard) {
  if ( document.getElementById("checkoutcom-card-frame") !== null ) {
    var $frames = document.getElementById("checkoutcom-card-frame");
  }else{
    var $frames = document.getElementById("checkoutcom-multi-frame");
  }
  if($frames){
    $frames.style="display:none";
    
  }
  $("#checkoutcom-card-frame").hide();
  $(".cvvVerification").hide();

  if (savecard) {
    $(".save-card-check").hide();
  }
}

function showFrames(savecard) {

  if ( document.getElementById("checkoutcom-card-frame") !== null ) {
    var $frames = document.getElementById("checkoutcom-card-frame");
  }else{
    var $frames = document.getElementById("checkoutcom-multi-frame");
  }

  if (savecard) {
    $(".save-card-check").show();
  }
}
