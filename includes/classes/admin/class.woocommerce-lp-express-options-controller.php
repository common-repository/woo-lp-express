<?php

/**
 * Responsible for formatting and getting lp express options
 * @package    woocommerce-lp-express
 * @subpackage woocommerce-lp-express/includes/classes/admin
 */

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

if(!class_exists('Woocommerce_Lp_Express_Options_Controller')) {
    class Woocommerce_Lp_Express_Options_Controller
    {
        /**
         * The ID of the plugin
         **/
        private $plugin_name;

        /**
         * The version of the plugin
         **/
        private $version;

        /**
         * Woocommerce_Lp_Express_Options_Controller constructor.
         * @param $plugin_name
         * @param $version
         */
        public function __construct($plugin_name, $version)
        {
            $this->plugin_name = $plugin_name;
            $this->version = $version;
        }

        /**
         * Formatting options
         */
        public static function get_formatted_options()
        {
            if(get_option('wc_lp_express_api') != null && get_option('wc_lp_express_sender') != null) {
                $wc_lp_options = array_merge(get_option('wc_lp_express_api'), get_option('wc_lp_express_sender'));

                //Main options for lpexpress api
                $options['options'] = [
                    'hostname' => $wc_lp_options['wc_lp_domain'],
                    'partnerId' => $wc_lp_options['wc_lp_partnerid'],
                    'partnerPassword' => $wc_lp_options['wc_lp_partnerpassword'],
                    'rawOptions' => [
                        'trace' => true,
                        'connection_timeout' => 5,
                        'cache_wsdl' => WSDL_CACHE_DISK
                    ]
                ];

                //Additional options
                $options['customerId']      = $wc_lp_options['wc_lp_clientid'];
                $options['paymentPin']      = $wc_lp_options['wc_lp_paymentpin'];
                $options['adminPin']        = $wc_lp_options['wc_lp_adminpin'];
                $options['sender_name']     = $wc_lp_options['wc_lp_sendername'];
                $options['sender_phone']    = $wc_lp_options['wc_lp_senderphone'];
                $options['sender_email']    = $wc_lp_options['wc_lp_senderemail'];
                $options['sender_address']  = $wc_lp_options['wp_lp_senderaddress'];
                $options['sender_city']     = $wc_lp_options['wp_lp_sendercity'];
                $options['sender_zip']      = $wc_lp_options['wp_lp_senderzipcode'];
            }

            return $options;
        }

        /**
         * Get terminal fields
         * @return array|null
         */
        public static function get_terminal_options()
        {
            if(self::get_formatted_options()['options'] != null) {
                //Connect to lp express
                $client = new LpExpressApi(self::get_formatted_options()['options']);
                try {
                    //Get terminals
                    $response = $client->call('public_terminals', []);
                    if ($response) { //If response wasn't null
                        $terminals = [];
                        foreach ($response as $terminal) {
                            $terminals[$terminal->machineid] = $terminal->city . ' ' . $terminal->address . ' - ' . $terminal->name;
                        }
                        return $terminals; //return formatted terminals
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }

            return null;
        }

        /**
         * Clear trash international fixed price data
         */
        public function clear_int_fixed_data()
        {
            $countries_obj   = new WC_Countries();
            $callback        = 'wp_lp_fixed_international';
            $options         = get_option('wc_lp_express_sender_settings');

            //First clear all_ prefixed values
            $options['all_' . $callback]       = null;

            //clear other prefixed options
            foreach($countries_obj->__get('countries') as $code => $value) {
                $options[$code . '_' . $callback] = null;
            }

            update_option('wc_lp_express_sender_settings',$options);

            wp_die('cleared');
        }

        /**
         * Get woocommerce product categories
         */
        public function get_product_categories()
        {
            //Product categories
            $args = array(
                'taxonomy'   => "product_cat",
                'hide_empty' => false,
            );

            $product_categories = get_terms($args);

            echo json_encode($product_categories);
            wp_die();
        }

        /**
         * Get available lp express shipping methods
         */
        public function get_lpexpress_available()
        {
            $methodArray = array();

            //__toString() method returns JSON string
            if (!class_exists('WC_LP_Express_24_Terminal_Shipping_Method')) {
                require WCLP_PLUGIN_DIR . '/includes/classes/public/class.24-terminal-shipping-method.php';
            }

            if (!class_exists('WC_LP_Express_Courrier_Shipping_Method')) {
                require WCLP_PLUGIN_DIR . '/includes/classes/public/class.courrier-shipping-method.php';
            }

            if (!class_exists('WC_LP_Express_International_Shipping_Method')) {
                require WCLP_PLUGIN_DIR . '/includes/classes/public/class.international-shipping-method.php';
            }

            if (!class_exists('WC_LP_Express_Post_Office_Shipping_Method')) {
                require WCLP_PLUGIN_DIR . '/includes/classes/public/class.post-office-shipping-method.php';
            }

            array_push($methodArray, json_decode((string)new WC_LP_Express_24_Terminal_Shipping_Method()));
            array_push($methodArray, json_decode((string)new WC_LP_Express_Courrier_Shipping_Method()));
            array_push($methodArray, json_decode((string)new WC_LP_Express_International_Shipping_Method()));
            array_push($methodArray, json_decode((string)new WC_LP_Express_Post_Office_Shipping_Method()));

            echo json_encode(array_filter($methodArray, function($value) {
                return $value !== null;
            }));

            wp_die();
        }
    }
}