<?php defined('ABSPATH') or exit;

class stWcAwesomeBusinessHoursAdminPage {

    /**
	 *
	 * @var \stWcAwesomeBusinessHoursAdminPage
	 */
    private static $instance    = null;

    private $pageId             = 'st-wc-awesome-business-hours';
    private $pageTitle          = 'Awesome Business Hours';
    private $menuTitle          = 'Awesome Business Hours';
    private $slug               = 'st-wc-awesome-business-hours';
    private $icon               = '';
    private $menuPosition       = 90;
    
    /** 
     *  Return current instance of stWcAwesomeBusinessHoursAdminPage object
     *  @return \stWcAwesomeBusinessHoursAdminPage
    */
    public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
        }
        
		return self::$instance;
    }

    /** Init */
    private function __construct() {
        /** add plugin settings page in the Wordpress menu */
        add_action('admin_menu', [$this, 'addPluginPage']);

        if(isset($_GET['page']) and $_GET['page'] === $this->slug) {
            add_action('admin_enqueue_scripts', [$this, 'loadScriptsNStyles'], 899);
            add_action('admin_enqueue_scripts', [$this, 'loadAdminComponent'], 900);
            add_action('admin_enqueue_scripts', [$this, 'setupLoadVueObj'], 999);
            add_action('admin_head', [ StWcAwesomeFunctions::getInstance(), 'hideAdminNotices' ], 1);
        }
    }

    /**
	 *  Enqueue scripts and style in admin page
	 *  @return \void
	 */
    function loadScriptsNStyles() {
        wp_enqueue_style('st-wc-awesome-roboto', STAwesome()->getBase() . 'assets/css/roboto.font.css', null, false, 'all');
        wp_enqueue_style('st-wc-awesome-material-icons', STAwesome()->getBase() . 'assets/css/materialdesignicons.min.css', null, false, 'all');
        wp_enqueue_style('st-wc-awesome-vuetify', STAwesome()->getBase() . 'assets/css/vuetify-2.3.16.css', null, false, 'all');
        wp_enqueue_style('st-wc-awesome-wrapper', STAwesome()->getBase() . 'assets/css/stWcAwesome' . STAwesome()->minified() . '.css', null, false, 'all');
        wp_enqueue_style( 'st-wc-awesome-admin-style', STAwesome()->getBase() . 'assets/css/admin' . STAwesome()->minified() . '.css', null, false, 'all' );
        wp_enqueue_script('st-wc-awesome-vue', STAwesome()->getBase() . 'assets/scripts/vue@2.6.0.min.js', [], '2.6.0', false);
        wp_enqueue_script('st-wc-awesome-vuetify', STAwesome()->getBase(). 'assets/scripts/vuetify@2.1.4.min.js', [], '2.1.4', false);
        wp_enqueue_script('st-wc-awesome-axios', STAwesome()->getBase() . 'assets/scripts/axios.min.js', null, '', false);
    }

    /**
	 *  Enqueue scripts and style for component in admin page
	 *  @return \void
	 */
    function loadAdminComponent() {
        $base = stWcAwesomeBusinessHours::getInstance()->getBase();
        wp_enqueue_style('st-wc-business-hours-admin',          $base . 'assets/styles/style' . STAwesome()->minified() . '.css', null, false, 'all');
        wp_enqueue_script('st-wc-business-hours-admin',         $base . 'assets/scripts/stWcBusinessHoursAdmin' . STAwesome()->minified() . '.js', null, '', false);
        wp_enqueue_script('st-wc-business-hours-switch',        $base . 'assets/scripts/forms/stWcSwitch' . STAwesome()->minified() . '.js', null, '', false);
        wp_enqueue_script('st-wc-business-hours-select',        $base . 'assets/scripts/forms/stWcSelect' . STAwesome()->minified() . '.js', null, '', false);
        wp_enqueue_script('st-wc-business-hours-color-picker',  $base . 'assets/scripts/forms/stWcColorPicker' . STAwesome()->minified() . '.js', null, '', false);
        wp_enqueue_script('st-wc-business-hours-day',           $base . 'assets/scripts/stWcDay' . STAwesome()->minified() . '.js', null, '', false);
        wp_enqueue_script('st-wc-business-hours-alert',         $base . 'assets/scripts/stWcAlert' . STAwesome()->minified() . '.js', null, '', false);
        wp_enqueue_script('st-wc-business-hours',               $base . 'assets/scripts/stWcBusinessHours' . STAwesome()->minified() . '.js', null, '', false);
    }

    /**
	 *  Load vue object
	 *  @return \void
	 */
    function setupLoadVueObj() {
        wp_enqueue_script('st-initialize', STAwesome()->getBase() . 'assets/scripts/stInitialize' . STAwesome()->minified() . '.js', null, '', true);
    }

    /**
     *  Add settings button to the administrator menu
     *  @return \void
     */
    public function addPluginPage() {
        $managerSlug = StWcAwesomeAdminPage::getInstance()->getSlug();
        add_submenu_page($managerSlug, $this->pageTitle, $this->menuTitle, 'administrator', $this->slug, [$this, 'createAdmin'], $this->menuPosition);
    }

    /**
     *  Options page callback
     *  @return \void
     */
    public function createAdmin() {
        $settings   = stWcAwesomeBusinessHours::getInstance()->settings;
        $forms      = json_encode($settings->getFormData());
        $settings   = json_encode($settings->getSettings());
        wc_get_template('admin.php', ['settings' => $settings, 'forms' => $forms], '', stWcAwesomeBusinessHours::getInstance()->getTemplatesPath());
    }
}