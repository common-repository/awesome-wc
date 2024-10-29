<?php
if (!function_exists('st_wc_apply_coupon_code')) {
    /**  */
    function st_wc_apply_coupon_code() {
        if(!isset($_POST['code'])) { 
            echo wp_send_json([
                'status'    => false,
                'error'     => false,
                'message'   => 'Missing arg(s)'
            ]);
        } else {
            $code = sanitize_text_field( $_POST['code'] );

            if (WC()->cart->has_discount($code)) {
                echo wp_send_json([
                    'status'    => true,
                    'error'     => true,
                    'message'   => 'Code already applied'
                ]);
            } else if (!WC()->cart->add_discount($code)) {
                $all_notices  = WC()->session->get('wc_notices', []);
                $notice_types = apply_filters('woocommerce_notice_types', ['error', 'success', 'notice']);
            
                ob_start();     // buffer output
                $errors = [];   // init
                foreach ($notice_types as $notice_type) {
                    if (wc_notice_count($notice_type) > 0) {
                        $errors[] = strlen($all_notices[$notice_type][0]['notice']) > strlen($all_notices[$notice_type][0]) ? $all_notices[$notice_type][0]['notice'] : $all_notices[$notice_type][0];
                    }
                }
            
                wc_clear_notices();
                $errors = sizeof($errors) ? implode(', ', $errors) : 'Some error ocurred';

                echo wp_send_json([
                    'status'    => true,
                    'error'     => true,
                    'message'   => $errors
                ]);
            } else {
                wc_clear_notices();
                echo wp_send_json([
                    'status'    => true,
                    'error'     => false,
                    'message'   => 'Success' 
                ]);
            }
        }
    }
}