<div class="checkoutapi-wrapper">
    {if $respond}
        <div class="message state-{$respond.status}">
            <span>{$respond.message}</span>
        </div>
    {/if}

    <a href="https://www.checkout.com/" class="checkoutapi-logo" target="_blank"><img src="https://www.checkout.com/static/img/checkout-logo/logo.svg" alt="Checkout.com" border="0" style="width: 700px;"/></a>

    <div class="setting">
        <h3 class="setting-header"> {l s='Setting for Checkout.com Gateway 3.0' mod='checkoutAPI'}</h3>

        <form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
            <ul class="fields-set">
                <li class="field">
                    <label for="checkoutapi_test_mode">
                        <span>Production Mode<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <select name="checkoutapi_test_mode" class="input-txt required" id="checkoutapi_test_mode" required>
                            <option value="sandbox" {if $CHECKOUTAPI_TEST_MODE =='sandbox'}selected{/if} >Sandbox</option>
                            <option value="live" {if $CHECKOUTAPI_TEST_MODE =='live'}selected{/if} >Live</option>
                        </select>
                    </div>
                </li>
                <li class="field">
                    <label for="">
                        <span>Secret key<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" name="checkoutapi_secret_key" id="checkoutapi_secret_key" class="input-txt
                        required" required  value="{$CHECKOUTAPI_SECRET_KEY}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="">
                        <span>Public key<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" name="checkoutapi_public_key" id="checkoutapi_public_key" class="input-txt
                        required" required  value="{$CHECKOUTAPI_PUBLIC_KEY}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_integration_type">
                        <span>Integration Type</span>
                    </label>
                    <div class="wrapper-field">
                        <div class="wrapper-field">
                            <select name="checkoutapi_integration_type" class="input-txt required" id="checkoutapi_integration_type" required>
                               <!--  <option value="pci"  {if $CHECKOUTAPI_INTEGRATION_TYPE ==pci}selected{/if}>PCI</option> -->
                                <option value="js" {if $CHECKOUTAPI_INTEGRATION_TYPE ==js}selected{/if}>Checkout Js</option>
                                <!-- <option value="hosted" {if $CHECKOUTAPI_INTEGRATION_TYPE ==hosted}selected{/if}>Hosted</option>
                                <option value="frames" {if $CHECKOUTAPI_INTEGRATION_TYPE ==frames}selected{/if}>Frames</option> -->
                            </select>
                        </div>
                    </div>
                </li>
                {*<li class="field">*}
                    {*<label for="checkoutapi_is_3d">*}
                        {*<span>Is 3d?</span>*}
                    {*</label>*}
                    {*<div class="wrapper-field">*}
                        {*<div class="wrapper-field">*}
                            {*<select name="checkoutapi_is_3d" class="input-txt required" id="checkoutapi_is_3d" required>*}
                                {*<option value="0"  {if $CHECKOUTAPI_IS3d ==0}selected{/if}>No</option>*}
                                {*<option value="1" {if $CHECKOUTAPI_IS3d ==1}selected{/if}>Yes</option>*}
                            {*</select>*}
                        {*</div>*}
                    {*</div>*}
                {*</li>*}
                <li class="field">
                    <label for="checkoutapi_is_3d">
                        <span>Is 3d?</span>
                    </label>
                    <div class="wrapper-field">
                        <div class="wrapper-field">
                            <select name="checkoutapi_is_3d" class="input-txt required" id="checkoutapi_is_3d" required>
                                <option value="0"  {if $CHECKOUTAPI_IS_3D ==0}selected{/if}>No</option>
                                <option value="1" {if $CHECKOUTAPI_IS_3D ==1}selected{/if}>Yes</option>
                            </select>
                        </div>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_payment_action">
                        <span>Payment Action<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <select name="checkoutapi_payment_action" id="checkoutapi_payment_action"
                                class="input-txt required" required >
                            <option value="N" {if $CHECKOUTAPI_PAYMENT_ACTION =='N'}selected{/if} >Authorize only</option>
                            <option value="Y" {if $CHECKOUTAPI_PAYMENT_ACTION =='Y'}selected{/if} >Authorize & Capture</option>
                        </select>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_autocapture_delay">
                        <span>Auto capture time <em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="number" step="any" class="input-txt required" required name="checkoutapi_autocapture_delay"
                                id="checkoutapi_autocapture_delay" value="{$CHECKOUTAPI_AUTOCAPTURE_DELAY}"/>
                    </div>
                </li>
              <!-- <li class="field">
                    <label for="">
                        <span>Card type (PCI only)</span>
                    </label>
                    <div class="wrapper-field">
                        <ul class="card-type-list">
                            {foreach from=$cardtype item='card'}
                                <li class="card {$card.id}-carttype">
                                    <label for="cardType[{$card.id}]">
                                        <input type="checkbox" name="cardType[{$card.id}]"
                                               id="cardType[{$card.id}]"
                                               class="card-txt input-txt {if $card.selected}selected{/if}"
                                               {if $card.selected}checked="checked"{/if} value="1"/>
                                        <span style="background-image:url({$card.path})" class="{$card.id}-class {if $card.selected}selected{/if}">
                                        </span>
                                    </label>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </li> -->
                {*<li class="field">*}
                    {*<label for="checkoutapi_hold_review_os">*}
                        {*<span>Order status:  "Hold for Review"<em>*</em></span>*}
                    {*</label>*}
                    {*<div class="wrapper-field">*}
                        {*<select id="checkoutapi_hold_review_os" name="checkoutapi_hold_review_os" class="input-txt required">*}
                            {*// Hold for Review order state selection*}
                            {*{foreach from=$order_states item='os'}*}
                                {*<option value="{if $os.id_order_state|intval}" {((int)$os.id_order_state == $CHECKOUTAPI_HOLD_REVIEW_OS)} selected{/if}>*}
                                    {*{$os.name|stripslashes}*}
                                {*</option>*}
                            {*{/foreach}*}
                        {*</select>*}
                    {*</div>*}
                {*</li>*}
                <li class="field">
                    <label for="checkoutapi_gateway_timeout">
                        <span>Gateway timeout</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt required" required name="checkoutapi_gateway_timeout"
                               id="checkoutapi_gateway_timeout" value="{$CHECKOUTAPI_GATEWAY_TIMEOUT}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_save_card">
                        <span>Save Card<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <select name="checkoutapi_save_card" class="input-txt required" id="checkoutapi_save_card" required>
                            <option value="no" {if $CHECKOUTAPI_SAVE_CARD =='no'}selected{/if} >No</option>
                            <option value="yes" {if $CHECKOUTAPI_SAVE_CARD =='yes'}selected{/if} >Yes</option>
                        </select>
                    </div>
                </li>

                <h3 class="setting-header"> {l s='Advanced setting for Hosted and Checkout Js solution' mod='checkoutAPI'}</h3>
                <li class="field">
                    <label for="checkoutapi_payment_mode">
                        <span>Payment Mode</span>
                    </label>
                    <div class="wrapper-field">
                        <div class="wrapper-field">
                            <select name="checkoutapi_payment_mode" class="input-txt required" id="checkoutapi_payment_mode" required>
                                <option value="cards"  {if $CHECKOUTAPI_PAYMENT_MODE ==cards}selected{/if}>Cards</option>
                                <option value="localpayments" {if $CHECKOUTAPI_PAYMENT_MODE ==localpayments}selected{/if}>Local Payment</option>
                                <option value="mixed" {if $CHECKOUTAPI_PAYMENT_MODE ==mixed}selected{/if}>Mixed</option>
                            </select>
                        </div>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_title">
                        <span>Title</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt" name="checkoutapi_title"
                               id="checkoutapi_title" value="{$CHECKOUTAPI_TITLE}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_logo_url">
                        <span>Logo url</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt"  name="checkoutapi_logo_url"
                               id="checkoutapi_logo_url" value="{$CHECKOUTAPI_LOGO_URL}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_theme_color">
                        <span>Theme color</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt" name="checkoutapi_theme_color"
                               id="checkoutapi_theme_color" value="{$CHECKOUTAPI_THEME_COLOR}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_icon_color">
                        <span>Icon color</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt" name="checkoutapi_icon_color"
                               id="checkoutapi_icon_color" value="{$CHECKOUTAPI_ICON_COLOR}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_button_color">
                        <span>Button color</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt" name="checkoutapi_button_color"
                               id="checkoutapi_button_color" value="{$CHECKOUTAPI_BUTTON_COLOR}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="checkoutapi_currency_code">
                        <span>Widget currency format</span>
                    </label>
                    <div class="wrapper-field">
                        <select name="checkoutapi_currency_code" class="input-txt" id="checkoutapi_currency_code">
                                <option value= "true"  {if $CHECKOUTAPI_CURRENCY_CODE == 'true'}selected{/if}>Code</option>
                                <option value="false" {if $CHECKOUTAPI_CURRENCY_CODE =='false'}selected{/if}>Symbol</option>
                        </select>
                    </div>
                </li>

                <!-- <h3 class="setting-header"> {l s='Advanced setting for Frames Js' mod='checkoutAPI'}</h3>
                <li class="field">
                    <label for="checkoutapi_theme">
                        <span>Theme</span>
                    </label>
                    <div class="wrapper-field">
                        <div class="wrapper-field">
                            <select name="checkoutapi_theme" class="input-txt required" id="checkoutapi_theme">
                                <option value="standard"  {if $CHECKOUTAPI_THEME ==standard}selected{/if}>Standard</option>
                                <option value="simple" {if $CHECKOUTAPI_THEME ==simple}selected{/if}>Simple</option>
                            </select>
                        </div>
                    </div>
                </li>

                <li class="field">
                    <label for="checkoutapi_custom_css">
                        <span>Custom Css</span>
                    </label>
                    <div class="wrapper-field" >
                        <p><textarea rows="5" cols="50" name="checkoutapi_custom_css" id="checkoutapi_custom_css" >{$CHECKOUTAPI_CUSTOM_CSS}</textarea></p>
                    </div>
                </li> -->

                <li class="action">
                    <div class="wrapper-field">
                        <button name="submitPayment" type="submit" >
                            <span><span>Update settings</span></span>
                        </button>
                    </div>
                </li>
            </ul>
        </form>
    </div>
</div>