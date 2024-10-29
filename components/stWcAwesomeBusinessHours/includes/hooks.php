<?php
if(is_admin()) {
    add_action('wp_ajax_st_wc_business_hours_update_option', 'st_wc_business_hours_update_option');
    add_action('wp_ajax_nopriv_st_wc_business_hours_update_option', 'st_wc_business_hours_update_option');
}