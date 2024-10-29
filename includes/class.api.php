<?php defined('ABSPATH') or exit;
class StWcAPI {

    // URL of the license API
    const API_URL               = 'https://upo0m28yu9.execute-api.us-east-1.amazonaws.com/';
    const DOWNLOAD_URL          = '';
    
    private static $instance    = null;

    /** 
	 * 	Return current instance of StWcAPI object
	 * 	@param \StWcAPI
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
    }

    /**
	 * @param string $licenseKey
     * @param string $productId
	 *
	 * @return \String|\WP_Error
	 */
    public function getLicenseStatus( $licenseKey, $productId ) { 

        if( !isset( $licenseKey ) || empty( $licenseKey ) )
            return new \WP_Error( 'no_license', __( 'Missing license key', STAwesome()->textDomain ) );
        if( !isset( $productId ) || empty( $productId ) )
            return new \WP_Error( 'no_productid', __( 'Missing product id', STAwesome()->textDomain ) );

        $body = [
            'licenseCode'   => $licenseKey,
            'productId'     => $productId,
            'url'           => home_url(),
        ];

        $response = $this->get( 'license/validate', $body );
        
        if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_body( $response );
		if ( empty( $status ) ) {
			return new \WP_Error( 'no_json', __( 'An error occurred, please try again', STAwesome()->textDomain ) );
        }
        
        return json_decode( $status );
    }

    /**
	 * @param String $licenseKey
	 * @return String|\WP_Error
	 */
    public function getPlanLicenseStatus( $licenseKey ) { 

        if( !isset( $licenseKey ) || empty( $licenseKey ) )
            return new \WP_Error( 'no_license', __( 'Missing license key', STAwesome()->textDomain ) );

        $body = [
            'licenseCode'   => $licenseKey,
            'url'           => home_url(),
        ];

        $response = $this->get( 'plan/validate', $body );
        
        if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_body( $response );
		if ( empty( $status ) ) {
			return new \WP_Error( 'no_json', __( 'An error occurred, please try again', STAwesome()->textDomain ) );
        }
        
        return json_decode( $status );
    }

    public function getLicenseData( $licenseKey, $productId ) {

        if( !isset( $licenseKey ) || empty( $licenseKey ) )
            return new \WP_Error( 'no_license', __( 'Missing license key', STAwesome()->textDomain ) );
        if( !isset( $productId ) || empty( $productId ) )
            return new \WP_Error( 'no_productid', __( 'Missing product id', STAwesome()->textDomain ) );

        $body = [
            'licenseCode'   => $licenseKey,
            'productId'     => $productId,
            'url'           => home_url(),
        ];

        $response = $this->get( 'license/data', $body );

        if ( is_wp_error( $response ) ) {
			return $response;
        }
        
        $data = wp_remote_retrieve_body( $response );
		if ( empty( $data ) ) {
			return new \WP_Error( 'no_json', __( 'An error occurred, please try again', STAwesome()->textDomain ) );
        }

        return $data;
    }

    public function getPlanData( $licenseKey ) {

        if( !isset( $licenseKey ) || empty( $licenseKey ) )
            return new \WP_Error( 'no_license', __( 'Missing license key', STAwesome()->textDomain ) );

        $body = [
            'licenseCode'   => $licenseKey,
            'url'           => home_url(),
        ];

        $response = $this->get( 'plan/data', $body );

        if ( is_wp_error( $response ) ) {
			return $response;
        }
        
        $data = wp_remote_retrieve_body( $response );
		if ( empty( $data ) ) {
			return new \WP_Error( 'no_json', __( 'An error occurred, please try again', STAwesome()->textDomain ) );
        }

        return $data;
    }
    
    public function getProducts() { 
        $response = $this->get( 'products' );
        if ( is_wp_error( $response ) ) {
			return $response;
        }
        
        $products = wp_remote_retrieve_body( $response );

        if( !isset( $products ) || empty( $products ) )
            return [];

        // check which components are installed
        $products = json_decode( $products );
        $components = STAwesome()->getComponentsObject();

        foreach( $products as $product ) {
            
            if( isset( $product->installed ) && $product->installed == true )
                continue;

            foreach( $components as $component ) {
                if( defined( get_class( $component ) . "::PRODUCT_ID" ) && $product->productId == $component::PRODUCT_ID ) {
                    // update license data
                    $license = null;
                    if( STAwesome()->license->isUsable( $component::internalName ) ) {
                        $license = json_decode( STAwesome()->license->getLicenseData( $component::internalName ) );
                        
                        if( isset( $license->licenseCode ) ) {
                            $data = $this->getLicenseData( $license->licenseCode, $component::PRODUCT_ID );

                            // save license data
                            if( isset( $data ) && !empty( $data ) ) {
                                $obj = json_decode( $data );
                                STAwesome()->license->setLicenseData( $component::internalName, $obj );
                                $license = $obj;
                            }

                            // $license = STAwesome()->license->getLicenseData( $component::internalName );
                        }
                    }

                    $product->installed         = true;
                    $product->installedVersion  = defined( get_class( $component ) . "::VERSION" ) ? $component::VERSION : "NOT SET";
                    $product->slug              = str_replace( '_', '-', $component::internalName );
                    $product->needRenovation    = !STAwesome()->license->isValid( $component::internalName );
                    $product->licenseInstalled  = STAwesome()->license->isUsable( $component::internalName );
                    $product->needUpdate        = $product->installed && $product->version != $product->installedVersion;
                    $product->updating          = false;
                    $product->license           = !empty( $license ) ? $license : null;
                }
            }
        }

        return $products;
    }

    // *** esta funcion no esta completa. Tengo que verificar si se va a usar o no
    public function getLatestVersion() {
        $response = $this->get( 'products/latest-version' );
        if ( is_wp_error( $response ) ) {
			return $response;
        }

        $product = wp_remote_retrieve_body( $response );

        if( !isset( $products ) || empty( $products ) )
            return [];

    }

    public function downloadUpdate( $productId ) {
        $component = STAwesome()->getComponentByProductId( $productId );

        if( !STAwesome()->license->isValid( $component::internalName ) )
            return new \WP_Error( 'invalid', __( 'Invalid', STAwesome()->textDomain ) );

        $license = STAwesome()->license->getLicenseData( $component::internalName );
        if( empty( $license ) ) {
            $license = STAwesome()->license->getLicensePlanData();
        }

        return $this->download( $productId, $license->licenseCode );
    }

    public function download( $productId, $licenseCode = '' ) {
        if( empty( $licenseCode ) ) {
            $component = STAwesome()->getComponentByProductId( $productId );
            $licenseCode = empty( $component ) ? '' : STAwesome()->license->getLicense( $component::internalName, $productId );
        }

        // Get package signed URL 
        $response = $this->get( 'products/latest-version', [ 'productId' => $productId, 'licenseCode' => $licenseCode, 'url' => home_url() ] );
        if ( is_wp_error( $response ) ) {
			return $response;
        }

        $package = wp_remote_retrieve_body( $response );
        $package = json_decode( $package );
		if ( empty( $package ) ) {
			return new \WP_Error( 'no_json', __( 'An error occurred, please try again', STAwesome()->textDomain ) );
        }
        else if( !$package->status ) {
            return new \WP_Error( 'fail', __( $package->data, STAwesome()->textDomain ) );
        }

        // Get latest version of the component
        $data = wp_remote_get( $package->data->src, [ 'timeout'   => 40 ]);
        if ( is_wp_error( $data ) ) {
			return $data;
        }
        
        $response_code = wp_remote_retrieve_response_code( $data );
		if ( 200 !== (int) $response_code ) {
			return new \WP_Error( $response_code, __( 'HTTP Error', STAwesome()->textDomain ) );
        }

        // Create zip file
        $zipName = $package->data->packageName;
        $file = fopen( $zipName, 'w+' );
        fputs( $file, $data['body'] );
        fclose( $file );

        // install the update
        $zip = new ZipArchive;
        $res = $zip->open( $zipName );

        if ($res === TRUE) {
            $zip->extractTo( STAwesome()->getComponentsDirBase() ); // . '/' . $package->data->directory
            $zip->close();
            
            unlink( $zipName );
        }
        else {
            return new \WP_Error( 'fail', __( 'Unzip fail', STAwesome()->textDomain ) );
        }

        return true;
    }

    public function get( $slug, $body = [] ) {

        $slug = self::API_URL . $slug;

        $response = wp_remote_get( $slug, [
            'timeout'   => 40,
            'headers'   => $this->getHeaders(),
			'body'      => $body,
        ]);

        if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $response_code ) {
			return new \WP_Error( $response_code, __( 'HTTP Error', STAwesome()->textDomain ) );
		}

		return $response;
    }

    public function post( $slug, $body = [] ) { 

        $slug = self::API_URL . $slug;

        $response = wp_remote_post( $slug, [
            'timeout'   => 40,
            'headers'   => $this->getHeaders(),
			'body'      => $body,
        ]);

        if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $response_code ) {
			return new \WP_Error( $response_code, __( 'HTTP Error', STAwesome()->textDomain ) );
		}

		return $response;
    }

    public function update() { }

    private function getHeaders() {
        return [];
    }
}