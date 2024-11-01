<?php

/**
* Main Core Class
* @package woocommerce_lp_express
* @subpackage woocommerce_lp_express/includes/classes
**/

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

/**
* Class that defines all hooks, localizations
**/
class Woocommerce_Lp_Express {
    /**
    * Loader that's responsible for maintaing and registering all hooks
    * @access protected
    **/
    protected $loader;

    /**
    * Plugins unique identifier
    * @access protected
    **/
    protected $plugin_name;

    /**
    * Current plugin version
    * @access protected
    **/
    protected $version;

    /**
    * Current class instance
    * @access public
    **/
    public static $instance;

    /**
     * Woocommerce_Lp_Express constructor
     */
    public function __construct() {

        self::$instance    = $this;

        $this->plugin_name = 'woocommerce-lp-express';
        $this->version     = '2.0.4';
        $this->extensions  = array();

        $this->load_dependencies();
		$this->define_admin_hooks();
        $this->define_public_hooks();
        $this->create_files_directories();
        $this->additional_tabs();
    }

    /**
     * Additional tabs
     * Adds additional tab
     */
    private function additional_tabs() {
        $tabController = new Woocommerce_Lp_Express_Tab_Controller();

        //New feature shipping rules
        $tabController->add_tabs(new Woocommerce_Lp_Express_Tab('lp_express_admin_page', 'woocommerce-lp-express',"Siuntimo taisyklės","shipping-rules"));
    }

    /**
     * Permision notice function
     */
    public function permissions_notice() {
        $class = 'notice notice-error';
        $message = __( 'LP Express: Direktorija neegzistuoja arba uploads neturi priveligijų rašymui. 
        Prašome sukurti direktoriją arba suteikti rašymo privilegijas.', 'lp_express' );

        printf( '<br /><div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }

    private function create_files_directories() {
        if(!is_writable(wp_upload_dir()['basedir'])) {
            add_action('admin_notices', [$this, 'permissions_notice']);
        } else {
            //Create directories for labels and manifests
            if(!file_exists(WCLP_MANIFESTS_DIR)) {
                wp_mkdir_p(WCLP_MANIFESTS_DIR);
            }
            if(!file_exists(WCLP_LABELS_DIR)) {
                wp_mkdir_p(WCLP_LABELS_DIR);
            }
        }
    }

    /**
    * Load dependencies
    **/
    private function load_dependencies() {
        //Checking if library is already included
        if(!class_exists('LpExpressApi')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/lp-express-api.php';
        }

        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class.'        . $this->plugin_name . '-shipping-methods-controller.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class.'        . $this->plugin_name . '-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/admin/class.'  . $this->plugin_name . '-options-controller.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/admin/class.'  . $this->plugin_name . '-shipping-rules-controller.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/admin/class.'  . $this->plugin_name . '-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/admin/class.'  . $this->plugin_name . '-tab.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/admin/class.'  . $this->plugin_name . '-tab-controller.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/admin/class.'  . $this->plugin_name . '-tab-content-controller.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'classes/admin/class.'  . $this->plugin_name . '-metabox.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'classes/public/class.' . $this->plugin_name . '-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'events/'               . $this->plugin_name . '-events.php';

		$this->loader = new Woocommerce_Lp_Express_Loader();
    }

    /**
    * Get plugin identifier
    **/
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
    * Get plugin version 
    **/
    public function get_plugin_version() {
        return $this->version;
    }

    /**
    * Get plugin loader 
    **/
    public function get_loader() {
        return $this->loader;
    }

    /**
    * Run the loader to execute all of the hooks
    **/
    public function run() {
        $this->loader->run();
    }
	
	/**
    * All admin hooks
    **/
	private function define_admin_hooks() {
        $woocommerce_lp_express_admin              = new Woocommerce_Lp_Express_Admin($this->plugin_name, $this->version);
        $woocommerce_lp_express_content_loader     = new Woocommerce_Lp_Express_Tab_Content_Controller($this->plugin_name,$this->version);
		$woocommerce_order_lp_express_metabox      = new Woocommerce_Order_Lp_Express_Metabox($this->plugin_name,$this->version);
		$woocommerce_lp_express_options_controller = new Woocommerce_Lp_Express_Options_Controller($this->plugin_name, $this->version);
		$woocommerce_lp_express_shipping_rules     = new Woocommerce_Lp_Express_Shipping_Rules_Controller($this->plugin_name, $this->version);

		//Shipping label ajax hooks
        $this->loader->add_action('wp_ajax_lp_generate_label',          $woocommerce_lp_express_admin,               'generate_shipping_label');

        //Validate serialized data
        $this->loader->add_action('wp_ajax_validate_serialized_data',   $woocommerce_lp_express_admin,               'validate_serialized_data');

        //Test auth
        $this->loader->add_action('wp_ajax_test_auth_data',             $woocommerce_lp_express_admin,                'test_authorization_data');

        //Clear international trash data
        $this->loader->add_action('wp_ajax_clear_int_data',             $woocommerce_lp_express_options_controller,    'clear_int_fixed_data');

        //Get product categories
        $this->loader->add_action('wp_ajax_lpx-product-categories',     $woocommerce_lp_express_options_controller,    'get_product_categories');

        //Get product categories
        $this->loader->add_action('wp_ajax_lpx-shipping-methods',       $woocommerce_lp_express_options_controller,    'get_lpexpress_available');

        //Save shipping rules options
        $this->loader->add_action('wp_ajax_lpx-save-shipping-rules',    $woocommerce_lp_express_shipping_rules,    'save_shipping_rules_ajax');

        //Save shipping rules options
        $this->loader->add_action('wp_ajax_lpx-get-shipping-rules',     $woocommerce_lp_express_shipping_rules,    'get_shipping_rules_ajax');

        //Manifest ajax hooks
        $this->loader->add_action('wp_ajax_lp_call_courier',            $woocommerce_lp_express_admin,                'call_courier');
        $this->loader->add_action('call_courier',                       $woocommerce_lp_express_admin,                'call_courier',10,2);
		
        $this->loader->add_action('admin_init',                         $woocommerce_lp_express_admin,                'admin_settings_options');
        $this->loader->add_action('wp_ajax_lp_load_tab',                $woocommerce_lp_express_content_loader,      'admin_tab_content');
		$this->loader->add_action('admin_menu',                         $woocommerce_lp_express_admin ,               'admin_settings_page');
        $this->loader->add_action('admin_enqueue_scripts',              $woocommerce_lp_express_admin,                'admin_load_assets');
		
		$this->loader->add_action('add_meta_boxes',                     $woocommerce_order_lp_express_metabox,       'lp_express_register_metabox');
		$this->loader->add_action('save_post',                          $woocommerce_order_lp_express_metabox,       'lp_express_save_metabox_content');
	}
	
    /**
    * Registers shipping methods into woocommerce shipping method array calls
    **/
    private function define_public_hooks() {

        $woocomerce_lp_express_public                      = new Woocommerce_Lp_Express_Public($this->plugin_name, $this->version);
        $woocomerce_lp_express_shipping_methods_controller = new Woocommerce_Lp_Express_Shipping_Methods_Controller($this->plugin_name, $this->version);

        $this->loader->add_action('wp_enqueue_scripts', $woocomerce_lp_express_public,               'admin_load_assets');

        //----------------------------------------------shipping method hooks---------------------------------------------------//
        $this->loader->add_filter('woocommerce_shipping_methods', $woocomerce_lp_express_shipping_methods_controller, 'add_lp_express_courrier_shipping_method');
        $this->loader->add_filter('woocommerce_shipping_methods', $woocomerce_lp_express_shipping_methods_controller, 'add_lp_express_post_office_shipping_method');
        $this->loader->add_filter('woocommerce_shipping_methods', $woocomerce_lp_express_shipping_methods_controller, 'add_lp_express_24_terminal_shipping_method');
        $this->loader->add_filter('woocommerce_shipping_methods', $woocomerce_lp_express_shipping_methods_controller, 'add_lp_express_international_shipping_method');

        $this->loader->add_action('woocommerce_shipping_init',    $woocomerce_lp_express_shipping_methods_controller, 'lp_express_courrier_shipping_method_init');
        $this->loader->add_action('woocommerce_shipping_init',    $woocomerce_lp_express_shipping_methods_controller, 'lp_express_post_office_shipping_method_init');
        $this->loader->add_action('woocommerce_shipping_init',    $woocomerce_lp_express_shipping_methods_controller, 'lp_express_24_terminal_shipping_method_init');
        $this->loader->add_action('woocommerce_shipping_init',    $woocomerce_lp_express_shipping_methods_controller, 'lp_express_international_shipping_method_init');
        //----------------------------------------------------------------------------------------------------------------------//

        //----------------------------------------------terminal fields---------------------------------------------------//
        $this->loader->add_filter('woocommerce_checkout_fields', $woocomerce_lp_express_shipping_methods_controller, 'register_terminal_fields');
        $this->loader->add_action('woocommerce_checkout_after_customer_details',    $woocomerce_lp_express_shipping_methods_controller, 'display_terminal_field');
        $this->loader->add_action('woocommerce_checkout_update_order_meta',    $woocomerce_lp_express_shipping_methods_controller, 'save_custom_field_data',10,2);
        //----------------------------------------------------------------------------------------------------------------//
    }
}