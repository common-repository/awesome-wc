<?php defined('ABSPATH') or exit;
class stWcCouponCode {
    /** @var \stWcCouponCode */
    private static $instance = null;
    
    const internalName          = 'st_wc_awesome_coupon_code';

    private static $_pluginUrl;
    private $pluginUrl;
    private $classesBasePath;
    private $templates;
    private $textDomain;
    private $checkoutAdminPage;
    private $base;
    private $dirBase;
    
    public static function getInstance(){
		if ( !isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
    }
    
    private function __construct(){

        $this->includeFiles();

        $this->base                 = plugins_url( '/', __FILE__ );
        $this->dirBase              = plugin_dir_path( __FILE__ );
        $this->classesBasePath	    = trailingslashit( $this->base . 'includes' );
        $this->pluginUrl		    = $this->base;
        self::$_pluginUrl           = $this->base;
        // $this->textDomain           = 'st-wc-checkout';

        $this->registerTypes();
        $this->addActions();
        $this->createShortcodes();
    }
    
    public function includeFiles() {
        require_once( 'includes/templates.php' );
        require_once( 'includes/ajax-manager.php' );
        require_once( 'includes/hooks.php' );
    }
    
    public function addActions() {
        add_action( 'wp_enqueue_scripts',                   [ $this, 'setupScriptNStyles' ], 900 );
        add_action( 'elementor/preview/enqueue_styles',     [ $this, 'enqueueScripts' ], 900 );
    }

    public function createShortcodes() {
        add_shortcode('st_wc_coupon_code', 'st_wc_coupon_code_shortcode');
    }

    function setupScriptNStyles() {
        if( is_cart() || is_checkout() ) { 
            $this->enqueueScripts();
        }
    }

    function enqueueScripts() {
        wp_enqueue_style( 'st-wc-coupon-code', $this->base . 'assets/styles/style' . STAwesome()->minified() . '.css', null, false, 'all' );
        wp_enqueue_script('stWcCouponCode', $this->base . 'assets/scripts/stWcCouponCode' . STAwesome()->minified() . '.js', null, '', false );
    }

    function registerTypes() {
        STAwesome()->registerType( 'cart' );
        STAwesome()->registerType( 'checkout' );
        STAwesome()->registerShortcode( 'st_wc_coupon_code' );
    }

    public function loadPluginTextdomain() {
        $domain = $this->textDomain;
        $mo_file = WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . get_locale() . '.mo';

        load_textdomain( $domain, $mo_file ); 
        load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
    }
}

