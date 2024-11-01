<?php

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

/**
 * On order payment complete
 * Executed when payment is complete
 */
add_action('woocommerce_thankyou', function( $order_id ) {
    if(get_option('wc_lp_express_sender_settings')['wp_lp_labelgen'] == 'auto') {
        Woocommerce_Lp_Express_Admin::generate_shipping_label($order_id);
    }
});

/**
 * Clear shipping rates cache order review
 */
add_filter('woocommerce_checkout_update_order_review', function () {
    $packages = WC()->cart->get_shipping_packages();
    foreach ($packages as $key => $value) {
        $shipping_session = "shipping_for_package_$key";
        unset(WC()->session->$shipping_session);
    }
});

/**
 * Clear shipping rates cache car view
 */
add_filter('woocommerce_check_cart_items', function () {
    $packages = WC()->cart->get_shipping_packages();
    foreach ($packages as $key => $value) {
        $shipping_session = "shipping_for_package_$key";
        unset(WC()->session->$shipping_session);
    }
});