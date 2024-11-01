<?php
/**
 * Responsible for loading tab content with ajax
 * @package    woocommerce-lp-express
 * @subpackage woocommerce-lp-express/includes/classes/admin
 */

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

if(!class_exists('Woocommerce_Lp_Express_Tab_Content_Controller')) {
    class Woocommerce_Lp_Express_Tab_Content_Controller
    {
        /**
         * @var
         * Returns plugin name
         */
        private $plugin_name;

        /**
         * @var
         * Returns plugin version
         */
        private $version;

        /**
         * Woocommerce_Lp_Express_Tab_Content_Controller constructor.
         * @param $plugin_name
         * @param $version
         */
        public function __construct($plugin_name, $version)
        {
            $this->plugin_name = $plugin_name;
            $this->version = $version;
        }

        /**
         * Includes tab content from template file given by ajax
         */
        public function admin_tab_content()
        {
            //Tab name from ajax request
            $tabLocation = $_POST['pluginName'];

            //Don't allow use any wrong symbols
            $filteredTabName = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['id']);

            //Generate view template name
            $fileName = $this->plugin_name . "-admin-view-" . $filteredTabName . ".php";

            //Generate view template full path to file
            $filePath = get_home_path() . '/wp-content/plugins/' . $tabLocation . '/views/admin/' . $fileName;

            //Check if file exists
            if (file_exists($filePath))
                require_once $filePath; //include the file

            //Don't return 0 at the end of response
            wp_die();
        }
    }
}