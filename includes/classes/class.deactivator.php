<?php

/**
* Deactivation class
* @package woocomerce-lp-express
* @subpackage woocomerce-lp-express/includes/classes
**/

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
class Woocommerce_Lp_Express_Deactivator {
    /**
    * Plugins deactivation
    * Call: Woocomerce_Lp_Express_Activator::deactivate()
    **/
    public static function deactivate() {
        self::delete_all_data();
    }
	
    /**
     * Deletes all data
     */
	private static function delete_all_data(){
        //delete_option("lpx_base_menu");
        //delete_option('lpx_shipping_costs');
        //delete_option('lpx_label_identcode');
        delete_option('wc_lp_express_sender_settings');
	}
}