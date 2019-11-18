<script>

    /**
     * On document ready.
     */
    $(document).ready( function () {
        var is_capture = "{$is_capture}" ? true : false;

        // Show refund buttons only when payment is captured
        if (is_capture == true) {
            jQuery('#desc-order-standard_refund').show();
            jQuery('#desc-order-partial_refund').show();
        } else {
            jQuery('#desc-order-standard_refund').hide();
            jQuery('#desc-order-partial_refund').hide();
        }
    });
</script>