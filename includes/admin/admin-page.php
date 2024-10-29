<?php defined('ABSPATH') or exit;
class StWcAwesomeAdminPage {
    /** @var \StWcAwesomeAdminPage */
    private static $instance    = null;
    private $pageId             = 'st-wc-awesome';
    private $pageTitle          = 'Awesome Manager';
    private $menuTitle          = 'Awesome Manager';
    private $slug               = 'st-wc-awesome';
    private $menuPosition       = 90;
    
    public static function getInstance(){
		if ( !isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
    }

    private function __construct() {
        // add plugin settings page in the Wordpress menu
        add_action( 'admin_menu', [ $this, 'addPluginPage' ] );

        if( isset( $_GET['page'] ) && !empty( $_GET['page'] ) && $_GET['page'] == $this->slug ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'loadScriptsNStyles' ], 899);
            add_action( 'admin_enqueue_scripts', [ $this, 'loadAdminComponent' ], 900);
            add_action( 'admin_enqueue_scripts', [ $this, 'setupLoadVueObj' ], 999);
            add_action( 'admin_head', [ StWcAwesomeFunctions::getInstance(), 'hideAdminNotices' ], 1 );

            add_filter('admin_footer_text', [ $this, 'removeDefaultWpFooter' ]);
        }
    }

    function loadScriptsNStyles() {
        // wp_enqueue_style( 'st-wc-awesome-roboto', 'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900', null, false, 'all' );
        wp_enqueue_style( 'st-wc-awesome-roboto', STAwesome()->getBase() . 'assets/css/roboto.font.css', null, false, 'all' );
        wp_enqueue_style( 'st-wc-awesome-material-icons', STAwesome()->getBase() . 'assets/css/materialdesignicons.min.css', null, false, 'all' );
        wp_enqueue_style( 'st-wc-awesome-vuetify', STAwesome()->getBase() . 'assets/css/vuetify-2.min.css', null, false, 'all' );
        wp_enqueue_style( 'st-wc-awesome-wrapper', STAwesome()->getBase() . 'assets/css/stWcAwesome.min.css', null, false, 'all' );
        wp_enqueue_script('st-wc-awesome-vue', STAwesome()->getBase() . 'assets/scripts/vue@2.6.0.min.js', [], '2.6.0', false);
        wp_enqueue_script('st-wc-awesome-vuetify', STAwesome()->getBase(). 'assets/scripts/vuetify@2.1.4.min.js', [], '2.1.4', false); 
        wp_enqueue_script('st-wc-awesome-axios', STAwesome()->getBase() . 'assets/scripts/axios.min.js', null, '', false );
    }

    function loadAdminComponent() {
        wp_enqueue_script('st-wc-admin', STAwesome()->getBase() . 'assets/scripts/stWcAdmin' . STAwesome()->minified() . '.js', null, '', false );
    }

    function setupLoadVueObj() {
        wp_enqueue_script('st-initialize', STAwesome()->getBase() . 'assets/scripts/stInitialize' . STAwesome()->minified() . '.js', null, '', true );
    }

    function getSlug() {
        return $this->slug;
    }

    /** add settings button to the administrator menu */
    public function addPluginPage() {
        $hook = add_menu_page( $this->pageTitle, $this->menuTitle, 'administrator', $this->slug, [ $this, 'createAdminPage' ], STAwesome()->getBase() . 'assets/img/wp-icon.png', $this->menuPosition );
    }

    public function removeDefaultWpFooter() {
        return '';
    }

    /**
     * Options page callback
     */
    public function createAdminPage() {
        // get bundle information
        $bundle = null;
        if( STAwesome()->license->isLicenseInstalled() ) {
            $bundle = STAwesome()->license->getLicensePlanData();
            
            // check bundle license status 
            // get license data
            $data = STAwesome()->api->getPlanData( $bundle->licenseCode );

            // save license data
            if( isset( $data ) && !empty( $data ) ) {
                $obj = json_decode( $data );
                STAwesome()->license->setPlanData( $obj );
            }

            $bundle = STAwesome()->license->getLicensePlanData();
        }

        // get products information
        $products = STAwesome()->api->getProducts();

        ?>
        <div id="stAppWrapper" style="margin-left:-20px;">
            <v-app id="awp-<?php echo STAwesome()->fn->getNewAppID(); ?>">
                <div is="st-wc-admin" inline-template v-cloak class="transparent fill-height" 
                        :products='<?php echo STAwesome()->fn->toJSON( $products ); ?>' 
                        admin-url="<?php echo admin_url( 'admin.php' ) . '?page='; ?>"
                        nonce="<?php echo wp_create_nonce( 'update' ); ?>"
                        plan-nonce="<?php echo wp_create_nonce( 'st-wc-awesome-activate-plan' ); ?>"
                        :bundle='<?php echo json_encode( $bundle ); ?>'>
                    <div class="st-wc-admin st-content">

                        <?php wc_get_template( 'app-bar.php', [ 'bundle' => $bundle ], '', STAwesome()->getTemplatesPath() ); ?>
                        
                        <div class="pa-5">

                            <div class="headline mb-4">Installed components</div>

                            <v-row>
                                <template v-for="(component, ix) in localProducts">
                                    <v-col cols="12" sm="12" md="6" lg="4" xl="3" :key="ix" v-if="component.installed">
                                        <v-card elevation="1" :loading="component.updating" :disabled="component.updating">
                                            <div class="d-flex flex-no-wrap justify-space-between" v-if="component.logo.length > 0" style="height:185px;">
                                                <v-img :src="component.logo" max-width="100" contain></v-img>

                                                <div>
                                                    <v-card-title class="body-1" style="font-size:15px !important;color:#FF7052;">
                                                        {{ component.title }}

                                                        <v-spacer></v-spacer>

                                                        <v-btn icon dense small :href="adminUrl + component.slug" v-if="component.licenseInstalled || (component.type == 'free' && component.installed)"><v-icon small>mdi-open-in-new</v-icon></v-btn>
                                                        <v-icon v-else-if="component.type == 'pro'" small>mdi-lock</v-icon>
                                                    </v-card-title>
                                                    <v-card-subtitle style="font-size:22px !important;">
                                                        {{ component.subtitle }}
                                                    </v-card-subtitle>

                                                    <v-card-text class="pb-1 grey--text">
                                                        
                                                        {{ component.description }}

                                                        <div class="d-flex flex-no-wrap justify-space-between caption mt-4"> 
                                                            <v-btn rounded depressed x-small color="primary" @click="update(component)" v-if="component.needUpdate">Update</v-btn>
                                                            <v-chip color="orange" x-small class="white--text" v-if="component.licenseInstalled && component.license != null && component.license.expired">Expired</v-chip>
                                                            <v-spacer></v-spacer>
                                                            <div class=" text-right grey--text text--darken-1 align-self-end">latest {{ component.version }}</div>
                                                        </div>
                                                    </v-card-text>
                                                </div>
                                            </div>
                                            <div v-else style="height:185px;">
                                                <v-card-title class="body-1" style="font-size:15px !important;color:#FF7052;">
                                                    {{ component.title }}

                                                    <v-spacer></v-spacer>

                                                    <v-btn icon dense small :href="adminUrl + component.slug" v-if="component.licenseInstalled || (component.type == 'free' && component.installed)"><v-icon small>mdi-open-in-new</v-icon></v-btn>
                                                    <v-icon v-else-if="component.type == 'pro'" small>mdi-lock</v-icon>
                                                </v-card-title>
                                                <v-card-subtitle style="font-size:22px !important;">{{ component.subtitle }}</v-card-subtitle>
                                                <v-card-text class="pb-1" grey--text>
                                                    {{ component.description }}

                                                    <div class="d-flex flex-no-wrap justify-space-between caption mt-4"> 
                                                        <v-btn rounded depressed x-small color="primary" @click="update(component)" v-if="component.needUpdate">Update</v-btn>
                                                        <v-chip color="orange" x-small class="white--text" v-if="component.licenseInstalled && component.license != null && component.license.expired">Expired</v-chip> 
                                                        <v-spacer></v-spacer>
                                                        <div class=" text-right grey--text align-self-end">latest {{ component.version }}</div>
                                                    </div>
                                                </v-card-text>
                                            </div>
                                            <v-divider></v-divider>
                                            <v-card-actions :class="component.installed && (component.type == 'free' || (component.licenseInstalled && !component.needRenovation)) ? 'green--text' : ''" style="height:44px;">
                                                <v-icon color="green" v-if="component.installed && (component.type == 'free' || (component.licenseInstalled && !component.needRenovation))">mdi-check</v-icon> 
                                                <span v-if="component.installed && (component.type == 'free' || (component.licenseInstalled && !component.needRenovation))">Ready to use</span>
                                                <v-btn depressed small v-else-if="component.installed && !component.needRenovation && component.type == 'pro'" class="st-wc-text-transform-none" @click="openLicenseDialog(component)">Enter license key</v-btn>
                                                <v-btn depressed small v-else-if="component.installed && component.needRenovation && component.type == 'pro'" class="st-wc-text-transform-none" @click="openLicenseDialog(component)">Update license</v-btn>
                                                <v-btn depressed small v-else-if="!component.installed" class="st-wc-text-transform-none" :href="component.download" target="_blank">Get component</v-btn>

                                                <v-spacer></v-spacer>

                                                <span class="caption" v-if="component.installed">installed {{ component.installedVersion }} </span>
                                            </v-card-actions>
                                        </v-card>
                                    </v-col>
                                </template>
                            </v-row>

                            <div class="headline my-5" v-show="componentsAvailable.length > 0">Other components available</div>

                            <v-row>
                                <template v-for="(component, ix) in componentsAvailable">
                                    <v-col cols="12" sm="12" md="6" lg="4" xl="3" :key="ix" v-if="!component.installed">
                                        <v-hover>
                                            <template v-slot:default="{ hover }">
                                                <v-card elevation="1" :class="{ 'on-hover': hover }" :loading="component.updating" :disabled="component.updating">
                                                    <div class="d-flex flex-no-wrap justify-space-between" v-if="component.logo.length > 0" style="height:165px;">
                                                        <v-img :src="component.logo" max-width="100" contain></v-img>

                                                        <div>
                                                            <v-card-title class="body-1" style="font-size:15px !important;color:#FF7052;">
                                                                {{ component.title }}
                                                            </v-card-title>
                                                            <v-card-subtitle class="grey--text text--darken-2" style="font-size:22px !important;">
                                                                {{ component.subtitle }}
                                                            </v-card-subtitle>

                                                            <v-card-text class="pb-1 grey--text">
                                                                {{ component.description }}

                                                                <div class="d-flex flex-no-wrap justify-space-between caption mt-4"> 
                                                                    <v-btn rounded depressed x-small color="primary" @click="update(component)" v-if="component.needUpdate">Update</v-btn>
                                                                    <v-spacer></v-spacer>
                                                                    <div class=" text-right grey--text align-self-end">latest {{ component.version }}</div>
                                                                </div>
                                                            </v-card-text>
                                                        </div>
                                                    </div>
                                                    <div v-else style="height:165px;">
                                                        <v-card-title class="headline">
                                                            {{ component.title }}

                                                            <v-spacer></v-spacer>

                                                            <v-btn icon dense :href="adminUrl + component.slug" v-if="component.licenseInstalled || (component.type == 'free' && component.installed)"><v-icon>mdi-open-in-new</v-icon></v-btn>
                                                            <v-icon v-else-if="component.type == 'pro'">mdi-lock</v-icon>
                                                        </v-card-title>
                                                        <v-card-subtitle>{{ component.subtitle }}</v-card-subtitle>
                                                        <v-card-text class="pb-1">
                                                            {{ component.description }}

                                                            <div class="d-flex flex-no-wrap justify-space-between caption mt-4"> 
                                                                <v-btn rounded depressed x-small color="primary" @click="update(component)" v-if="component.needUpdate">Update</v-btn>
                                                                <v-spacer></v-spacer>
                                                                <div class=" text-right grey--text align-self-end">latest {{ component.version }}</div>
                                                            </div>
                                                        </v-card-text>
                                                    </div>

                                                    <v-fade-transition>
                                                        <v-overlay v-if="hover" absolute :value="hover">
                                                            <div v-if="component.type == 'pro'">
                                                                <v-btn class="st-wc-text-transform-none ma-1" :href="component.buyUrl" target="_blank" depressed small block color="">
                                                                    Purchase component
                                                                </v-btn>
                                                                <v-btn class="st-wc-text-transform-none ma-1" depressed small block color="success" @click="openLicenseDialog(component)">
                                                                    Activate & download
                                                                </v-btn>
                                                            </div>
                                                            <v-btn class="st-wc-text-transform-none" @click="download(component)" :disabled="component.updating" depressed small dark v-else>
                                                                Download
                                                            </v-btn>
                                                        </v-overlay>
                                                    </v-fade-transition>
                                                </v-card>
                                            </template>
                                        </v-hover>
                                    </v-col>
                                </template>
                            </v-row>
                        </div>
                        <v-dialog v-model="licenseDialog" width="400px" persistent>
                            <v-card flat>
                                <v-card-title>
                                    {{ selected != undefined && selected != null ? selected.title : '' }} license key

                                    <v-spacer></v-spacer>

                                    <v-icon @click="licenseDialog = false">mdi-close</v-icon>
                                </v-card-title>
                                
                                <v-card-text>
                                    <v-card-subtitle class="pl-0">Activate your license to enjoy future updates and support</v-card-subtitle>
                                    <v-alert v-model="alert" text border="left" :color="alertColor" dismissible dense>{{ alertMessage }}</v-alert>
                                    <v-text-field v-model="licenseKey" outlined class="no-border" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxxx" dense
                                        :disabled="activating" @keydown.enter="activateLicense" ref="license" :error-messages="error"></v-text-field>

                                    <v-btn depressed outlined block @click="activateLicense" class="st-wc-text-transform-none white--text" color="primary" :loading="activating">Activate license</v-btn>
                                </v-card-text>
                            </v-card>
                        </v-dialog>

                        <v-dialog v-model="planDialog" width="500px" persistent>
                            <v-card flat>
                                <v-card-title>
                                    Awesome Plan Key

                                    <v-spacer></v-spacer>

                                    <v-icon @click="planDialog = false">mdi-close</v-icon>
                                </v-card-title>

                                <v-card-text>
                                    <v-card-subtitle class="pl-0">Activate your plan license to enjoy of all the plugin components, updates and support</v-card-subtitle>
                                    <v-alert v-model="alert" text border="left" :color="alertColor" dismissible dense>{{ alertMessage }}</v-alert>
                                    <v-text-field v-model="planKey" outlined class="no-border" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxxx" dense
                                        :disabled="activating" @keydown.enter="activatePlan" ref="plan" :error-messages="error"></v-text-field>

                                    <v-btn depressed outlined block @click="activatePlan" class="st-wc-text-transform-none white--text" color="primary" :loading="activating">Activate Plan</v-btn>
                                </v-card-text>
                            </v-card>
                        </v-dialog>

                        <v-dialog v-model="progressDialog" width="400px" persistent>
                            <v-card flat loading>
                                <v-card-title>
                                    {{ selected != undefined && selected != null ? selected.title : '' }} is installing
                                </v-card-title>
                                <v-card-subtitle class="mt-1">The page will refresh once the component has installed</v-card-subtitle>
                                <v-card-text v-show="alert">
                                    <v-alert v-model="alert" text border="left" :color="alertColor" dismissible dense>{{ alertMessage }}</v-alert>
                                    <v-btn depressed outlined block @click="progressDialog = false" class="st-wc-text-transform-none" color="primary" :loading="activating">Close</v-btn>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                    </div>
                </div>
            </v-app>
        </div>
        <?php
    }
}