<p>
    Please wait while we process your order ........
</p>
<input type="hidden" name="cko_cc_token" id="cko-cc-token" value="{$simulateToken}"/>
<input type="hidden" name="cko_cc_email" id="cko-cc-email" value="{$simulateEmail}" />
<script type="text/javascript">
   $(function(){
       $('[name^="checkoutapipayment_form"]').trigger('submit');
   })
</script>



