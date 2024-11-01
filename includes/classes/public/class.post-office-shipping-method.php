<?php
/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
if(!class_exists('WC_LP_Express_Post_Office_Shipping_Method')) {
    class WC_LP_Express_Post_Office_Shipping_Method extends WC_Shipping_Method
    {
        /**
         * Adds LP Express post office shipping to WooCommerce shipping settings
         * WC_LP_Express_Post_Office_Shipping_Method constructor.
         */
        public function __construct()
        {
            $this->id                   = 'lp_express_post_office_shipping_method';
            $this->method_title         = __('LP Express pristatymas į paštą', 'lp_express');
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled              = $this->get_option('enabled');
            $this->title                = $this->get_option('title');

//            echo '<pre>';
//            print_r(Woocommerce_Lp_Express_Shipping_Rules_Controller::get_rule_for_method($this->id));

//            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
//                $product = $cart_item['data'];
//                print_r($product->regular_price*$cart_item['quantity']);
//            }

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
         * Adds form fields to WooCommerce LP Express post office shipping settings
         */
        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled'       => [
                    'title'     => __('Įjungti/Išjungti', 'lp_express'),
                    'type'      => 'checkbox',
                    'label'     => __('Įjungti LP Express pristatymą į paštą', 'lp_express'),
                    'default'   => 'no'
                ],
                'title'             => [
                    'title'         => __('Būdo pavadinimas', 'lp_express'),
                    'type'          => 'text',
                    'description'   => __('Tai kontroliuoja pavadinimą kurį matys lankytojas čekio puslapyje ', 'lp_express'),
                    'default'       => __('LP Express pristatymas į paštą', 'lp_express'),
                ]
            ];
        }

        /**
         * Ckecks if shipping method is available
         * @param $package
         * @return bool
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

            if ( WC()->customer->get_shipping_country() == "LT" ) {
                //Check if not exceeds weight and size limit
                $length = $weight = $width = $height = 0;
                foreach ( $package['contents'] as $item_id => $values ) {
                    $_product = $values['data'];

                    if ( $_product->get_length() == null || $_product->get_width() == null || $_product->get_height() == null
                        || $_product->get_weight() == null ) {
                        return false;
                    }

                    $length += $_product->get_length() * $values['quantity'];
                    $width  += $_product->get_width()  * $values['quantity'];
                    $height += $_product->get_height() * $values['quantity'];
                    $weight += $_product->get_weight() * $values['quantity'];

                    //Check if not exceeds size and weight limit
                    if ( $length > 150 || $width > 300 || $height > 300 || $weight > 10 ) return false;
                }

                //If shipping rule returned false that means we need to disable this method
                $shipping_rules = Woocommerce_Lp_Express_Shipping_Rules_Controller::shipping_rule_availability($this->id);
                if (!$shipping_rules) {
                    return false;
                }

                return Woocommerce_Lp_Express_Public::send_request('AB', $weight, $package);
            }

            return false;
        }

        /**
         * Get current shipping method fixed price
         * @return mixed
         */
        public function get_fixed_price() {
            return get_option('wc_lp_express_sender_settings')['wp_lp_fixed_post_office'];
        }


        /**
         * Calculates shipping costs
         * @param $package
         */
        public function calculate_shipping($package = array())
        {
            if($this->get_fixed_price() == null) {
                //Dimensions
                $weight = $qty = null;

                //Response
                $response = null;

                //Cost
                $cost = null;

                //Summing up all products in checkout
                foreach ($package['contents'] as $item => $value) {
                    $weight += $value['data']->get_weight() * $value['quantity'];
                    $qty += $value['quantity'];
                }

                //Returns &cost as addressed parameter
                Woocommerce_Lp_Express_Public::send_request('AB', $weight, $package, $cost);
            } else $cost = $this->get_fixed_price();

            //If price is controlled by rules
            Woocommerce_Lp_Express_Shipping_Rules_Controller::shipping_costs_controls($this->id, $cost);

            //Returns shipping costs
            $this->add_rate([
                'id'        => $this->id,
                'label'     => $this->title,
                'cost'      => $cost,
                'calc_tax'  => 'per_order'
            ]);
        }
    }
}