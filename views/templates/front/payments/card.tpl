<form name="{$module}" id="{$module}-card-form" action="{$link->getModuleLink($module, 'payment', [], true)|escape:'html'}" method="POST">
	<div id="{$module}-card-frame" class="{$module}-frames-container" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-debug="{$debug}" data-theme="{$CHECKOUTCOM_CARD_FORM_THEME}" data-lang="{$lang}" data-module="{$module}"></div>
  <input id="{$module}-card-source" type="hidden" name="source" value="card" required>
	<input id="{$module}-card-token" type="hidden" name="token" value="" required>
</form>

{literal}
<script type="text/javascript" src="https://cdn.checkout.com/js/frames.js"></script>
<script type="text/javascript">

  if(typeof Frames !== 'undefined') {

    Frames.removeAllEventHandlers(Frames.Events.CARD_VALIDATION_CHANGED);
    Frames.removeAllEventHandlers(Frames.Events.CARD_TOKENISED);
    Frames.removeAllEventHandlers(Frames.Events.FRAME_ACTIVATED);

    const $cForm = document.getElementById('checkoutcom-card-form');
    const $frames = document.getElementById('checkoutcom-card-frame');
    const $input = document.getElementById('checkoutcom-card-token');

    Frames.init({
        publicKey: $frames.dataset.key || 'pk_test_10b309b8-904c-4db3-b79d-fb114ab15620', // @todo: remove my key
        containerSelector: '.' + $frames.dataset.module + '-frames-container',
        theme: $frames.dataset.theme,
        debugMode: $frames.dataset.debug,
        localisation: $frames.dataset.lang,

        // customerName:'',
        // billingDetails:{},

        cardValidationChanged: function() {$input.value = '';},
        cardSubmitted: function() {},
        cardTokenised: function(event) {$input.value = event.data.cardToken; $cForm.submit();},
        cardTokenisationFailed: function(event) {
          // Display error message @todo: display error
          $input.value = '';
        }
      });

    $cForm.onsubmit = function(e) {
      e.preventDefault();

      if(Frames.isCardValid()) {
        Frames.submitCard();
      }

      Frames.unblockFields();

    };

  } else {
    // Hide pay by card option
  }

</script>
{/literal}
<br>