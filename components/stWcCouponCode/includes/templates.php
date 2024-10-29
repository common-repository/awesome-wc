<?php
if ( ! function_exists( 'st_wc_coupon_code_shortcode' ) ) {
	/** add dom element where vue will mount for the component */
	function st_wc_coupon_code_shortcode($atts = [], $content = null, $tag = '') {
        extract(shortcode_atts([
            'textboxbackgroundcolor'    => '',
            'buttoncolor'               => ''
        ], $atts));

        // Start output buffer since the html may need discarding for BW compatibility.
        ob_start();

        ?>
        <st-wc-coupon-code inline-template :coupon-button-style="couponButtonStyle" ajax-url="<?php echo admin_url('admin-ajax.php'); ?>">
            <div class="st-wc-coupon-code st-content">
                <v-container class="pa-0">
                <v-row class="py-4">
                    <v-col class="py-0 d-flex">
                        <div style="width: calc(100% - 50px)">
                            <v-text-field v-model="code" placeholder="Enter code" rounded single-line flat filled dense background-color="<?php echo $textboxbackgroundcolor; ?>" @keyup.enter="applyCode" hide-details class="awesome-coupon-field"></v-text-field>
                        </div>
                        <div class="text-right" style="width: 50px">
                            <v-btn class="pa-0 awesome-coupon-button" style="width: 40px !important; min-width: inherit !important; height: 40px; border-radius: 50%;" small depressed @click="applyCode" :loading="applying" color="<?php echo $buttoncolor; ?>">
                                <v-icon>mdi-plus</v-icon>
                            </v-btn>
                        </div>
                    </v-col>
                </v-row>
                </v-container>
                
                <div v-if="errorMessage.length">
                    <v-alert type="error" dense dismissible class="text-left">{{ errorMessage }}</v-alert>
                </div>
            </div>
        </st-wc-coupon-code>
        
        <?php

        // Send output buffer.
        return ob_get_clean();
	}
}