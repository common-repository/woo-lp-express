<?php

/**
* Running when plugin is activated
* @package woocommerce-lp-express
* @subpackage woocommerce-lp-express/includes/classes
**/
/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class Woocommerce_Lp_Express_Activator {
    /**
    * Plugin aktivation
    * Call: Woocomerce_Lp_Express_Activator::activate()
    **/
    public static function activate() {
        self::create_tabs();
    }

    /**
     * Adds settings tabs
     **/
    private static function create_tabs() {
        $tabController = new Woocommerce_Lp_Express_Tab_Controller();
        $tabController->add_tabs(new Woocommerce_Lp_Express_Tab('lp_express_admin_page', 'woocommerce-lp-express',"API nustatymai","api-options"));
        $tabController->add_tabs(new Woocommerce_Lp_Express_Tab('lp_express_admin_page', 'woocommerce-lp-express',"Siuntėjo informacija","sender-info"));
        $tabController->add_tabs(new Woocommerce_Lp_Express_Tab('lp_express_admin_page', 'woocommerce-lp-express',"Siuntų nustatymai","sender-settings"));
        $tabController->add_tabs(new Woocommerce_Lp_Express_Tab('lp_express_admin_page', 'woocommerce-lp-express',"Pagalba","help"));
    }
}