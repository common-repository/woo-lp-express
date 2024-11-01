<?php
/**
 * Responsible for registering and rendering lp express order metabox
 * @package    woocommerce-lp-express
 * @subpackage woocommerce-lp-express/includes/classes/admin
 */

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

if(!class_exists('Woocommerce_Order_Lp_Express_Metabox')) {
    class Woocommerce_Order_Lp_Express_Metabox
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
         * Woocommerce_Order_Lp_Express_Metabox constructor.
         * @param $plugin_name
         * @param $version
         */
        public function __construct($plugin_name, $version)
        {
            $this->plugin_name = $plugin_name;
            $this->version = $version;
        }

        /**
         * Registers order view metabox
         */
        public function lp_express_register_metabox()
        {
            add_meta_box('meta-box-id', __('LP Express Pristatymas', 'textdomain'),
                [$this, 'lp_express_display_metabox_content'], 'shop_order');
        }

        /**
         * Generate form for shipping label
         * @param $shipping_method
         * @param $order_id
         * @return string
         */
        private static function generate_metabox_label_form($shipping_method, $order_id) {
            //Message by shipping method

            if( $shipping_method['method_id'] == 'lp_express_post_office_shipping_method' ||
                $shipping_method['method_id'] == 'lp_express_courrier_shipping_method' ||
                $shipping_method['method_id'] == 'lp_express_international_shipping_method') {

                $message = __('Siuntos svoris (kg)', 'lp_express');
                $class   = 'weight';

            } else {
                $message = __('Dėžės dydis (S, M arba L)', 'lp_express');
            }

            //Generate html for manual generation content
            $html = '<div id="parcel-info-wrapper">';
                $html .= '<table id="parcel-info" class="table">';
                    $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th style="text-align:left">' . $message . '</th>';
                        $html .= '<th></th>';
                    $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                    $html .= '<tr>';
                        //Input field
                        $html .= '<td>';
                            $html .= '<input type="text" id="parcel-info-field" class="' . $class . '"/>';
                        $html .= '</td>';
                        //Action
                        $html .= '<td>';
                            $html .= '<button class="button" id="generate-label"
                                        type="button" onclick="jQuery(\'.loading-bar\').show(); jQuery(\'.label_wrapper\').hide(); 
                                    generateLabelAjax(\'' . $order_id . '\')" />' .
                                    __('Generuoti lipduką', 'lp_express') . '</button>';
                        $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '</tbody>';
                $html .= '</table>';
            $html .= '</div>';

            return $html;
        }

        /**
         * Renders metabox content
         * @param $post
         */
        public static function lp_express_display_metabox_content($post)
        {
            $order              = new WC_Order($post->ID);
            $shipping_method    = @array_shift($order->get_shipping_methods());
            $shipping_method_id = $shipping_method['method_id'];

            //If shipping methods are lpexpress
            if($shipping_method_id    == 'lp_express_courrier_shipping_method'
               || $shipping_method_id == 'lp_express_post_office_shipping_method'
               || $shipping_method_id == 'lp_express_international_shipping_method'
               || $shipping_method_id == 'lp_express_24_terminal_shipping_method') {

                $order_id              = explode("_", $order->get_order_key())[2];
                $label_name            = 'labels_' . $order_id . '.pdf';
                $current_label_dir     = WCLP_LABELS_URL. '/' . $post->ID . '/' . $label_name;
                $label_exists          = file_exists(WCLP_LABELS_DIR . '/' . $post->ID . '/' . $label_name);

                $manifest_name         = 'manifests_' . $order_id . '.pdf';
                $current_manifest_dir  = WCLP_MANIFESTS_URL . '/' . $post->ID . '/' . $manifest_name;
                $manifest_exists       = file_exists(WCLP_MANIFESTS_DIR . '/' . $post->ID . '/' . $manifest_name);

                echo '<div class="loading-bar">
                    <div class="md-preloader">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="75" width="75" viewbox="0 0 75 75">
                        <circle cx="37.5" cy="37.5" r="33.5" stroke-width="8"/></svg>
                    </div>
                </div>';
                echo '<div class="label_wrapper">';
                    if ($label_exists) {
                        //Existing label
                        echo '<table class="table" style="width:100%">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th style="text-align:left">' . __('Siuntos dokumentai: ', 'lp_express') . '</th>';
                        echo '<th style="text-align:left">' . __('Siuntos kaina: ', 'lp_express') . '</th>';
                        echo get_option('wc_lp_express_sender_settings')['wp_lp_labelgen'] == 'manual' ? '<th></th>' : '';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        echo '<tr>';
                        echo '<td>';
                        echo '<a href="' . $current_label_dir . '" target="_blank">' . $label_name . '</a><br />';
                        //If manifest exists
                        if ($manifest_exists) {
                            echo '<a href="' . $current_manifest_dir . '" target="_blank">' . $manifest_name . '</a>';
                        }
                        echo '</td>';
                        //[post_id]=cost[0]
                        echo '<td>' . get_option('lpx_shipping_costs')[$post->ID][0] . ' &euro;</td>';
                        //Call courier button if call courier is manual
                        if (get_option('wc_lp_express_sender_settings')['wp_lp_manifestgen'] == 'manual' || get_option('wc_lp_express_sender_settings') == null) {
                            $terminal = get_option('wc_lp_express_sender_settings')['wp_lp_terminal_shipping_type'];
                            if ($terminal != 'CC' && $terminal != 'CA' && $terminal != 'CH' && !$manifest_exists) {
                                echo '<td><button class="button" type="button" 
                                            onclick="jQuery(\'.loading-bar\').show(); jQuery(\'.label_wrapper\').hide();
                                            call_courier(\'' . $post->ID . '\',\'' . get_option('lpx_label_identcode')[$post->ID][0] . '\')">'
                                    . __('Generuoti manifestą', 'lp_express') .
                                    '</button></td>';
                            }
                        }
                        echo '</tr>';
                        echo '</tbody>';
                        echo '</table>';
                    } else if (get_option('wc_lp_express_sender_settings') == null) {
                        echo self::generate_metabox_label_form($shipping_method,$post->ID);
                    } else if (get_option('wc_lp_express_sender_settings')['wp_lp_labelgen'] == 'manual'
                        && !$label_exists) {
                        //Form to generate label manually
                        echo self::generate_metabox_label_form($shipping_method,$post->ID);
                    } else {
                        //Label don't exist yet
                        echo __('Pristatymo lipdukas nebuvo sugeneruotas.', 'lp_express');
                    }
                echo '</div>';
            } else echo __('Pasirinktas ne lp express pristatymo būdas.', 'lp_express');
        }

        /**
         * Saves metabox content
         */
        public function lp_express_save_metabox_content($post_id)
        {
            // This should be empty if metabox content gets saved by ajax
        }
    }
}