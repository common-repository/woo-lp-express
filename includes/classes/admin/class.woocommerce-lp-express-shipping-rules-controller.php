<?php
/**
 * Responsible for managing shipping rules
 * @package    woocommerce-lp-express
 * @subpackage woocommerce-lp-express/includes/classes/admin
 */

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

if (!class_exists('Woocommerce_Lp_Express_Shipping_Rules_Controller')) {
    class Woocommerce_Lp_Express_Shipping_Rules_Controller
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
         * Woocommerce_Lp_Express_Shipping_Rules_Controller constructor.
         * @param $plugin_name
         * @param $version
         */
        public function __construct($plugin_name, $version)
        {
            $this->plugin_name  = $plugin_name;
            $this->version      = $version;
        }

        /**
         * Save shipping rules options ajax
         * TODO: Add validation
         */
        public function save_shipping_rules_ajax()
        {
            update_option("lpx-shipping-rules",json_encode($_POST['data']));
            wp_die();
        }

        /**
         * Get shipping rule options ajax
         */
        public function get_shipping_rules_ajax()
        {
            echo get_option("lpx-shipping-rules");
            wp_die();
        }

        /**
         * Get shipping rules decoded
         * @return array
         */
        public static function get_shipping_rules()
        {
            return json_decode(get_option('lpx-shipping-rules'));
        }

        /**
         * Get rules for method
         * @param $method_id
         * @return array
         */
        public static function get_rules_for_method($method_id)
        {
            $ruleArray = array();
            if(self::get_shipping_rules() != null) {
                foreach (self::get_shipping_rules() as $rule) {
                    foreach ($rule->action->for as $method) {
                        if (property_exists($method, $method_id)) {
                            array_push($ruleArray, $rule);
                        }
                    }
                }
                return $ruleArray;
            }
        }

        /**
         * Get all category ids from object
         * @param $category_object
         * @return array
         */
        public static function get_term_ids($category_object)
        {
            $term_ids = array();
            foreach ($category_object as $category) {
                array_push($term_ids, $category->term_id);
            }
            return $term_ids;
        }

        /**
         * Shipping rules controls
         * @param $shipping_method_id
         * @return bool
         */
        public static function shipping_rule_availability($shipping_method_id)
        {
            //Is available shipping rules
            if(self::get_rules_for_method($shipping_method_id) != null) {
                foreach (self::get_rules_for_method($shipping_method_id) as $rule) {
                    if (property_exists($rule, 'action') && $rule->action->type == 'disable') {
                        if (self::filter_variations($rule) != false)
                            return !self::filter_variations($rule); //false if not available
                    }
                }
            }

            return true; //If there is no shipping rule for current method return true
        }

        /**
         * Check if current product applies for the filter
         * @param $rule
         * @return bool
         */
        public static function filter_variations($rule)
        {
            if(WC()->cart->get_cart() != null) {
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    $product = $cart_item['data'];
                    $product_price = $product->sale_price != null ? $product->sale_price * $cart_item['quantity'] : $product->regular_price * $cart_item['quantity'];

                    //True - if rule applies
                    //Category variations ( Category, Category - qty, Category - price, Category - qty, price )
                    if (property_exists($rule, 'filter') && property_exists($rule->filter, 'Kategorija')) {
                        //Check if has any id from category object
                        if (has_term(self::get_term_ids($rule->filter->Kategorija), 'product_cat', $product->id)) {
                            //If QTY rule exists
                            if (property_exists($rule->filter, 'Kiekis')) {
                                //Category - qty
                                if ($cart_item['quantity'] >= $rule->filter->Kiekis && !property_exists($rule->filter, 'Kaina')) {
                                    return true;
                                }

                                //Category - qty,price
                                if (property_exists($rule->filter, 'Kaina')) {
                                    if ($cart_item['quantity'] >= $rule->filter->Kiekis && $product_price >= $rule->filter->Kaina) {
                                        return true;
                                    }
                                }
                            }

                            //Category - price
                            if (property_exists($rule->filter, 'Kaina') && !property_exists($rule->filter, 'Kiekis')) {
                                if ($product_price >= $rule->filter->Kaina) {
                                    return true;
                                }
                            }

                            //Category
                            if (!property_exists($rule->filter, 'Kaina') && !property_exists($rule->filter, 'Kiekis')) {
                                return true;
                            }
                        }
                    }

                    //Variations Qty, Qty - price
                    if (property_exists($rule, 'filter') && !property_exists($rule->filter, 'Kategorija')
                        && property_exists($rule->filter, 'Kiekis')) {

                        //Qty - price
                        if (property_exists($rule->filter, 'Kaina')) {
                            if ($cart_item['quantity'] >= $rule->filter->Kiekis && $product_price >= $rule->filter->Kaina) {
                                return true;
                            }
                        }

                        //Qty
                        if (!property_exists($rule->filter, 'Kaina')) {
                            if ($cart_item['quantity'] >= $rule->filter->Kiekis) {
                                return true;
                            }
                        }
                    }

                    //Variations Price only
                    if (property_exists($rule, 'filter') && !property_exists($rule->filter, 'Kategorija')
                        && !property_exists($rule->filter, 'Kiekis') && property_exists($rule->filter, 'Kaina')) {
                        if ($product_price >= $rule->filter->Kaina) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        /**
         * Calculate shipping cost by rules
         * @param $shipping_method_id
         * @param $cost
         */
        public static function shipping_costs_controls($shipping_method_id, &$cost)
        {
            if(self::get_rules_for_method($shipping_method_id) != null) {
                foreach (self::get_rules_for_method($shipping_method_id) as $rule) {
                    //If rule applies for current method
                    if (self::filter_variations($rule)) {
                        switch ($rule->action->type) {
                            case 'discount':
                                //Fixed discount
                                if ($rule->action->amount[0] == 'fixed') {
                                    $cost = $cost - $rule->action->amount[1];
                                }
                                //Percentage discount
                                if ($rule->action->amount[0] == 'percentage') {
                                    $cost = $cost - ($cost * ($rule->action->amount[1] / 100));
                                }
                                break;
                            case 'fixed_price':
                                $cost = $rule->action->amount;
                                break;
                            case 'free_shipping':
                                $cost = 0;
                                break;
                        }
                    }
                }
            }
        }
    }
}