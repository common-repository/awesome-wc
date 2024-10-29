<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class StWcLicenseManager {

    private static $instance        = null;
    private $bundleComponentName    = 'st_wc_awesome';

    // URL of the license API
    const API_URL                   = 'https://upo0m28yu9.execute-api.us-east-1.amazonaws.com/';

    // When a renewal is required, send the user to this URL
    const RENEW_URL                 = '';

    // License status
    const STATUS_VALID              = 'valid';
    const STATUS_INVALID            = 'invalid';
    const STATUS_EXPIRED            = 'expired';
    const STATUS_DISABLED           = 'disabled';

    /** 
	 * 	Return current instance of StWcLicenseManager object
	 * 	@param \StWcLicenseManager
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
    }
    
    // Validate the license status for the product id. 
    // *** THIS MUST BE CALLED ALWAYS BEFORE THE isUsable METHOD ***
    public function isValid( $componentName = null ) {

        if( empty( $componentName ) )
            $componentName = $this->bundleComponentName;
        else if( $this->isValid() ) {
            return true;
        }

        $license = json_decode( $this->getLicenseData( $componentName ) );
        if( !isset( $license ) || empty( $license ) )
            return false;
        if( !isset( $license->licenseCode ) || empty( $license->licenseCode ) )
            return false;

        // update license status
        if( $componentName === $this->bundleComponentName )
            $this->setPlanData( $license );
        else
            $this->setLicenseData( $componentName, $license );

        // print_r( $license );
        if( $license->status === self::STATUS_VALID )
            return true;

        return false;
    }

    public function isUsable( $componentName = null ) {

        if( empty( $componentName ) )
            $componentName = $this->bundleComponentName;
        else if( $this->isUsable() ) {
            return true;
        }
        
        $license = json_decode( $this->getLicenseData( $componentName ) );
        if( !isset( $license ) || empty( $license ) )
            return false;
        if( !isset( $license->licenseCode ) || empty( $license->licenseCode ) )
            return false;

        if( property_exists( $license, 'status' ) && $license->status === self::STATUS_VALID || $license->status === self::STATUS_EXPIRED )
            return true;
        // if( $license->productId == STAwesome()->getComponentByInternalName( $componentName )::PRODUCT_ID && time() < strtotime( $license->expirationDate ) )
        //     return true;

		return false;
    }

    public function isAboutToExpire( $componentName = null ) {

        if( empty( $componentName ) )
            $componentName = $this->bundleComponentName;

        $license = json_decode( $this->getLicenseData( $componentName ) );
        if( !isset( $license ) || empty( $license ) )
            return false;
        if( !isset( $license->licenseCode ) || empty( $license->licenseCode ) )
            return false;
        if( !isset( $license->expirationDate ) || empty( $license->expirationDate ) )
            return false;

        if( time() > strtotime( '-30 days', strtotime( $license->expirationDate ) ) && time() <= strtotime( $license->expirationDate ) )
            return true;

        return false;
    }

    private function getErrors() {
        return [
			'no_activations_left' => sprintf( __( '<strong>You have no more activations left.</strong> <a href="%s" target="_blank">Please upgrade to a more advanced license</a> (you\'ll only need to cover the difference).', STAwesome()->textDomain ), 'https://go.elementor.com/upgrade/' ),
			'expired' => sprintf( __( '<strong>Your License Has Expired.</strong> <a href="%s" target="_blank">Renew your license today</a> to keep getting feature updates, premium support and unlimited access to the template library.', STAwesome()->textDomain ), 'https://go.elementor.com/renew/' ),
			'missing' => __( 'Your license is missing. Please check your key again.', STAwesome()->textDomain ),
			'revoked' => __( '<strong>Your license key has been cancelled</strong> (most likely due to a refund request). Please consider acquiring a new license.', STAwesome()->textDomain ),
			'key_mismatch' => __( 'Your license is invalid for this domain. Please check your key again.', STAwesome()->textDomain ),
		];
    }

    public function getErrorMessage( $error ) {
        $errors = $this->getErrors();

        if ( isset( $errors[ $error ] ) ) {
			$errorMsg = $errors[ $error ];
		} else {
			$errorMsg = __( 'An error occurred. Please check your internet connection and try again. If the problem persists, contact our support.', STAwesome()->textDomain ) . ' (' . $error . ')';
		}

		return $errorMsg;
    }

    public function isLicenseActive( $licenseKey, $productId ) {
		$licenseData = json_decode( $this->getLicenseData( $licenseKey, $productId ) );

		return $licenseData['status'] === self::STATUS_VALID;
    }
    
    public function isLicenseAboutToExpire( $licenseKey, $productId ) {
		$licenseData = json_decode( $this->getLicenseData( $licenseKey, $productId ) );

		if ( ! empty( $licenseData['autorenew'] ) && true === $licenseData['autorenew'] ) {
			return false;
		}

		return time() > strtotime( '-28 days', strtotime( $licenseData['expirationDate'] ) );
    }

    public function setLicense( $componentName, $licenseKey ) {
        return update_option( $componentName . '_license_key', $licenseKey );
    }

    public function setPlan( $licenseKey ) {
        return update_option( $this->bundleComponentName . '_license_key', $licenseKey );
    }

    public function setLicenseData( $componentName, $license ) {
        if( $license->productId == STAwesome()->getComponentByInternalName( $componentName )::PRODUCT_ID && time() < strtotime( $license->expirationDate ) ) 
            $license->status = self::STATUS_VALID;
        else if( time() >= strtotime( $license->expirationDate ) )
            $license->status = self::STATUS_EXPIRED;
        else
            $license->status = self::STATUS_INVALID;

        // $license->status = self::STATUS_EXPIRED;

        return update_option( $componentName . '_license', json_encode( $license ) );
    }

    public function setPlanData( $license ) {

        if( $license->expired || time() >= strtotime( $license->expirationDate ) )
            $license->status = self::STATUS_EXPIRED;
        else
            $license->status = self::STATUS_VALID;

        // $license->status = self::STATUS_EXPIRED;

        return update_option( $this->bundleComponentName . '_license', json_encode( $license ) );
    }

    public function getLicense( $componentName = null ) {
        if( empty( $componentName ) )
            $componentName = $this->bundleComponentName;

        return trim( get_option( $componentName . '_license_key' ) );
    }

    public function isLicenseInstalled( $componentName = null ) {
        if( empty( $componentName ) )
            $componentName = $this->bundleComponentName;

        $license = $this->getLicense( $componentName );
        if( isset( $license ) && !empty( $license ) )
            return true;
        
        return false;
    }

    public function getLicenseData( $componentName = null ) {
        if( empty( $componentName ) )
            $componentName = $this->bundleComponentName;

        return get_option( $componentName . '_license' );
    }

    public function getLicensePlanData() {
        $data = get_option( $this->bundleComponentName . '_license' );
        if( !empty( $data ) )
            $data = json_decode( $data );

        return $data;
    }

    // Run this before uninstalling the plugin
    // delete all component licenses
    public function deactivateAll() {
        foreach( STAwesome()->getComponentsObject() as $component ) {
            if( defined( get_class( $component ) . "::internalName" ) ) {
                delete_option( $component::internalName . '_license_key' );
                delete_option( $component::internalName . '_license' );
            }
        }

        // delete bundle license
        delete_option( $this->bundleComponentName . '_license_key' );
        delete_option( $this->bundleComponentName . '_license' );
    }
}