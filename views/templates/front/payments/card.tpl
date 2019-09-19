<form name="{$module}" id="{$module}-card-form" action="{$link->getModuleLink($module, 'payment', [], true)|escape:'html'}" method="POST">
	<div id="{$module}-card-frame" class="card-frame" data-key="{$CHECKOUTCOM_PUBLIC_KEY}" data-debug="{$debug}" data-lang="{$lang}" data-module="{$module}"></div>
  <input id="{$module}-card-source" type="hidden" name="source" value="card" required>
	<input id="{$module}-card-token" type="hidden" name="token" value="" required>
</form>

{literal}
<script type="text/javascript" src="https://cdn.checkout.com/js/framesv2.min.js"></script>
<script type="text/javascript">

  if(typeof Frames !== 'undefined') {

    const $frames = document.getElementById('checkoutcom-card-frame');
    const $input = document.getElementById('checkoutcom-card-token');

    Frames.init({
        publicKey: $frames.dataset.key || 'pk_test_10b309b8-904c-4db3-b79d-fb114ab15620', // @todo: remove my key
        debug: Boolean(+$frames.dataset.debug),
        localization: $frames.dataset.lang.toUpperCase(),
        //name: prestashop.customer.firstname + ' ' + prestashop.customer.lastname
      });

    Frames.addEventHandler(
      Frames.Events.CARD_TOKENIZATION_FAILED,
      function (event) {
        // Display error message @todo: display error
        $input.value = '';
      }
    );

    Frames.addEventHandler(
      Frames.Events.CARD_VALIDATION_CHANGED,
      function (event) {
        $input.value = '';
        if(Frames.isCardValid()) {
          Frames.submitCard();
        }
      }
    );

    Frames.addEventHandler(
      Frames.Events.CARD_TOKENIZED,
        function (event) {
            $input.value = event.token;
            Frames.enableSubmitForm();
        }
    );

  } else {
    // Hide pay by card option
  }

</script>
{/literal}
<br>