<?php

/**
* Admin specific functionality
* @package woocommerce-lp-express
* @subpackage woocommerce-lp-express/includes/classes/admin
**/

/**
 * Don't allow to call this file directly
 **/
if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('Woocommerce_Lp_Express_Admin')) {
    class Woocommerce_Lp_Express_Admin
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
         * @param $plugin_name
         * @param $version
         **/
        public function __construct($plugin_name, $version)
        {
            $this->plugin_name = $plugin_name;
            $this->version = $version;

            $this->load_dependencies();
        }

        /**
         * Adds settings page section
         */
        public function admin_settings_page()
        {
            add_menu_page('LP Express', 'LP Express', 'manage_options', 'lp_express_admin_page',
                [$this, 'admin_page_display'], WCLP_PLUGIN_URL . '/views/admin/images/lp_express_icon.png');
        }

        /**
         * Loads current class dependencies
         */
        private function load_dependencies()
        {
            require WCLP_PLUGIN_DIR . '/includes/templates/admin/form/' . $this->plugin_name . '-forms-callback.php';
        }

        /**
         * Serialized data validation
         * @param bool return
         * @return array
         */
        public function validate_serialized_data($return=false) {
            if (isset($_POST['data'])) {
                parse_str( $_POST['data'], $unserialized_data );

                //Validate between option pages
                switch ( $unserialized_data['option_page'] ) {
                    /**
                     * Api options validation
                     */
                    case 'wc_api_options':
                        $invalid_data       = array();
                        $api_data           = $unserialized_data['wc_lp_express_api'];

                        //Is API Domain valid
                        if ( $api_data['wc_lp_domain'] != 'api.balticpost.lt' && $api_data['wc_lp_domain'] != 'apibeta.balticpost.lt' ) {
                            array_push($invalid_data, 'wc_lp_domain');
                        }

                        //Is PartnerID valid
                        if ( strlen( $api_data['wc_lp_partnerid'] ) != 32 ) {
                            array_push($invalid_data, 'wc_lp_partnerid');
                        }

                        //Is ClientID valid
                        if ( !is_numeric( $api_data['wc_lp_clientid'] ) ) {
                            array_push($invalid_data, 'wc_lp_clientid');
                        }

                        //Is PaymentPIN valid
                        if ( !is_numeric( $api_data['wc_lp_paymentpin'] ) ) {
                            array_push($invalid_data, 'wc_lp_paymentpin');
                        }

                        //Is AdminPIN valid
                        if ( !is_numeric( $api_data['wc_lp_adminpin'] ) ) {
                            array_push($invalid_data, 'wc_lp_adminpin');
                        }

                        //If return is true then return data without echo
                        if ($return)
                            return $invalid_data;
                        else
                            echo json_encode($invalid_data);
                    break;

                    /**
                     * Sender info validation
                     */
                    case 'wc_sender_info':
                        $invalid_data        = array();
                        $sender_data         = $unserialized_data['wc_lp_express_sender'];

                        //Is Sender Name valid
                        if ( preg_match( '/\\d/', $sender_data['wc_lp_sendername'] ) ) {
                            array_push($invalid_data, 'wc_lp_sendername');
                        }

                        //Is Sender Phone valid
                        if ( !preg_match( '(86|\+3706)', $sender_data['wc_lp_senderphone'] ) ) {
                            array_push($invalid_data, 'wc_lp_senderphone');
                        }

                        //Is Email valid
                        if ( !filter_var( $sender_data['wc_lp_senderemail'], FILTER_VALIDATE_EMAIL ) ) {
                            array_push($invalid_data, 'wc_lp_senderemail');
                        }

                        //Is Sender Zip Code valid
                        if ( !is_numeric( $sender_data['wp_lp_senderzipcode'] ) ) {
                            array_push($invalid_data, 'wp_lp_senderzipcode');
                        }

                        //If return is true then return data without echo
                        if ($return)
                            return $invalid_data;
                        else
                            echo json_encode($invalid_data);
                    break;

                    /**
                     * Sender settings validation
                     */
                    case 'wc_sender_settings':
                        $invalid_data          = array();
                        $sender_settings       = $unserialized_data['wc_lp_express_sender_settings'];

                        //Is Labelgen valid
                        if ( $sender_settings['wp_lp_labelgen'] != 'manual'
                            && $sender_settings['wp_lp_labelgen'] != 'auto' ) {
                            array_push($invalid_data, 'wp_lp_labelgen');
                        }

                        //Is Manifestgen valid
                        if ( $sender_settings['wp_lp_manifestgen'] == 'manual'
                            && $sender_settings['wp_lp_manifestgen'] == 'auto' ) {
                            array_push($invalid_data, 'wp_lp_manifestgen');
                        }

                        //Is Manifestcron valid
                        if ( $sender_settings['wp_lp_manifestcron'] != '21600'
                            && $sender_settings['wp_lp_manifestcron'] != '43200'
                            && $sender_settings['wp_lp_manifestcron'] != '86400' ) {
                            array_push($invalid_data, 'wp_lp_manifestcron');
                        }

                        //Is numeric fields valid
                        if ( !is_numeric( $sender_settings['wp_lp_fixed_terminal'] )
                            && $sender_settings['wp_lp_fixed_terminal'] != null ) {
                            array_push($invalid_data, 'wp_lp_fixed_terminal');
                        }

                        if ( !is_numeric( $sender_settings['wp_lp_fixed_courier'] )
                            && $sender_settings['wp_lp_fixed_courier'] != null ) {
                            array_push($invalid_data, 'wp_lp_fixed_courier');
                        }

                        if ( !is_numeric( $sender_settings['wp_lp_fixed_international'] )
                            && $sender_settings['wp_lp_fixed_international'] != null ) {
                            array_push($invalid_data, 'wp_lp_fixed_international');
                        }

                        if ( !is_numeric( $sender_settings['wp_lp_fixed_post_office'] )
                            && $sender_settings['wp_lp_fixed_post_office'] != null ) {
                            array_push($invalid_data, 'wp_lp_fixed_post_office');
                        }

                        //Is Terminal valid
                        if( $sender_settings['wp_lp_terminal_shipping_type'] != 'HC' &&
                            $sender_settings['wp_lp_terminal_shipping_type'] != 'CC' &&
                            $sender_settings['wp_lp_terminal_shipping_type'] != 'CH' &&
                            $sender_settings['wp_lp_terminal_shipping_type'] != 'CA' ) {
                            array_push($invalid_data, 'wp_lp_terminal_shipping_type');
                        }

                        //If return is true then return data without echo
                        if ($return)
                            return $invalid_data;
                        else
                            echo json_encode($invalid_data);
                    break;
                }
                wp_die();
            }
        }

        /**
         * Test authentification data with hello world message
         */
        public function test_authorization_data() {
            if(!empty($this->validate_serialized_data(true))) {
                echo __('Kaidingai įvesti duomenys, prašome patikrinti ir mėginti dar kartą.', 'lp_express');
                wp_die();
            }

            //Options
            $options = Woocommerce_Lp_Express_Options_Controller::get_formatted_options();

            //Check if options is saved
            if($options == null) {
                echo __('Duomenys įvesti teisingai tačiau prieš testuojant juos išsaugokite.', 'lp_express');
                wp_die();
            }

            //LpExpress options
            $client = new LpExpressApi($options['options']);

            //Call request hello world
            try {
                $response = $client->call('hello_world',['mymessage'=>'']);
                echo $response;
            } catch (SoapFault $e) {
                echo $e->getMessage();
            }

            wp_die();
        }

        /**
         * Call courier and generate manifest
         * Universal method for ajax and direct call
         * @param $identcode
         */
        public static function call_courier($order_id, $identcode=null) {

            //WC Order
            $order = new WC_Order(isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : $order_id);

            //identcode
            $identcode = isset($_REQUEST['identcode']) ? $_REQUEST['identcode'] : $identcode;

            //Options
            $options = Woocommerce_Lp_Express_Options_Controller::get_formatted_options();

            //LpExpress options
            $client = new LpExpressApi($options['options']);

            //location to download labels
            $download_location = WCLP_MANIFESTS_DIR . '/' . $order->get_order_number();

            //Order
            $partnerorderid = explode('_', $order->get_order_key())[2];

            //Formatted request
            $call_request = [
                'kepoluserid' => $options['customerId'],
                'adminpin'    => $options['adminPin'],
                'parcels'     => [$identcode]
            ];

            //Try to call courier
            try {
                //Create manifests directory if not exists
                if (!file_exists($download_location)) {
                    mkdir($download_location, 0755, true);
                }

                //Call courier request sent
                $call_response = $client->call('call_courier', $call_request);

                if ($call_response) {
                    //Save manifest file
                    foreach ($call_response->calls as $index => $call) {
                        $file_status = file_put_contents(
                            $download_location . '/manifests_' . $partnerorderid . '.pdf',
                            fopen($client->getManifestUri($call->manifestid), 'r')
                        );

                        //If failed to download file
                        if ($file_status) {
                            echo Woocommerce_Order_Lp_Express_Metabox::
                            lp_express_display_metabox_content(get_post($order->get_order_number()));
                        } else {
                            echo __('Įvyko klaida. Prašome perkrauti puslapį ir atlikti šį veiksmą pakartotinai.
                                        Jeigu šio veiksmo nepavyksta atlikti prašome kreiptis į pagalbos centrą.',
                                'lp_express');
                        }
                    }
                } else {
                    echo __('Įvyko klaida. Prašome perkrauti puslapį ir atlikti šį veiksmą pakartotinai.
                                        Jeigu šio veiksmo nepavyksta atlikti prašome kreiptis į pagalbos centrą.',
                        'lp_express');
                }
            } catch(SoapFault $e) {
                echo $e->getMessage();
            }

            if(isset($_REQUEST['identcode'])) die();
        }

        /**
         * Generate shipping label
         * Universal method for ajax and direct call
         * @param $order_id
         */
        public static function generate_shipping_label($order_id) {

            //Orderid
            $order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : $order_id;

            //WC Order
            $order = new WC_Order($order_id);

            //Shipping type and dimensions
            $parcel_type = $weight = $length = $width = $height = null;

            //Order
            $partnerorderid = explode('_', $order->get_order_key())[2];

            //Options
            $options = Woocommerce_Lp_Express_Options_Controller::get_formatted_options();

            //LpExpress client
            $client = new LpExpressApi($options['options']);

            //Selected shipping method
            $shipping_method = @array_shift($order->get_shipping_methods())['method_id'];

            //Get items
            $items = $order->get_items();

            //location to download labels
            $download_location = WCLP_LABELS_DIR . '/' . $order->get_order_number();

            //Find parcel type if lp express method is selected (default: null)
            switch ($shipping_method) {
                case 'lp_express_courrier_shipping_method':
                    $parcel_type = 'EB';
                    break;

                case 'lp_express_post_office_shipping_method':
                    $parcel_type = 'AB';
                    break;

                case 'lp_express_international_shipping_method':
                    $parcel_type = 'IN';
                    break;

                case 'lp_express_24_terminal_shipping_method':
                    //HC || CC || CH || CA
                    $parcel_type = get_option('wc_lp_express_sender_settings')['wp_lp_terminal_shipping_type'];
                    break;
            }

            if ($parcel_type == null) $parcel_type = 'HC';

            //If one of lp express shipping method called
            if ($parcel_type != null) {
                //Just skip LT- without validation
                $postcode = str_replace("LT", "",  $order->get_shipping_postcode());
                $postcode = str_replace("-",  "",  $postcode);

                //Prepared array for receiver data
                $receiver_data = [
                    'receiver_name'    => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                    'receiver_mobile'  => $order->get_billing_phone(),
                    'receiver_email'   => $order->get_billing_email(),
                    'receiver_address' => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2(),
                    'receiver_city'    => $order->get_shipping_city(),
                    'receiver_zip'     => $postcode,
                    'receiver_country' => $order->get_shipping_country()
                ];

                //Calculate whole order weight and size
                foreach ($items as $key => $value) {
                    $_product = wc_get_product($value['product_id']);
                    $weight += $_product->get_weight();

                    //Cm to mm
                    $length += $_product->get_length() * 10;
                    $width  += $_product->get_width()  * 10;
                    $height += $_product->get_height() * 10;
                }

                //Terminal shipping method
                if ($parcel_type == 'HC' || $parcel_type == 'CC' || $parcel_type == 'CH' || $parcel_type == 'CA') {
                    //If called from ajax boxsize
                    if (isset($_REQUEST['parcel_info'])) {
                        $box_size = $_REQUEST['parcel_info'];
                        switch($box_size) {
                            case strtolower($box_size) == 's':
                                $box_size = 'Small';
                                break;
                            case strtolower($box_size) == 'm':
                                $box_size = 'Medium';
                                break;
                            case strtolower($box_size) == 'l':
                                $box_size = 'Large';
                                break;
                        }
                    } else {
                        //Calculate box size auto
                        $box_size_request = $client->call('get_product_by_dimensions',
                            [$parcel_type, $length, $width, $height]);

                        $box_size = $box_size_request->boxsize;
                    }

                    //Find terminal ID
                    foreach ($order->get_meta_data() as $meta) {
                        if ($meta->key == 'lp_express_terminal_id') {
                            $receiver_data['terminal_id'] = $meta->value;
                        }
                    }
                } else {
                    //If ajax called as manual shipping label generation and parcel type is not terminal
                    if (isset($_REQUEST['order_id'])) {
                        if($parcel_type == 'EB' || $parcel_type == 'AB' || $parcel_type == 'IN') {
                            //Weight is called from ajax
                            $weight = $_REQUEST['parcel_info'];
                            if($parcel_type == 'AB' && $weight > 10) {
                                //Call error because AB shipping doesnt allow this
                                echo __('Svorio limitas gali būti nedaugiau kaip 10kg šiam siuntimo metodui.', 'lp_express');
                            }
                        }
                    }

                    //If payment method is COD
                    if ($parcel_type == 'EB' && $order->get_payment_method() == 'cod') {
                        $options['parcelvaluecurrency'] = $order->get_currency();
                        $options['parcelvalue']         = $order->get_subtotal();
                    }
                }

                $request = [
                    'partnerorderid' => $partnerorderid,
                    'kepoluserid' => $options['customerId'],
                    'paymentpin' => $options['paymentPin'],
                    'labels' => [
                        Woocommerce_Lp_Express_Public::format_label($parcel_type, $weight, $box_size, $options,
                            uniqid(), null, $receiver_data, $order->get_payment_method() == 'cod')
                    ]
                ];

                //Try to generate shipping label from lp express
                try {
                    //Adding labels to lpexpress
                    $response = $client->call('add_labels', $request);

                    //Add shipping cost to database get_option('lpx_shipping_costs')[order_id] returns cost
                    $current_cost_array = !get_option('lpx_shipping_costs') ? [] : get_option('lpx_shipping_costs');

                    //Initialize array array[post_id] so we can push something to it
                    $current_cost_array[$order->get_order_number()] = [];

                    //Push array[post_id] = cost
                    array_push($current_cost_array[$order->get_order_number()], $response->totalsum);

                    //Update option
                    update_option('lpx_shipping_costs', $current_cost_array);

                    //Request for label confirmation
                    $confirmed_request = ['partnerid' => $response->orderid];

                    //Confirm labels
                    $confirm_response = $client->call('confirm_labels', $confirmed_request);

                    //Create labels directory if not exists
                    if (!file_exists($download_location)) {
                        mkdir($download_location, 0755, true);
                    }

                    //Save label to download location
                    if ($confirm_response) {
                        $file_status = file_put_contents(
                            $download_location . '/labels_' . $confirm_response->partnerorderid . '.pdf',
                            fopen($client->getLabelsUri($confirm_response->orderpdfid), 'r')
                        );

                        //Add identcode for courier calling if manifest generation is auto
                        //if (get_option('wc_lp_express_sender_settings')['wp_lp_labelgen'] == 'manual') {
                            //Confirmed label array identcode
                            //array[order_id] = identcode
                            $parcels = get_option('lpx_label_identcode');

                            //Initialize empty array for current order
                            $parcels[$order->get_order_number()] = [];
                            foreach ($confirm_response->labels as $label) {
                                array_push($parcels[$order->get_order_number()],$label->identcode);
                            }

                            //Register option with identcode
                            update_option('lpx_label_identcode', $parcels);
                        //}

                        //If this method called from ajax set response to generate content
                        if (isset($_REQUEST['order_id'])) {
                            if ($file_status) {
                                echo Woocommerce_Order_Lp_Express_Metabox::
                                lp_express_display_metabox_content(get_post($order->get_order_number()));
                            } else {
                                echo __('Įvyko klaida. Prašome perkrauti puslapį ir atlikti šį veiksmą pakartotinai.
                                        Jeigu šio veiksmo nepavyksta atlikti prašome kreiptis į pagalbos centrą.',
                                    'lp_express');
                            }
                        }

                        //Call courier cronjob if manifest is auto
                        if (get_option('wc_lp_express_sender_settings')['wp_lp_manifestgen'] == 'auto') {
                            wp_schedule_single_event(time() + get_option('wc_lp_express_sender_settings')['wp_lp_manifestcron'],
                                'call_courier',
                                [$order->get_order_number(), $parcels[$order->get_order_number()][0]]);
                        }

                    } else {
                        echo __('Įvyko klaida. Prašome perkrauti puslapį ir atlikti šį veiksmą pakartotinai.
                                        Jeigu šio veiksmo nepavyksta atlikti prašome kreiptis į pagalbos centrą.',
                            'lp_express');
                    }
                } catch (SoapFault $e) {
                    print_r($e->getMessage());
                }
            }
            if (isset($_REQUEST['order_id'])) die();
        }



        /**
         * Adds core options to settings pages
         */
        public function admin_settings_options()
        {
            //-------------------------------------------------------------------------------------//
            //Api options
            //-------------------------------------------------------------------------------------//
            register_setting('wc_api_options', 'wc_lp_express_api');
            add_settings_section('wc_lp_section_id', '', '', 'api-options');

            //Domain
            add_settings_field('wc_lp_domain', __('Domenas:', 'lp_express'),
                'wclp_api_options_callback', 'api-options', 'wc_lp_section_id',
                [
                    'callback'  => 'wc_lp_domain',
                    'example'   => __('Testavimo reikmėms naudokite apibeta.balticpost.lt ne testavimo - api.balticpost.lt', 'lp_express')
                ]
            );
            //Partner ID
            add_settings_field('wc_lp_partnerid', __('Partnerio ID:', 'lp_express'),
                'wclp_api_options_callback', 'api-options', 'wc_lp_section_id',
                [
                    'callback'  => 'wc_lp_partnerid',
                    'example'   => __('Pvz.: a60e321273cd78de2b273131925f9836', 'lp_express')
                ]
            );
            //Partner Password
            add_settings_field('wc_lp_partnerpassword', __('Partnerio slaptažodis:', 'lp_express'),
                'wclp_api_options_callback', 'api-options', 'wc_lp_section_id',
                [
                    'callback'  => 'wc_lp_partnerpassword',
                    'example'   => __('Pvz.: treYep4e', 'lp_express')
                ]
            );
            //Client ID
            add_settings_field('wc_lp_clientid', __('Kliento ID:', 'lp_express'),
                'wclp_api_options_callback', 'api-options', 'wc_lp_section_id',
                [
                    'callback'  => 'wc_lp_clientid',
                    'example'   => __('Pvz.: 201199', 'lp_exress')
                ]
            );
            //Payment PIN
            add_settings_field('wc_lp_paymentpin', __('Mokėjimų PIN:', 'lp_express'),
                'wclp_api_options_callback', 'api-options', 'wc_lp_section_id',
                [
                    'callback'  => 'wc_lp_paymentpin',
                    'example'   => __('Pvz.: 5654', 'lp_express')
                ]
            );
            //Administrator PIN
            add_settings_field('wc_lp_adminpin', __('Administratoriaus PIN:'),
                'wclp_api_options_callback', 'api-options', 'wc_lp_section_id',
                [
                    'callback'  => 'wc_lp_adminpin',
                    'example'   => __('Pvz.: 7249', 'lp_express')
                ]
            );

            //-------------------------------------------------------------------------------------//
            //Sender info
            //-------------------------------------------------------------------------------------//
            register_setting('wc_sender_info', 'wc_lp_express_sender');
            add_settings_section('wc_sender_info', '', '', 'sender-info');


            //Sender name or company
            add_settings_field('wp_lp_sendername', __('Siuntėjo vardas pavardė arba įmonės pavadinimas:', 'lp_express'),
                'wclp_sender_options_callback', 'sender-info', 'wc_sender_info',
                [
                    'callback'  => 'wc_lp_sendername',
                    'example'   => __('Pvz: Vardenis Pavardenis arba UAB Įmonė','lp_express')
                ]
            );

            //Sender phone
            add_settings_field('wp_lp_senderphone', __('Siuntėjo telefonas:', 'lp_express'),
                'wclp_sender_options_callback',
                'sender-info', 'wc_sender_info',
                [
                    'callback'  => 'wc_lp_senderphone',
                    'example'   => __('Pvz: +37065555555','lp_express')
                ]
            );

            //Sender email
            add_settings_field('wp_lp_senderemail', __('Siuntėjo el. paštas:', 'lp_express'),
                'wclp_sender_options_callback',
                'sender-info', 'wc_sender_info',
                [
                    'callback' => 'wc_lp_senderemail',
                    'example'  => __('Pvz: pavizdys@pavizdys.lt','lp_express')
                ]
            );

            //Sender address
            add_settings_field('wp_lp_senderaddress', __('Siuntėjo adresas:', 'lp_express'),
                'wclp_sender_options_callback',
                'sender-info', 'wc_sender_info',
                [
                    'callback' => 'wp_lp_senderaddress',
                    'example' => __('Pvz: Gatvė g. 155','lp_express')
                ]
            );

            //Sender city
            add_settings_field('wp_lp_sendercity', __('Siuntėjo miestas:', 'lp_express'),
                'wclp_sender_options_callback',
                'sender-info', 'wc_sender_info',
                [
                    'callback' => 'wp_lp_sendercity',
                    'example' => __('Pvz: Kaunas','lp_express')
                ]
            );

            //Sender zip code
            add_settings_field('wp_lp_senderzipcode', __('Siuntėjo pašto kodas:', 'lp_express'),
                'wclp_sender_options_callback',
                'sender-info', 'wc_sender_info',
                [
                    'callback' => 'wp_lp_senderzipcode',
                    'example' => __('Pvz: 44444 (Be pradžios LT-)','lp_express')
                ]
            );

            //-------------------------------------------------------------------------------------//
            //Sender settings
            //-------------------------------------------------------------------------------------//
            register_setting('wc_sender_settings', 'wc_lp_express_sender_settings');
            add_settings_section('wc_sender_settings', '', '', 'sender-settings');

            //Sender label generation
            add_settings_field('wp_lp_labelgen', __('Lipduko generavimas: ', 'lp_express'),
                'wclp_sender_settings_label',
                'sender-settings', 'wc_sender_settings',
                [
                    'callback' => 'wp_lp_labelgen',
                ]
            );

            //Sender manifest generation
            add_settings_field('wp_lp_manifestgen', __('Manifesto generavimas: ', 'lp_express'),
                'wclp_sender_settings_manifest',
                'sender-settings', 'wc_sender_settings',
                [
                    'callback' => 'wp_lp_manifestgen',
                ]
            );

            //Sender manifest generation
            add_settings_field('wp_lp_manifestcron', __('Manifestas generuojamas kas: ', 'lp_express'),
                'wclp_sender_settings_manifest_cron',
                'sender-settings', 'wc_sender_settings',
                [
                    'callback' => 'wp_lp_manifestcron',
                    'hours' => [
                        '21600' => __('6 valandas',  'lp_express'),
                        '43200' => __('12 valandų',  'lp_express'),
                        '86400' => __('24 valandas', 'lp_express')
                    ]
                ]
            );

            //Sender terminal shipping fixed price
            add_settings_field('wp_lp_fixed_terminal', __('Pristatymo į terminalą kaina: ', 'lp_express'),
                'wclp_sender_settings_fixed_terminal',
                'sender-settings', 'wc_sender_settings',
                [
                    'callback' => 'wp_lp_fixed_terminal',
                    'additional_info' => __('Jei norite, kad skaičiavimai būtų automatiniai, palikite tuščią')
                ]
            );

            //Sender courier shipping fixed price
            add_settings_field('wp_lp_fixed_courier', __('Pristatymo kurjeriu kaina: ', 'lp_express'),
                'wclp_sender_settings_fixed_courier',
                'sender-settings', 'wc_sender_settings',
                [
                    'callback' => 'wp_lp_fixed_courier',
                    'additional_info' => __('Jei norite, kad skaičiavimai būtų automatiniai, palikite tuščią')
                ]
            );

            //Sender international shipping fixed price
            add_settings_field('wp_lp_fixed_international', __('Pristatymo užsienyje kaina: ', 'lp_express'),
                'wclp_sender_settings_fixed_international',
                'sender-settings', 'wc_sender_settings',
                [
                    'callback' => 'wp_lp_fixed_international',
                    'additional_info' => __('Jei norite, kad skaičiavimai būtų automatiniai, palikite tuščią')
                ]
            );

            //Sender post office shipping fixed price
            add_settings_field('wp_lp_fixed_post_office', __('Pristatymo į pašto skyrių kaina: ', 'lp_express'),
                'wclp_sender_settings_fixed_post_office',
                'sender-settings', 'wc_sender_settings',
                [
                    'callback' => 'wp_lp_fixed_post_office',
                    'additional_info' => __('Jei norite, kad skaičiavimai būtų automatiniai, palikite tuščią')
                ]
            );

            //Sender terminal shipping method select HC CC CH CA
            add_settings_field('wp_lp_terminal_shipping_type', __('Terminalo siuntimo tipas: ', 'lp_express'),
                'wclp_sender_settings_terminal_shipping_type',
                'sender-settings', 'wc_sender_settings',
                [
                    'callback' => 'wp_lp_terminal_shipping_type',
                    'methods' => [
                        'HC' => 'HC',
                        'CC' => 'CC',
                        'CH' => 'CH',
                        'CA' => 'CA'
                    ]
                ]
            );
        }

        /**
         * Loads main admin display page
         */
        public function admin_page_display()
        {
            require_once WCLP_PLUGIN_DIR . '/views/admin/' . $this->plugin_name . '-admin-page-display-view.php';
        }

        /**
         * Loads various assets css/js
         */
        public function admin_load_assets()
        {
            wp_enqueue_script('interface', WCLP_PLUGIN_URL . '/views/admin/js/interface.js', ['jquery']);
            wp_enqueue_script('select2', WCLP_PLUGIN_URL . '/views/public/js/select2.min.js', ['jquery']);

            wp_localize_script('interface' , 'trans', [
                'validateSizeMsg'     => __('Neteisingas dėžės dydžio tipas.', 'lp_express'),
                'validateWeightMsg'   => __('Neteisingas siuntinio svoris.', 'lp_express'),
                'validateManifestMsg' => __('Įvyko klaida. Prašome kreptis į pagalbos centrą.', 'lp_express')
            ]);

            wp_enqueue_style('interface_design', WCLP_PLUGIN_URL . '/views/admin/css/interface.css');
            wp_enqueue_style('interface_loader', WCLP_PLUGIN_URL . '/views/admin/css/loading.css');
            wp_enqueue_style('select2_css', WCLP_PLUGIN_URL . '/views/public/css/select2.min.css');
        }
    }
}