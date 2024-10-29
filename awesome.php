<?php
/*
Plugin Name: Awesome for WC
Plugin URI: https://awesomeplugin.com/
Description: Bundle of very useful WooCommerce components to maximize sales and customer engagement
Version:  1.0.1
Author: Softech Corporation
Author URI: https://softechpr.com
*/

defined('ABSPATH') or exit;

class StWcAwesome {
    /** @var \StWcAwesome */

    // Constant to put the plugin in development mode
    const DEBUG                 = false;

	private static $instance    = null;
    public $textDomain;
    private $components;
    private $base;
    private $dirBase;
    private $registeredTypes;
    private $registeredPages;
    private $registeredShortcodes;
    private $registeredElementorWidgets;
    private $shortcodeFound;
    public $fn;
    public $api;
    public $license;
    
    public static function getInstance(){
		if ( !isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
    }
    
    private function __construct() {
        $this->base                         = plugins_url( '/', __FILE__ );
        $this->dirBase                      = plugin_dir_path( __FILE__ );
        $this->components                   = [];
        $this->textDomain                   = 'st-wc-awesome';

        $this->registeredPages              = [];
        $this->registeredTypes              = [];
        $this->registeredShortcodes         = [];
        $this->registeredElementorWidgets   = [];

        $this->shortcodeFound               = false;

        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            $this->includeFiles();
            $this->addActions();
    
            $this->fn                       = StWcAwesomeFunctions::getInstance();
            $this->api                      = StWcAPI::getInstance();
            $this->license                  = StWcLicenseManager::getInstance();
    
            if(is_admin()) {
                StWcAwesomeAdminPage::getInstance();
            }
        } else if (is_admin()) {
            add_action('admin_notices', [$this, 'wooCommerceNotice'], 899);
        }
    }

    /** Display WooCommerce notice */
    public function wooCommerceNotice() {
        ?>
        <div class="notice notice-error">
            <p><strong>
                Awesome WooCommerce Plugins requires WooCommerce to be installed and active.
                Download WooCommerce <a href="https://wordpress.org/plugins/woocommerce/">here</a>.
            </strong></p>
        </div>
        <?php
    }
    
    public function includeFiles() {
        // Common functions
        include_once( 'includes/class.functions.php' );

        // API
        include_once( 'includes/class.api.php' );

        // License manager
        include_once( 'includes/class.licenseManager.php' );

        // Admin
        if( is_admin() ) {
            include_once( 'includes/ajax_manager.php' );
            include_once( 'includes/hooks.php' );
            include_once( 'includes/admin/admin-page.php' );
        }

        // Components
        $this->requireComponents();
    }
    
    public function addActions() {

        add_action( 'wp_enqueue_scripts',               [ $this, 'loadScriptsNStyles' ], 899);
        add_action( 'wp_enqueue_scripts',               [ $this, 'loadVueForElementor' ], 900);
        // add_action( 'wp_enqueue_scripts', [ $this, 'loadVue' ], 999);

        add_action('elementor/preview/enqueue_styles',  [ $this, 'loadAssets' ], 899);
        add_action('elementor/preview/enqueue_styles',  [ $this, 'loadVueForElementor' ], 900);

        add_action( 'elementor/editor/before_enqueue_scripts',   [ $this, 'loadElementorIcons' ] );

        // add_action( 'wp_body_open', [ $this, 'appWrapperStart' ] );
        // add_action( 'wp_footer', [ $this, 'appWrapperEnd' ] );

        add_action( 'init', [ $this, 'loadPluginTextdomain' ] );

        add_action( 'plugins_loaded', [ $this, 'loadComponents' ] );

        // hook action to detect if the post has any registered shortcodes
        add_action('the_posts', [ $this, 'hasShortcode' ], 999, 2 );

        // Elementor
        add_action( 'elementor/elements/categories_registered', [ $this, 'addElementorCategory' ] );
    }

    function loadScriptsNStyles() {
        if( $this->needToLoadVue() || $this->shortcodeFound ) {
            $this->loadAssets();
        }
    }

    function loadVue() {
        if( $this->needToLoadVue() || $this->shortcodeFound )
            $this->loadVueObj();
    }

    function loadAssets() {
        wp_enqueue_style( 'st-wc-roboto', $this->base. 'assets/css/roboto.font.css', null, false, 'all' );
        wp_enqueue_style( 'st-wc-material-icons', $this->base . 'assets/css/materialdesignicons.min.css', null, false, 'all' ); // already minified - external source: 'https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css'
        wp_enqueue_style( 'st-wc-vuetify', $this->base . 'assets/css/vuetify-2.3.16.css', null, false, 'all' ); // already minified
        wp_enqueue_style( 'st-wc-wrapper', $this->base . 'assets/css/stWcAwesome' . $this->minified() . '.css', null, false, 'all' );

        if( self::DEBUG ) {
            // wp_enqueue_script( 'st-wc-vue', 'https://cdn.jsdelivr.net/npm/vue@2.6.0/dist/vue.js', [], '2.6.0', false);
            wp_enqueue_script( 'st-wc-vue', $this->base . 'assets/scripts/vue@2.6.0.js', [], '2.6.0', false);
        }
        else {
            // wp_enqueue_script( 'st-wc-vue', 'https://cdn.jsdelivr.net/npm/vue@2.6.0', [], '2.6.0', false);
            wp_enqueue_script( 'st-wc-vue', $this->base . 'assets/scripts/vue@2.6.0.min.js', [], '2.6.0', false);
        }

        wp_enqueue_script( 'vuetify', $this->base . 'assets/scripts/vuetify@2.1.4.min.js', [], '2.1.4', false); // already minified - source: https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js
        wp_enqueue_script( 'axios', $this->base . 'assets/scripts/axios.min.js', null, '', false );
    }

    function loadVueForElementor() {
		// if ( $this->fn->isElementorEditMode() ) 
            // $this->loadVueObj();

        if( wp_script_is( 'st-wc-vue', 'enqueued' ) ) {
            wp_enqueue_script('st-initialize-modular', $this->base . 'assets/scripts/stInitializeModular' . $this->minified() . '.js', null, '', true );
        }
    }

    function loadVueObj() {
        wp_enqueue_script('st-initialize', $this->base . 'assets/scripts/stInitialize' . $this->minified() . '.js', null, '', true );
    }

    function loadElementorIcons() {
        wp_enqueue_style( 'st-wc-awesome-elementor', $this->base . 'assets/css/stWcAwesomeElementor' . $this->minified() . '.css', null, false, 'all' );
    }

    function needToLoadVue() {
        foreach( $this->registeredPages as $page ) {
            if( is_page( $page ) ) {
                // echo 'page: ' . $page;
                return true;
            }
        }

        foreach( $this->registeredTypes as $type ) {
            if( call_user_func( 'is_' . $type ) ) {
                // echo 'type: ' . $type;
                return true;
            }
        }

        if( !empty( $this->registeredElementorWidgets ) ) {
            // echo 'elementor widget';
            return true;
        }

        return false;
    }

    function hasShortcode( $posts, $wp_query ) {
        if ( $wp_query->is_main_query() ) {
            $this->shortcodeFound = false;

            if ( empty( $posts ) || is_admin() )
                return $posts;
                
            foreach( $posts as $post ) {
                foreach( $this->registeredShortcodes as $shortcode ) {
                    if( has_shortcode( $post->post_content, $shortcode ) ) {
                        $this->shortcodeFound = true;
                        break;
                    }
                }
            }
        }

        return $posts;
    }

    function requireComponents() {
        $components = array_diff(scandir( $this->dirBase . 'components' ), ['..', '.']);
        foreach( $components as $component ) {
            require_once( 'components/' . $component . '/' . $component . '.php' );
        }
    }

    function loadComponents() {
        $this->components = [];
        $components = array_diff(scandir( $this->dirBase . 'components' ), ['..', '.']);
        foreach( $components as $component ) {
            $this->components[$component] = call_user_func_array( $component . '::getInstance', [] );
        }
    }

    function appWrapperStart() {
        echo '<div id="stAppWrapper" style="white"><v-app>';
    }
    function appWrapperEnd() {
        echo '</v-app></div>';
    }

    function addElementorCategory( $elements_manager ) {
        $elements_manager->add_category('awesome', [
            'title' => 'Awesome Widgets',
            'icon'  => 'fas fa-plug',
        ]);
    }

    function getBase() {
        return $this->base;
    }

    function getDirBase() {
        return $this->dirBase;
    }

    /**
     *  get resource by name
     */
    public function getImage($name) {
        $image = 'assets/img/' . $name;
        if (file_exists($this->getDirBase() . $image)) {
            return $this->getBase() . $image;
        } else {
            return '';
        }
    }

    function getComponentsDirBase() {
        return $this->dirBase . '/components';
    }

    /** 
     *  get active components
     *  @return \array - active components
     */
    function getComponents() {
        return array_keys($this->components);
    }

    /** 
     *  verify if component installed
     *  @param \string $component - component to find
     *  @return \boolean
     */
    function isComponentInstalled($component) {
        return in_array($component, array_keys($this->components));
    }

    /** 
     *  verify if component installed and active
     *  @param \string $component - component to find
     *  @return \boolean
     */
    public function isComponentInstalledActive( $componentName ) {
        // check if component is active
        $component = $this->getComponentByInternalName( $componentName );
        if( $component === null )
            return false;

        return $component->settings->getValue('enabled');
	}

    function registerType( $type ) {
        if( !empty( $type ) )
            $this->registeredTypes[] = $type;
    }
    function registerPage( $page ) {
        if( !empty( $page ) )
            $this->registeredPages[] = $page;
    }
    function registerShortcode( $shortcode ) {
        if( !empty( $shortcode ) )
            $this->registeredShortcodes[] = $shortcode;
    }
    function registerElementorWidget( $componentName ) {
        if( !empty( $componentName ) )
            $this->registeredElementorWidgets[] = $componentName;
    }

    function getRegisteredElementorWidgets() {
        return $this->registeredElementorWidgets;
    }

    function minified() {
        return self::DEBUG ? '' : '.min';
    }

    function getComponentsObject() {
        return $this->components;
    }

    function getComponentByProductId( $productId ) {
        foreach( $this->components as $component ) {
            if( defined( get_class( $component ) . "::PRODUCT_ID" ) && $component::PRODUCT_ID == $productId )
                return $component;
        }

        return null;
    }

    function getComponentByInternalName( $internalName ) {
        foreach( $this->components as $component ) {
            if( defined( get_class( $component ) . "::internalName" ) && $component::internalName == $internalName )
                return $component;
        }

        return null;
    }

    public function getTemplatesPath() { 
        return $this->dirBase . 'templates/'; 
    }

    public function loadPluginTextdomain() {
        $domain = $this->textDomain;
        $mo_file = WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . get_locale() . '.mo';

        load_textdomain( $domain, $mo_file ); 
        load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
    }
}

StWcAwesome::getInstance();

/**
 *
 * @return \StWcAwesome
 */
function STAwesome() {
	return StWcAwesome::getInstance();
}