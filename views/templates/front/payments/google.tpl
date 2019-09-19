<form name="{$module}" id="{$module}-google-form" action="{$link->getModuleLink($module, 'payment', [], true)|escape:'html'}" method="POST">
  <input id="{$module}-card-source" type="hidden" name="source" value="card" required>
	<input id="{$module}-card-token" type="hidden" name="token" value="" required>
</form>

{literal}
<script type="text/javascript">
</script>
{/literal}
<br>