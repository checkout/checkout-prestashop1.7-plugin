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
  $("#checkoutcom-card-frame").hide();
  $(".cvvVerification").hide();

  if (savecard) {
    $(".save-card-check").hide();
  }
}

function showFrames(savecard) {
  $("#checkoutcom-card-frame").show();

  if (savecard) {
    $(".save-card-check").show();
  }
}
