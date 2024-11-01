<?php

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

if(!class_exists('WC_LP_Express_24_Terminal_Shipping_Method')) {
    class WC_LP_Express_24_Terminal_Shipping_Method extends WC_Shipping_Method
    {
        /**
         * WC_LP_Express_24_Terminal_Shipping_Method constructor.
         * Adds LP Express 24 terminal shipping to WooCommerce shipping settings
         */
        public function __construct()
        {
            $this->id                   = 'lp_express_24_terminal_shipping_method';
            $this->method_title         = __('LP Express pristatymas į 24 savitarnos terminalą', 'lp_express');
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled              = $this->get_option('enabled');
            $this->title                = $this->get_option('title');

            add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
        }

        /**
         * Get method string if enabled
         * @return string
         */
        public function __toString()
        {
            $currentMethod = WC()->shipping->load_shipping_methods()[$this->id];

            if($currentMethod->enabled == 'yes') {
                return json_encode(array($currentMethod->id => $currentMethod->title));
            }

            return '';
        }

        /**
         * Adds form fields to WooCommerce LP Express 24 terminal shipping settings
         */
        public function init_form_fields()
        {
            $this->form_fields = [
                    'enabled'     => [
                        'title'   => __('Įjungti/Išjungti', 'lp_express'),
                        'type'    => 'checkbox',
                        'label'   => __('Įjungti LP Express pristatymą į 24 savitarnos terminalą', 'lp_express'),
                        'default' => 'no'
                    ],
                    'title'                 => [
                        'title'             => __('Būdo pavadinimas', 'lp_express'),
                        'type'              => 'text',
                        'description'       => __('Tai kontroliuoja pavadinimą kūrį matys lankytojas čekio puslapyje ', 'lp_express'),
                        'default'           => __('LP Express pristatymas į 24 savitarnos terminalą', 'lp_express'),
                    ]
            ];
        }

        /**
         * Ckecks if shipping method is available
         */
        public function is_available($package)
        {
            if ($this->is_enabled() == false) {
                return false;
            }

            if (WC()->customer->get_shipping_country() == "LT"
                && get_option('wc_lp_express_sender_settings') == null) {
                return true;
            }

            if (WC()->customer->get_shipping_country() == "LT" &&
                get_option('wc_lp_express_sender_settings')['wp_lp_manifestgen'] == 'manual') {
                return true;
            }

            if(WC()->customer->get_shipping_country() == "LT") {
                //Check if not exceeds weight and size limit
                $length = $weight = $width = $height = 0;
                foreach ($package['contents'] as $item_id => $values) {
                    $_product = $values['data'];

                    if($_product->get_length() == null || $_product->get_width() == null || $_product->get_height() == null
                        || $_product->get_weight() == null) {
                        return false;
                    }

                    $length += $_product->get_length() * $values['quantity'];
                    $width  += $_product->get_width()  * $values['quantity'];
                    $height += $_product->get_height() * $values['quantity'];
                    $weight += $_product->get_weight() * $values['quantity'];

                    //Check if not exceeds size and weight limit
                    if($length > 35 || $width > 74.5 || $height > 61 || $weight > 30) return false;
                }

                //If shipping rule returned false that means we need to disable this method
                $shipping_rules = Woocommerce_Lp_Express_Shipping_Rules_Controller::shipping_rule_availability($this->id);
                if (!$shipping_rules) {
                    return false;
                }

                return true;
            }

            return false;
        }

        /**
         * Get client lp express client
         * @return LpExpressApi
         */
        private function get_client() {
            return new LpExpressApi(Woocommerce_Lp_Express_Options_Controller::
                                            get_formatted_options()['options']);
        }

        /**
         * Get terminal fixed price
         * @return mixed
         */
        public function get_fixed_price() {
            return get_option('wc_lp_express_sender_settings')['wp_lp_fixed_terminal'];
        }

        /**
         * Calculates shipping costs
         */
        public function calculate_shipping($package = array())
        {
            if ($this->get_fixed_price() == null) {
                //Dimensions
                $length = $width = $height = $weight = $qty = null;

                //Response
                $response   = null;

                //Cost
                $cost       = null;

                //Summing up all products in checkout
                foreach ($package['contents'] as $item => $value) {

                    $_product = $value['data'];

                    $length += $_product->get_length() * $value['quantity'];
                    $width  += $_product->get_width()  * $value['quantity'];
                    $height += $_product->get_height() * $value['quantity'];
                    $weight += $_product->get_weight() * $value['quantity'];
                    $qty += $value['quantity'];
                }

                //Request data for shipping cost calculation
                $shipping_type = get_option('wc_lp_express_sender_settings')['wp_lp_terminal_shipping_type'];
                $shipping_type = $shipping_type != null ? $shipping_type : 'HC';
                $request_data = [
                    'product_type' => $shipping_type,
                    'dimension_x'  => $length   * 10, //cm to mm
                    'dimension_y'  => $width    * 10,
                    'dimension_z'  => $height   * 10
                ];

                //LpExpress request
                $client = $this->get_client();
                try {
                    $response = $client->call('get_product_by_dimensions', $request_data);
                    $cost = $response->price != null ? $response->price : null;
                } catch (SoapFault $e) {
                    error_log($e->getMessage());
                    return false;
                }
            } else $cost = $this->get_fixed_price();

            //If price is controlled by rules
            Woocommerce_Lp_Express_Shipping_Rules_Controller::shipping_costs_controls($this->id, $cost);

            // Returns shipping costs
            $this->add_rate(array(
                'id'    => $this->id,
                'label' => $this->title,
                'cost'  => $cost
            ));
        }
    }
}