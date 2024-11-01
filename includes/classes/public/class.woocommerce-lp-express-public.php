<?php
/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
if(!class_exists('Woocommerce_Lp_Express_Public')) {
    class Woocommerce_Lp_Express_Public
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
         * Initialize class parameters
         * Woocommerce_Lp_Express_Public constructor.
         * @param $plugin_name
         * @param $version
         */
        public function __construct($plugin_name, $version)
        {
            $this->plugin_name = $plugin_name;
            $this->version = $version;
        }

        /**
         * Send request to calculate shipping price or calculate cost
         * @param $label_type
         * @param $weight
         * @param $package
         * @param null $cost
         * @return bool
         */
        public static function send_request($label_type, $weight, $package, &$cost=null)
        {
            //Options
            $options = Woocommerce_Lp_Express_Options_Controller::get_formatted_options();

            //Client
            $client = new LpExpressApi($options['options']);

            //Formatted request
            $request = [
                'partnerorderid' => uniqid(),
                'kepoluserid' => $options['customerId'],
                'paymentpin' => $options['paymentPin'],
                'labels' => [
                    self::format_label($label_type, $weight,
                        null, $options, uniqid(), $package, $options, false),
                ]
            ];

            try {
                //Add label order
                $response = $client->call('add_labels', $request);
                $cost = $response->totalsum;
            } catch (SoapFault $e) {
                //If any errors set method as unavalable
                error_log($e->getMessage());
                return false;
            }

            return true;
        }

        /**
         * Formats LP Express label for this method
         * @param $label_type
         * @param $weight
         * @param $boxsize
         * @param $options
         * @param $orderItemId
         * @param $package
         * @param $receiver_data
         * @param $is_cod
         * @return array
         */
        public static function format_label($label_type, $weight, $boxsize, $options, $orderItemId, $package, $receiver_data, $is_cod)
        {
            //Just skip postcode LT- without validation
            $postcode = str_replace("LT", "",  $package != null ? $package['destination']['postcode']
                                                                                        : $receiver_data['receiver_zip']);
            $postcode = str_replace("-",  "",  $postcode);

            $label = [
                //Order
                'partnerorderartid'         => $orderItemId,
                'productcode'               => $label_type,
                'parcelweight'              => $weight,
                'boxsize'                   => $boxsize,

                //Sender
                'sendername'                => $options['sender_name'],
                'sendermobile'              => $options['sender_phone'],
                'senderemail'               => $options['sender_email'],
                'senderaddressfield1'       => $options['sender_address'],
                'senderaddresscity'         => $options['sender_city'],
                'senderaddresszip'          => $options['sender_zip'],
                'senderaddresscountry'      => 'LT',

                //Receiver
                //If package is null then data is for real purpose of generating real label
                //Else is just for calculation purposes showing in checkout
                'receivername'              => $package != null ? $receiver_data['sender_name']       : $receiver_data['receiver_name'],
                'receivermobile'            => $package != null ? $receiver_data['sender_phone']      : $receiver_data['receiver_mobile'],
                'receiveremail'             => $package != null ? $receiver_data['sender_email']      : $receiver_data['receiver_email'],
                'receiveraddressfield1'     => $package != null ? $receiver_data['sender_address']    : $receiver_data['receiver_address'],
                'receiveraddresscity'       => $package != null ? $package['destination']['state']    : $receiver_data['receiver_city'],
                'receiveraddresszip'        => $postcode,
                'receiveraddresscountry'    => $package != null ? $package['destination']['country']  : $receiver_data['receiver_country']
            ];

            //If courier shipping and COD
            if($label_type == 'EB' && $is_cod) {
                $label['parcelvaluecurrency'] = $options['parcelvaluecurrency'];
                $label['parcelvalue']         = $options['parcelvalue'];
                $label['is_cod']              = 1;
            }


            //Terminal
            if($label_type == 'HC' || $label_type == 'CC' || $label_type == 'CH' || $label_type == 'CA') {
                $label['targetmachineidentification'] = $receiver_data['terminal_id'];
            }

            return $label;
        }

        /**
         * Loads various assets css/js
         */
        public function admin_load_assets()
        {
            wp_enqueue_script('lp_express_main', WCLP_PLUGIN_URL . '/views/public/js/main.js', array('jquery'));
            wp_enqueue_script('select2', WCLP_PLUGIN_URL . '/views/public/js/select2.min.js', ['jquery']);
            wp_enqueue_style( 'select2_css', WCLP_PLUGIN_URL . '/views/public/css/select2.min.css');
            wp_enqueue_style( 'main_css', WCLP_PLUGIN_URL . '/views/public/css/main.css');
        }
    }
}