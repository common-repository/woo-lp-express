<?php
/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
if(!class_exists('WC_LP_Express_International_Shipping_Method')) {
    class WC_LP_Express_International_Shipping_Method extends WC_Shipping_Method
    {
        /**
         * Adds LP Express international shipping to WooCommerce shipping settings
         */
        public function __construct()
        {
            $this->id                   = 'lp_express_international_shipping_method';
            $this->method_title         = __('LP Express pristatymas ne Lietuvoje', 'woocommerce');
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
         * Adds form fields to WooCommerce LP Express international shipping settings
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Įjungti/Išjungti', 'lp_express'),
                    'type' => 'checkbox',
                    'label' => __('Įjungti LP Express pristatymą ne Lietuvoje', 'lp_express'),
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => __('Būdo pavadinimas', 'lp_express'),
                    'type' => 'text',
                    'description' => __('Tai kontroliuoja pavadinimą kurį matys lankytojas čekio puslapyje ', 'lp_express'),
                    'default' => __('LP Express pristatymas ne Lietuvoje', 'woocommerce'),
                )
            );
        }

        /**
         * Ckecks if shipping method is available
         */
        public function is_available($package)
        {
            if ($this->is_enabled() == false) {
                return false;
            }

            $available_destinations = Woocommerce_Lp_Express_Shipping_Methods_Controller::get_available_destinations();
            $selected_destination   = WC()->customer->get_shipping_country();

            if ($selected_destination != 'LT' && get_option('wc_lp_express_sender_settings') == null) {
                foreach($available_destinations as $destination) {
                    if($selected_destination == $destination->code)
                        return true;
                }
            }

            if ($selected_destination != 'LT'
                && get_option('wc_lp_express_sender_settings')['wp_lp_manifestgen'] == 'manual') {
                //Check if country is available
                foreach($available_destinations as $destination) {
                    if($selected_destination == $destination->code)
                        return true;
                }
            }

            if($selected_destination != 'LT') {
                //Check if not exceeds weight and size limit
                $length = $weight = $width  = $height = 0;
                foreach ( $package['contents'] as $item_id => $values ) {
                    $_product = $values['data'];

                    $length += $_product->get_length() * $values['quantity'];
                    $width  += $_product->get_width()  * $values['quantity'];
                    $height += $_product->get_height() * $values['quantity'];
                    $weight += $_product->get_weight() * $values['quantity'];

                    if($_product->get_length() == null || $_product->get_width() == null || $_product->get_height() == null
                        || $_product->get_weight() == null) {
                        return false;
                    }

                    //Check if not exceeds size and weight limit
                    if($length > 150 || $width > 300 || $height > 300 || $weight > 500) return false;
                }

                //If shipping rule returned false that means we need to disable this method
                $shipping_rules = Woocommerce_Lp_Express_Shipping_Rules_Controller::shipping_rule_availability($this->id);
                if (!$shipping_rules) {
                    return false;
                }


                //Check if country is available
                foreach($available_destinations as $destination) {
                    if($selected_destination == $destination->code)
                        return true;
                }
            }

            return false;
        }

        /**
         * @param string $country_code
         * @return bool
         */
        public function is_fixed_price($country_code='all') {
            return get_option('wc_lp_express_sender_settings') != null && array_key_exists($country_code . '_wp_lp_fixed_international', get_option('wc_lp_express_sender_settings'));
        }

        private function calculate_automaticaly($package, &$cost) {
            //Dimensions
            $weight = $qty = null;

            //Summing up all products in checkout
            foreach ($package['contents'] as $item => $value) {
                $weight += $value['data']->get_weight() * $value['quantity'];
                $qty += $value['quantity'];
            }

            //Returns &cost as addressed parameter
            Woocommerce_Lp_Express_Public::send_request('IN', $weight, $package, $cost);
        }

        /**
         * Calculates shipping costs
         */
        public function calculate_shipping($package = array())
        {
            $callback        = 'wp_lp_fixed_international';
            $options         = get_option('wc_lp_express_sender_settings');
            $selected        = WC()->customer->get_shipping_country();
            $cost            = null;

            //Selected country fixed
            foreach (Woocommerce_Lp_Express_Shipping_Methods_Controller::get_available_destinations() as $destination) {
                if($this->is_fixed_price($destination->code) && $destination->code == $selected) {
                    $cost = $options[$destination->code . '_' . $callback];
                }
            }

            //If price is controlled by rules
            Woocommerce_Lp_Express_Shipping_Rules_Controller::shipping_costs_controls($this->id, $cost);

            //Selected country field is empty calculate automatically
            if ($cost == null && $this->is_fixed_price($selected)) {
                $this->calculate_automaticaly($package,$cost);
            }

            //If selected country not exists in fixed prices
            if ($cost == null && !$this->is_fixed_price($selected)) {
                //If still null calculate automatically
                if ($cost == null && $this->is_fixed_price()) {
                    $this->calculate_automaticaly($package,$cost);
                }

                if ($cost != null && $this->is_fixed_price() ) {
                    //Get cost  from all_
                    $cost = $options['all_' . $callback];
                }
            }

            //If still null calculate automatically
            if($cost == null) $this->calculate_automaticaly($package,$cost);

            // Returns shipping costs
            $this->add_rate(array(
                'id' => $this->id,
                'label' => $this->title,
                'cost' => $cost,
                'calc_tax' => 'per_order'
            ));
        }
    }
}