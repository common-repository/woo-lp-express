<?php

/**
 * Register all shipping methods
 * @package    woocommerce-lp-express
 * @subpackage woocommerce-lp-express/includes/classes
 */

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
if(!class_exists('Woocommerce_Lp_Express_Shipping_Methods_Controller')) {
    class Woocommerce_Lp_Express_Shipping_Methods_Controller
    {
        private $plugin_name;
        private $version;
        private $options;

        public function __construct($plugin_name, $version)
        {
            $this->plugin_name = $plugin_name;
            $this->version = $version;

            $this->load_dependencies();
            $this->options = Woocommerce_Lp_Express_Options_Controller::get_terminal_options();
        }

        /**
         * Loads current class dependencies
         */
        private function load_dependencies()
        {
            require WCLP_PLUGIN_DIR . '/includes/classes/admin/class.' . $this->plugin_name . '-options-controller.php';
        }

        /**
         * Adds shipping methods to shipping methods array
         * @param $methods
         * @return array
         **/
        public function add_lp_express_courrier_shipping_method($methods)
        {
            $methods[] = 'WC_LP_Express_Courrier_Shipping_Method';
            return $methods;
        }

        /**
         * @param $methods
         * @return array
         */
        public function add_lp_express_post_office_shipping_method($methods)
        {
            $methods[] = 'WC_LP_Express_Post_Office_Shipping_Method';
            return $methods;
        }

        /**
         * @param $methods
         * @return array
         */
        public function add_lp_express_24_terminal_shipping_method($methods)
        {
            $methods[] = 'WC_LP_Express_24_Terminal_Shipping_Method';
            return $methods;
        }

        /**
         * @param $methods
         * @return array
         */
        public function add_lp_express_international_shipping_method($methods)
        {
            $methods[] = 'WC_LP_Express_International_Shipping_Method';
            return $methods;
        }

        /**
         * Initializes shipping methods classes
         **/
        public function lp_express_courrier_shipping_method_init()
        {
            require_once WCLP_PLUGIN_DIR . '/includes/classes/public/class.courrier-shipping-method.php';
        }

        public function lp_express_post_office_shipping_method_init()
        {
            require_once WCLP_PLUGIN_DIR . '/includes/classes/public/class.post-office-shipping-method.php';
        }

        public function lp_express_24_terminal_shipping_method_init()
        {
            require_once WCLP_PLUGIN_DIR . '/includes/classes/public/class.24-terminal-shipping-method.php';
        }

        public function lp_express_international_shipping_method_init()
        {
            require_once WCLP_PLUGIN_DIR . '/includes/classes/public/class.international-shipping-method.php';
        }

        /**
         * @return mixed
         */
        private static function get_options()
        {
            return Woocommerce_Lp_Express_Options_Controller::get_formatted_options();
        }

        /**
         * @return LpExpressApi
         */
        private static function get_client()
        {
            return new LpExpressApi(self::get_options()['options']);
        }

        /**
         * Terminal fields
         * @param $fields
         * @return array
         */
        public function register_terminal_fields($fields)
        {
            $fields['extra_fields'] = [
                'lp_express_terminals' => [
                    'type' => 'select',
                    'options' => $this->options,
                    'required' => false,
                    'label' => __('Pasirinkite terminalą: ', 'lp_express')
                ]
            ];

            return $fields;
        }

        /**
         * Render fields
         */
        public function display_terminal_field()
        {
            $checkout = WC()->checkout();
            echo '<div id="lp_express_terminal_field" style="display:none">';
            foreach ($checkout->checkout_fields['extra_fields'] as $key => $field) {
                woocommerce_form_field($key, $field, $checkout->get_value($key));
            }
            echo '</div>';
        }

        /**
         * Save fields
         */
        function save_custom_field_data($order_id, $posted)
        {
            $options = array_keys($this->options);

            if (isset($posted['lp_express_terminals']) && in_array($posted['lp_express_terminals'], $options)) {
                update_post_meta($order_id, 'lp_express_terminal_id', $posted['lp_express_terminals']);
            }
        }

        /**
         * Get available destinations
         * @return mixed
         */
        public static function get_available_destinations()
        {
            try {
                $available_destinations = self::get_client()->call("overseas_destinations", []);
            } catch (SoapFault $e) {
                error_log($e->getMessage());
            }

            return $available_destinations;
        }
    }
}