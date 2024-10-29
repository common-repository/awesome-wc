<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class stWcAwesomeBusinessHours {
    /**
     *  stWcAwesomeBusinessHours object
     *  @var \stWcAwesomeBusinessHours
     */

    private static $instance    = null;
    const internalName          = 'st_wc_awesome_business_hours';
    const PRODUCT_ID            = 18248;
    const VERSION               = '1.0.0';

    private $base;
    private $dirBase;
    public  $settings;
    private $shortcodeFound;
    public $templatePath;
    
    /**
	 *  Return current instance of stWcAwesomeBusinessHours object
	 *  @return \stWcAwesomeBusinessHours
	 */
    public static function getInstance() {
		if (!isset(self::$instance)) {
		    self::$instance = new self();
        }
        
		return self::$instance;
    }
    
    /** init */
    private function __construct() {
        $this->includeRequiredFiles();

        $this->base             = plugins_url('/', __FILE__);
        $this->dirBase          = plugin_dir_path(__FILE__);
        $this->settings         = stWcAwesomeBusinessHoursSettings::getInstance();
        $this->shortcodeFound   = false;
        $this->templatePath     = $this->dirBase . 'templates/';

        if ($this->settings->getValue('enabled')) {
            $this->includeFiles();
            $this->registerTypes();
            $this->addActions();
            $this->createShortcodes();
        }

        if (is_admin()) {
            stWcAwesomeBusinessHoursAdminPage::getInstance();
        }
    }
    
    /** Load required files */
    public function includeRequiredFiles() {
        require_once('includes/settings.php');
        require_once('includes/ajax-manager.php');
        require_once('includes/hooks.php');
        require_once('includes/admin/stWcAwesomeBusinessHoursAdminPage.php');
    }

    /** Load files when object is enabled */
    public function includeFiles() {
        require_once('includes/templates.php');
    }

    /** Add hooks */
    public function addActions() {
        add_action('wp_enqueue_scripts',                        [$this, 'loadScriptsNStyles'], 900);
        
        add_action('elementor/preview/enqueue_styles',          [$this, 'enqueueScripts'], 900);
        add_action('elementor/preview/enqueue_styles',          [$this, 'enqueueVueForElementor'], 900);

        add_action('elementor/frontend/the_content',            [$this, 'hasShortcodeElementor']);

        if (did_action('elementor/loaded')) {
            add_action('elementor/widgets/widgets_registered',  [$this, 'includeElementorWidgets']);
        }
    }

    /** Create Shortcodes */
    public function createShortcodes() {
        add_shortcode('st_wc_awesome_business_hours', 'st_wc_business_hours_shortcode');
    }

    /**
	 *  Get base
	 *  @return \string
	 */
    function getBase() { return $this->base; }

    /**
	 *  Get dirBase
	 *  @return \string
	 */
    function getDirBase() { return $this->dirBase; }

    /**
	 *  Get templates path
	 *  @return \string
	 */
    public function getTemplatesPath() { return $this->templatePath; }

    /** Enqueue scripts and style in shop page */
    function loadScriptsNStyles() {
        global $post;

        if (!empty($post) and has_shortcode($post->post_content, 'st_wc_awesome_business_hours')) { 
            $this->shortcodeFound = true;
            $this->enqueueScripts();
            $this->enqueueVue(STAwesome()->fn->isElementorPage($post->ID));
        }
    }

    /** Enqueue scripts and style for business hours */
    function enqueueScripts() {
        wp_enqueue_style('st-wc-business-hours-style',  $this->base . 'assets/styles/style' . STAwesome()->minified() . '.css', null, false, 'all');
        wp_enqueue_script('st-wc-business-hours',       $this->base . 'assets/scripts/stWcBusinessHours' . STAwesome()->minified() . '.js', null, '', false);
    }

    function enqueueVue( $isElementor = false ) {
        $script = $isElementor ? 'stWcBHVueElementor' : 'stWcBHVue';
        wp_enqueue_script('st-wc-business-hours-vue', $this->base . 'assets/scripts/'. $script . STAwesome()->minified() . '.js', null, '', false);
    }

    function enqueueVueForElementor() {
        $this->enqueueVue( true );
    }

    /** Check if elementor has shortcode */
    function hasShortcodeElementor($content) {
        if (has_shortcode($content, 'st_wc_awesome_business_hours')) {
            $this->shortcodeFound = true;
        }
        
        return $content;
    }

    /** Register types/shortcodes */
    function registerTypes() {
        STAwesome()->registerShortcode('st_wc_awesome_business_hours');
    }

    /** Register widget */
    function includeElementorWidgets() {
        ?>
		<style scoped>
            .elementor-control-days-business-hours .elementor-repeater-row-tool, .elementor-control-days-business-hours .elementor-button-wrapper { display: none !important }
		</style>
		<?php
            
        require_once('includes/elementor/widgetAwesomeBusinessHours.php');
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new widgetAwesomeBusinessHours());
    }
}

