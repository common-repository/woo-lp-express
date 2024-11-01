jQuery("document").ready(function() {

    var shipping_field = jQuery("#lp_express_terminal_field");

    //Catch woocomerce shipping form review complete and add terminal selection to shipping method
    jQuery(document).ajaxComplete(function(e,xhr,sett) {
        var axaj_response = xhr.responseText;

        var shipping_method = jQuery(":radio[value=lp_express_24_terminal_shipping_method]");

        if(axaj_response.indexOf('.woocommerce-checkout-review-order-table') > -1
                && shipping_method.prop("checked")){

            //Append terminal to shipping option
            shipping_method.parent().append(shipping_field.html());
            shipping_field.remove();
            jQuery('#lp_express_terminals').select2();
        }
    });


});
