<?php
if( is_admin() ) {
    add_action( 'wp_ajax_st_wc_apply_coupon_code', 'st_wc_apply_coupon_code' );
    add_action( 'wp_ajax_nopriv_st_wc_apply_coupon_code', 'st_wc_apply_coupon_code' );
}