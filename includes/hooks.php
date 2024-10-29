<?php
if( is_admin() ) {

    // Activate a license of a previously installed component
    add_action( 'wp_ajax_st_wc_activate_license', 'st_wc_activate_license' );
    add_action( 'wp_ajax_nopriv_st_wc_activate_license', 'st_wc_activate_license' );

    // Update component to the latest version
    add_action( 'wp_ajax_st_wc_update_component', 'st_wc_update_component' );
    add_action( 'wp_ajax_nopriv_st_wc_update_component', 'st_wc_update_component' );

    // download component 
    add_action( 'wp_ajax_st_wc_download_component', 'st_wc_download_component' );
    add_action( 'wp_ajax_nopriv_st_wc_download_component', 'st_wc_download_component' );

    // activate and install component 
    add_action( 'wp_ajax_st_wc_activate_download', 'st_wc_activate_download' );
    add_action( 'wp_ajax_nopriv_st_wc_activate_download', 'st_wc_activate_download' );

    // activate plan 
    add_action( 'wp_ajax_st_wc_activate_plan', 'st_wc_activate_plan' );
    add_action( 'wp_ajax_nopriv_st_wc_activate_plan', 'st_wc_activate_plan' );
}
