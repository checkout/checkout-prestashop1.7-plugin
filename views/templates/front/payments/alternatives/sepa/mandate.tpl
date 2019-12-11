<div class="sepa-mandate-card">
    <div class="sepa-card-header">
        <div class="sepa-card-header-text">
            <div class="sepa-card-title">
                <h3 style="font-weight: bold;">{l s='SEPA Direct Debit Mandate for single payment' mod='checkoutcom'}</h3>
            </div>
        </div>
    </div>
    <div class="sepa-mandate-content">
        <div class="sepa-creditor">
            <h2 style="margin: unset;">{l s='Creditor' mod='checkoutcom'}</h2>
            <h3 style="margin: unset; font-weight: bold; ">{$shop_name}</h3>
            <p style="margin: unset;" class="ng-star-inserted">{$shop_address1}</p>
            <p style="margin: unset;">{$shop_address2}</p>
            <p style="margin: unset;">{$shop_postcode}</p>
            <p style="margin: unset;">{$shop_country}</p>
            <br>
            <p style="margin: unset;" class="monospace">{l s='Creditor ID: ' mod='checkoutcom'}{$mandate_reference}</p>
        </div>
        <div class="sepa-debitor">
            <h2 style="margin: unset;">{l s='Debitor' mod='checkoutcom'}</h2>
            <h3 style="margin: unset; font-weight: bold; ">{$customer_firstname} {$customer_lastname}</h3>
            <div class="address" style="margin: unset;">
                <p style="margin: unset;" class="ng-star-inserted">{$customer_address1}</p>
                <p style="margin: unset;">{$customer_address2}</p>
                <p style="margin: unset;">{$customer_postcode} {$customer_city}</p>
                <p style="margin: unset;">{$customer_country}</p>
            </div>
            <br>
            <p class="monospace" style="margin: unset;" id="sepa-dd-bic"></p>
            <p class="monospace" style="margin: unset;" id="sepa-dd-iban"></p>
        </div>
    </div>
    <div class="sepa-par">
        <hr style="opacity: 0.3;">
        <p>{l s='By accepting this mandate form, you authorise (A) Checkout.com to send instructions to your bank to debit your account (B) your bank to debit your account in accordance with the instructions from Checkout.com.' mod='checkoutcom'}</p>
        <p>{l s='As part of your rights, you are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.' mod='checkoutcom'}</p>
        <div class="sepa-checkbox-container" id="sepa-checkbox-container">
            <label class="sepa-checkbox-layout" for="sepa-checkbox-input">
                <div class="sepa-checkbox-inner-container">
                    <input style="margin-right: 10px;" class="sepa-checkbox-input" type="checkbox" id="checkoutcom-sepa-accept-terms" name="accepted" required>
                    <h4 style="font-size: 12px;display: inline;">{l s='I accept the mandate for a single payment' mod='checkoutcom'}</h4>
                    <input type="hidden" name="id" value="{$mandate_src}" required>
                    <input type="hidden" name="customer_id" value="{$customer_id}" required>
                </div>
            </label>
        </div>
    </div>
    <div class="sepa-right">
        <hr style="opacity: 0.3;">
        <div class="sepa-card-footer">
            <div class="sepa-card-footer-text">
                <div class="sepa-footer-title">
                    {l s='Your rights regarding the above mandate are explained in a statement that you can obtain from your bank.' mod='checkoutcom'}
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.sepa-info {
    padding-top: 15px;
    display: table-cell;
    text-align: right;
}

.sepa-info label{
    padding-bottom: 10px;
}

.sepa-heading {
    text-align: left;
}

.sepa-mandate-card {
    box-shadow: 0 2px 1px -1px rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 1px 3px 0 rgba(0,0,0,.12);
    padding-top: 0px;
    margin-top: 20px;
}

.sepa-card-title h3 {
    display: block;
    font-size: 1.4em;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
}

.sepa-card-header {
    display: flex;
    flex-direction: row;
}

.sepa-card-header-text {
    margin: 0 16px;
    margin-top: 0px;
    margin-right: 16px;
    margin-bottom: 0px;
    margin-left: 16px;
}

.sepa-mandate-content {
    display: flex;
    flex-wrap: wrap;
}

.sepa-mandate-content h2{
    display: block;
    font-size: 1.5em;
    margin-block-start: 0.83em;
    margin-block-end: 0.83em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
}

.sepa-mandate-content h3{
    display: block;
    font-size: 1.17em;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
}

.sepa-mandate-content p {
    display: block;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
}

.sepa-creditor {
    flex: 1 0 auto;
    margin: 16px;
    font-size: smaller;
}

.sepa-creditor h2 {
    margin: unset;
    margin-top: unset;
    margin-right: unset;
    margin-bottom: unset;
    margin-left: unset;

    display: block;
    font-size: 1.5em;
    margin-block-start: 0.83em;
    margin-block-end: 0.83em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
}

.sepa-creditor h3 {
    margin: unset;
    margin-top: unset;
    margin-right: unset;
    margin-bottom: unset;
    margin-left: unset;

    display: block;
    font-size: 1.17em;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
}

.sepa-creditor p {
    display: block;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
}

.sepa-debitor {
    flex: 1 0 auto;
    margin: 16px;
    font-size: smaller;
}

.sepa-par p{
    display: block;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    margin: 16px;
}

.sepa-checkbox-container label {
    display: flex;
}

.sepa-checkbox-container {
    margin: 16px;
    padding-top: 10px;
}

.sepa-checkbox-inner-container {
    margin-right: 5px;
}

.sepa-footer-title {
    font-size: 11px;
    margin: 16px;
    padding-bottom: 16px;
    text-align: center;
    opacity: 0.7;
}
</style>