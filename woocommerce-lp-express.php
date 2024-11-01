<?php
/*
Plugin Name: WooCommerce LP Express
Plugin URI: https://www.noriusvetaines.lt
Description: WooCommerce LP Express shipping methods.
Version: 2.0.4
Author: <a href="https://www.noriusvetaines.lt" target="_blank">www.noriusvetaines.lt</a>
*/

/**
* Don't allow to call this file directly
**/
if(!defined('ABSPATH')) {
    die;
}

/**
* Plugin path 
**/
define("WCLP_PLUGIN_DIR", __DIR__);
define("WCLP_PLUGIN_URL", plugin_dir_url( __FILE__ ));
define("WCLP_LABELS_DIR", wp_upload_dir()['basedir'] . '/woocommerce-lp-express/labels');
define('WCLP_LABELS_URL', wp_upload_dir()['baseurl'] . '/woocommerce-lp-express/labels');
define("WCLP_MANIFESTS_DIR", wp_upload_dir()['basedir'] . '/woocommerce-lp-express/manifests');
define('WCLP_MANIFESTS_URL', wp_upload_dir()['baseurl'] . '/woocommerce-lp-express/manifests');

/**
* Activation hook
* Documented in: includes/classes/class.activator.php 
**/
function activate_woocommerce_lp_express() {
    require_once plugin_dir_path(__FILE__) . 'includes/classes/class.activator.php';
    Woocommerce_Lp_Express_Activator::activate();
}

/**
* Deactivation hook
* Documented in: includes/classes/class.deactivator.php 
**/
function deactivate_woocommerce_lp_express() {
    require_once plugin_dir_path(__FILE__) . 'includes/classes/class.deactivator.php';
    Woocommerce_Lp_Express_Deactivator::deactivate();
}

/**
 * Register activation/deactivation hooks
 */
register_activation_hook(__FILE__, 'activate_woocommerce_lp_express');
register_deactivation_hook(__FILE__, 'deactivate_woocommerce_lp_express');

/**
 * Include main class
 */
require plugin_dir_path(__FILE__) . 'includes/classes/class.woocommerce-lp-express.php';

/**
 * Run the plugin
 */
function run_woocommerce_lp_express() {
    $woocomerce_lp_express = new Woocommerce_Lp_Express();
    $woocomerce_lp_express->run();

}

run_woocommerce_lp_express();