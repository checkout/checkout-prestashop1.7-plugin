<form name="{$module}" id="{$module}-card-form" action="{$link->getModuleLink($module, 'payment', [], true)|escape:'html'}" method="POST">
	<div id="{$module}-card-frame" class="card-frame" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-debug="{$debug}" data-lang="{$lang}" data-module="{$module}"></div>
  <input id="{$module}-card-source" type="hidden" name="source" value="card" required>
	<input id="{$module}-card-token" type="hidden" name="token" value="" required>
  <input id="{$module}-card-bin" type="hidden" name="bin" value="">
</form>

{literal}
<script type="text/javascript" src="https://cdn.checkout.com/js/framesv2.min.js"></script>
<script type="text/javascript">

  if(typeof Frames !== 'undefined') {

    const $frames = document.getElementById('checkoutcom-card-frame');
    const $token = document.getElementById('checkoutcom-card-token');
    const $bin = document.getElementById('checkoutcom-card-bin');
    const $cForm = document.getElementById('checkoutcom-card-form');

    /**
     * Initialize frames.
     */
    Frames.init({
        publicKey: $frames.dataset.key,
        debug: Boolean(+$frames.dataset.debug),
        localization: $frames.dataset.lang.toUpperCase()
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

    /**
     * Add form validation.
     *
     * @param      {Event}  e
     */
    $cForm.onsubmit = function(e) {
      e.preventDefault();
      if($token.value) {
        $cForm.submit();
      }
    };

  } else {
    // Hide pay by card option
  }

</script>
{/literal}
<br>