<form name="{$module}" id="{$module}-google-form" action="{$link->getModuleLink($module, 'payment', [], true)|escape:'html'}" method="POST">
	<input id="{$module}-card-source" type="hidden" name="source" value="card" required>
	<input id="{$module}-card-token" type="hidden" name="token" value="" required>

	<input type="hidden" id="cko-google-signature" name="cko-google-signature" value="" />
    <input type="hidden" id="cko-google-protocolVersion" name="cko-google-protocolVersion" value="" />
    <input type="hidden" id="cko-google-signedMessage" name="cko-google-signedMessage" value="" />
</form>

{literal}
<script type="text/javascript">
</script>
{/literal}
<br>