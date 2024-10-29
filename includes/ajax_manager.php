<?php

if( ! function_exists( 'st_wc_activate_license' ) ) {
    function st_wc_activate_license( $return = false ) {
        
        $licenseKey = '';
        $productId = '';
        if( isset( $_POST['licenseKey'] ) && !empty( $_POST['licenseKey'] ) )
            $licenseKey = sanitize_text_field( $_POST['licenseKey'] );
        if( isset( $_POST['productId'] ) && !empty( $_POST['productId'] ) )
            $productId = sanitize_text_field( $_POST['productId'] );

        $status = STAwesome()->api->getLicenseStatus( $licenseKey, $productId );

        if( $status == StWcLicenseManager::STATUS_VALID ) {
            // get license data
            $data = STAwesome()->api->getLicenseData( $licenseKey, $productId );

            // save license data
            if( isset( $data ) && !empty( $data ) ) {
                $componentName = '';
                foreach( STAwesome()->getComponentsObject() as $component ) {
                    if( defined( get_class( $component ) . "::PRODUCT_ID" ) && $component::PRODUCT_ID == $productId) {
                        $componentName = $component::internalName;
                        break;
                    }
                }

                if( !empty( $componentName ) ) {
                    $obj = json_decode( $data );
                    STAwesome()->license->setLicense( $componentName, $obj->licenseCode );
                    STAwesome()->license->setLicenseData( $componentName, $obj );
                }
            }

            if( $return )
                return $data;
            else
                echo wp_send_json( $data ); 
        }
        else {
            // return error message
            if( $return )
                return $status;
            else { 
                echo wp_send_json([
                    'resultType'    => 'error',
                    'message'       => $status
                ]);
            }
        }

        die();
    }
}

if( !function_exists( 'st_wc_update_component' ) ) {
    function st_wc_update_component() {
        $productId = '';
        $wpnonce = '';
        if( isset( $_POST['productId'] ) && !empty( $_POST['productId'] ) )
            $productId = sanitize_text_field( $_POST['productId'] );
        if( isset( $_POST['wpnonce'] ) && !empty( $_POST['wpnonce'] ) )
            $wpnonce = sanitize_text_field( $_POST['wpnonce'] );

        // if the request is not valid, return the error
        if( wp_verify_nonce( $wpnonce, 'update' ) === false ) {
            echo wp_send_json([
                'status'    => false,
                'error'     => true,
                'message'   => new \WP_Error( 'invalid_nonce', __( 'Invalid request', STAwesome()->textDomain ) ) 
            ]);
            die();
        }

        $result = STAwesome()->api->downloadUpdate( $productId );

        if( is_wp_error( $result ) ) {
            echo wp_send_json([
                'status'    => false,
                'error'     => true,
                'message'   => $result
            ]);
            die();
        }
            
        echo wp_send_json([
            'status'        => true,
            'error'         => false
        ]);
        die();
    }
}

if( !function_exists( 'st_wc_download_component' ) ) {
    function st_wc_download_component( $return = false ) {
        $productId      = '';
        $wpnonce        = '';
        $licenseKey     = '';
        if( isset( $_POST['productId'] ) && !empty( $_POST['productId'] ) )
            $productId = sanitize_text_field( $_POST['productId'] );
        if( isset( $_POST['wpnonce'] ) && !empty( $_POST['wpnonce'] ) )
            $wpnonce = sanitize_text_field( $_POST['wpnonce'] );
        if( isset( $_POST['licenseKey'] ) && !empty( $_POST['licenseKey'] ) )
            $licenseKey = sanitize_text_field( $_POST['licenseKey'] );

        // if the request is not valid, return the error
        if( wp_verify_nonce( $wpnonce, 'update' ) === false ) {

            $error = new \WP_Error( 'invalid_nonce', __( 'Invalid request', STAwesome()->textDomain ) );

            if( $return )
                return $error;
            else {
                echo wp_send_json([
                    'status'    => false,
                    'error'     => true,
                    'message'   => $error
                ]);
                die();
            }
        }

        $result = STAwesome()->api->download( $productId, $licenseKey );

        if( is_wp_error( $result ) ) {

            if( $return )
                return $result;
            else {
                echo wp_send_json([
                    'status'    => false,
                    'error'     => true,
                    'message'   => $result
                ]);
                die();
            }
        }
            
        if( $return )
            return true;
        else {
            echo wp_send_json([
                'status'        => true,
                'error'         => false
            ]);
            die();
        }
    }
}

if( !function_exists( 'st_wc_activate_download' ) ) {
    function st_wc_activate_download() {

        $licenseKey = '';
        $productId = '';
        if( isset( $_POST['licenseKey'] ) && !empty( $_POST['licenseKey'] ) )
            $licenseKey = sanitize_text_field( $_POST['licenseKey'] );
        if( isset( $_POST['productId'] ) && !empty( $_POST['productId'] ) )
            $productId = sanitize_text_field( $_POST['productId'] );

        // activate license
        $status = STAwesome()->api->getLicenseStatus( $licenseKey, $productId );
        if( $status != StWcLicenseManager::STATUS_VALID ) {
            echo wp_send_json([
                'resultType'    => 'error',
                'message'       => $status
            ]);
            die();
        }
        
        // download and install the component if license is valid
        $result = st_wc_download_component( true );
        if( is_wp_error( $result ) ) {
            echo wp_send_json([
                'status'    => false,
                'error'     => true,
                'message'   => $result
            ]);
            die();
        }

        // install license
        STAwesome()->requireComponents();
        STAwesome()->loadComponents();
        $result = json_decode( st_wc_activate_license( true ) );
        if( property_exists( $result, 'resultType' ) && $result->resultType == 'error' ) {
            echo wp_send_json([
                'resultType'    => 'error',
                'message'       => $result
            ]);
            die();
        }

        echo wp_send_json([
            'status'        => true,
            'error'         => false
        ]);
    }
}

if( !function_exists( 'st_wc_activate_plan' ) ) {
    function st_wc_activate_plan( $return = false ) {

        $planKey = '';
        $wpnonce = '';
        if( isset( $_POST['planKey'] ) && !empty( $_POST['planKey'] ) )
            $planKey = sanitize_text_field( $_POST['planKey'] );
        if( isset( $_POST['wpnonce'] ) && !empty( $_POST['wpnonce'] ) )
            $wpnonce = sanitize_text_field( $_POST['wpnonce'] );

        // if the request is not valid, return the error
        if( wp_verify_nonce( $wpnonce, 'st-wc-awesome-activate-plan' ) === false ) {
            echo wp_send_json([
                'status'    => false,
                'error'     => true,
                'message'   => new \WP_Error( 'invalid_nonce', __( 'Invalid request', STAwesome()->textDomain ) )
            ]);
            die();
        }

        $status = STAwesome()->api->getPlanLicenseStatus( $planKey );
   
        if( $status == StWcLicenseManager::STATUS_VALID ) {
            // get license data
            $data = STAwesome()->api->getPlanData( $planKey );

            // save license data
            if( isset( $data ) && !empty( $data ) ) {

                $obj = json_decode( $data );
                STAwesome()->license->setPlan( $obj->licenseCode );
                STAwesome()->license->setPlanData( $obj );
            }

            if( $return )
                return $data;
            else
                echo wp_send_json( $data ); 
        }
        else {
            // return error message
            if( $return )
                return $status;
            else { 
                echo wp_send_json([
                    'resultType'    => 'error',
                    'message'       => $status
                ]);
            }
        }

        die();
    }
}