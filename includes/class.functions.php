<?php defined('ABSPATH') or exit;
class StWcAwesomeFunctions {
    /** @var \stWcAwesomeFunctions */
	private static $instance = null;
	
	/** 
	 * 	Return current instance of stWcAwesomeFunctions object
	 * 	@param \stWcAwesomeFunctions
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** 
	 * 	Get product ids
	 * 	@return \array - product ids
	*/
	public function getProductIDs() {
		$args 			= ['post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1];
		$products 		= wc_get_products($args);
		$product_ids	= [];
		
		foreach ($products as $product) {
			$product_ids[] = $product->get_id();
		}
		
		return $product_ids;
	}
	
	/** 
	 * 	Get product from id
	 * 	@param  \int 	$id - product id
	 * 	@return \object	$data - product data
	 */
	public function getProduct($productId) {
		$product 	= wc_get_product($productId);
		if ($product && $product->exists()) {
			$data 	= [
				'id'                => $productId,
				'type'				=> $product->get_type(),
				'name'              => $product->get_name(),
				'slug'              => $product->get_slug(),
				'dateCreated'		=> $product->get_date_created(),
				'dateModified'		=> $product->get_date_modified(),
				'status'			=> $product->get_status(),
				'featured'			=> $product->get_featured(),
				'catalogVisibility'	=> $product->get_catalog_visibility(),
				'description'		=> $product->get_description(),
				'shortDescription'	=> $product->get_short_description(),
				'sku'				=> $product->get_sku(),
				'price'				=> $product->get_price(),
				'regularPrice'		=> $product->get_regular_price(),
				'salePrice'			=> $product->get_sale_price(),
				'onSaleFrom'		=> $product->get_date_on_sale_from(),
				'onSaleTo'			=> $product->get_date_on_sale_to(),
				'totalSales'		=> $product->get_total_sales(),
				'taxStatus'			=> $product->get_tax_status(),
				'taxClass'			=> $product->get_tax_class(),
				'manageStock'		=> $product->get_manage_stock(),
				'stockQty'			=> method_exists($product, 'get_stock_quantity') ? $product->get_stock_quantity() : 0, // fallback
				'stockStatus'		=> $product->get_stock_status(),
				'backorders'		=> $product->get_backorders(),
				'lowStockAmount'	=> method_exists($product, 'get_low_stock_amount') ? $product->get_low_stock_amount() : 0, // fallback
				'soldIndividually'	=> $product->get_sold_individually(),
				'weight'			=> $product->get_weight(),
				'length'			=> $product->get_length(),
				'width'				=> $product->get_width(),
				'height'			=> $product->get_height(),
				'dimensions'		=> [
					'length'	=> $product->get_length(),
					'width'		=> $product->get_width(),
					'height'	=> $product->get_height(),
				],
				'upsellIds'			=> $product->get_upsell_ids(),
				'crossSellIds'		=> $product->get_cross_sell_ids(),
				'parentId'			=> $product->get_parent_id(),
				'reviewsAllowed'	=> $product->get_reviews_allowed(),
				'purchaseNote'		=> $product->get_purchase_note(),
				'attributes'		=> $this->getProductAttributes($product->get_attributes()),
				'defaultAttributes'	=> $product->get_default_attributes(),
				'menuOrder'			=> $product->get_menu_order(),
				'postPassword'		=> method_exists($product, 'get_post_password') ? $product->get_post_password() : null, // fallback
				'categories'		=> $this->getProductCategories($productId),
				'tags'				=> $this->getProductTags($productId), 
				'virtual'			=> $product->get_virtual(),
				'galleryImages'		=> $this->getProductGallery($product->get_gallery_image_ids()), 
				'shippingClassId'	=> $product->get_shipping_class_id(), 
				'downloads'			=> $product->get_downloads(),
				'downloadExpiry'	=> $product->get_download_expiry(),
				'downloadable'		=> $product->get_downloadable(),
				'downloadLimit'		=> $product->get_download_limit(),
				'image'				=> [
					'id'		=> $product->get_image_id(),
					'thumbnail'	=> wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
					'medium'	=> wp_get_attachment_image_url($product->get_image_id(), 'medium'),
					'large'		=> wp_get_attachment_image_url($product->get_image_id(), 'large'),
					'full'		=> wp_get_attachment_image_url($product->get_image_id(), 'full')
				], 
				'ratingCounts'		=> $product->get_rating_counts(),
				'averageRating'		=> (float) $product->get_average_rating(),	// type cast
				'reviewCount'		=> $product->get_review_count(),
				'title'				=> $product->get_title(),
				'permalink'			=> $product->get_permalink(),
				'children'			=> $product->get_children(),
				'stockManagedById'	=> $product->get_stock_managed_by_id(),
				'priceHtml'			=> $product->get_price_html(),
				'formattedName'		=> $product->get_formatted_name(),
				'minPurchaseQty'	=> $product->get_min_purchase_quantity(),
				'maxPurchageQty'	=> $product->get_max_purchase_quantity(),
				'shippingClass'		=> $product->get_shipping_class(),
				'priceSuffix'		=> $product->get_price_suffix(),
				'availability'		=> $product->get_availability(),
				'isVisible'			=> $product->is_visible(),
				'isPurchasable'		=> $product->is_purchasable(),
				'isOnSale'			=> $product->is_on_sale(),
				'isInStock'			=> $product->is_in_stock(),
				'needShipping'		=> $product->needs_shipping(),
				'isTaxable'			=> $product->is_taxable(),
				'isShippingTaxable'	=> $product->is_shipping_taxable(),
				'isManagingStock'	=> $product->managing_stock(),
				'backordersAllowed'	=> $product->backorders_allowed(),
				'homeUrl'			=> home_url('/')
			];

			/** extract variable data */
			if ($product->is_type('variable')) {
				$data['minPrice']	= $product->get_variation_price('min');
				$data['maxPrice']	= $product->get_variation_price('max');
				$data['variations']	= $this->getProductVariations($product->get_available_variations());
			}
			
			return $data;
		}
	}

	/** 
	 * 	Get products from id array
	 * 	@param \array $product_ids - product ids
	 * 	@return \array  - product data array
	*/
	public function getProducts(array $product_ids) {
		$products = []; // init
		if (sizeof($product_ids)) {
			foreach ($product_ids as $product_id) {
				$products[] = $this->getProduct($product_id);
			}
		}

		return $products;
	}

	/** 
	 * 	Get data for available variations
	 * 	@param \array $availableVariations - variations array
	 * 	@return \array - variations data
	 */
	public function getProductVariations(array $availableVariations = []) {
		$variations = []; // init
		foreach($availableVariations as $variation) {
			$variations[] = [
				'attributes'			=> $variation['attributes'],
				'availabilityHtml'		=> $variation['availability_html'],
				'dimensions'			=> $variation['dimensions'],
				'image'					=> $variation['image'],
				'imageId'				=> $variation['image_id'],
				'price'					=> $variation['display_price'],
				'regularPrice'			=> $variation['display_regular_price'],
				'isDownloadable'		=> $variation['is_downloadable'],
				'isInStock'				=> $variation['is_in_stock'],
				'isPurchasable'			=> $variation['is_purchasable'],
				'isSoldIndividually'	=> $variation['is_sold_individually'],
				'isVirtual'				=> $variation['is_virtual'],
				'maxQty'				=> $variation['max_qty'],
				'minQty'				=> $variation['min_qty'],
				'sku'					=> $variation['sku'],
				'description'			=> $variation['variation_description'],
				'id'					=> $variation['variation_id'],
				'isActive'				=> $variation['variation_is_active'],
				'isVisible'				=> $variation['variation_is_visible'],
				'weight'				=> $variation['weight'],
				'weightHtml'			=> $variation['weight_html'],
				'selected'				=> false,
			];
		}

		return $variations;
	}

	/** 
	 * 	Get gallery images for product
	 * 	@param \array $galleryImageIds - gallery image ids
	 * 	@return \array - gallery images
	 */
	public function getProductGallery(array $galleryImageIds = []) {
		$gallery = [];	// init
		foreach($galleryImageIds as $imgId) {
			$gallery[] = [
				'id'		=> $imgId,
				'thumbnail'	=> wp_get_attachment_image_url($imgId, 'thumbnail'),
				'medium'	=> wp_get_attachment_image_url($imgId, 'medium'),
				'large'		=> wp_get_attachment_image_url($imgId, 'large'),
				'full'		=> wp_get_attachment_image_url($imgId, 'full'),
			];
		}

		return $gallery;
	}

	/** 
	 * 	Get product attribute data
	 * 	@param \array $productAttributes - product attributes
	 * 	@return \array - attributes data
	 */
	public function getProductAttributes(array $productAttributes = []) {
		$attributes = [];	// init
		foreach ($productAttributes as $attribute) {
			$terms = [];	// init
			foreach((array) $attribute->get_terms() as $term) {
				$term->selected = false;
				$terms[] = $term;
			}

			$attributes[] = [
				'taxonomy'			=> $attribute->get_taxonomy(),
				'taxonomyObject'	=> $attribute->get_taxonomy_object(),
				'terms'				=> $terms,
				'slugs'				=> $attribute->get_slugs(),
				'data'				=> $attribute->get_data(),
				'id'				=> $attribute->get_id(),
				'name'				=> $attribute->get_name(),
				'options'			=> $attribute->get_options(),
				'position'			=> $attribute->get_position(),
				'visible'			=> $attribute->get_visible(),
				'variation'			=> $attribute->get_variation()
			];
		}

		return $attributes;
	}

	/** 
	 * 	Get product tags
	 * 	@param \int $productId - product id
	 * 	@return \array - tag data
	 */
	public function getProductTags(int $productId) {
		$tags 	= []; // init
		$terms 	= get_the_terms($productId, 'product_tag');
		if (!empty($terms) && !is_wp_error($terms)){
			foreach ($terms as $term) {
				$tags[] = $term->name;
			}
		}

		return $tags;
	}

	/** 
	 * 	Get product categories
	 * 	@param \int $productId - product id
	 * 	@return \array - product categories
	 */
	public function getProductCategories(int $productId) {
		$categories	= []; // init
		$terms 		= get_the_terms($productId, 'product_cat');
		if (!empty($terms) && !is_wp_error($terms)){
			foreach ($terms as $term) {
				$categories[] = $term;
			}
		}

		return $categories;
	}

	/** 
	 *  Get user's purchased products
	 * 	@return \array - user's purchased products
	*/
	public function getPurchasedProducts() {
		$purchasedProducts  = []; // init
		$currentUser        = wp_get_current_user();

		/** get user's orders (completed & processing) */
		if ($currentUser->ID > 0) {
			$customerOrders = get_posts([
				'numberposts' => -1,
				'meta_key'    => '_customer_user',
				'meta_value'  => $currentUser->ID,
				'post_type'   => wc_get_order_types(),
				'post_status' => array_keys(wc_get_is_paid_statuses()),
			]);

			if (!empty($customerOrders)) {
				$product_ids = [];
				foreach($customerOrders as $customerOrder) {
					$order  = wc_get_order($customerOrder->ID);
					$items  = $order->get_items();
					foreach($items as $item) {
						$product_id     = $item->get_product_id();
						$product_ids[]	= $product_id;
					}
				}
				
				$product_ids = array_unique($product_ids);
				foreach($product_ids as $product_id) {
					$product                = $this->getProduct($product_id);
					$product['loading']     = false;
					$purchasedProducts[]    = $product;
				}
			}
		}

		return $purchasedProducts;
	}

	/** 
	 * 	Transform an object or string to a JSON format that can be used in Vue components
	 * 	@param \int $obj 	- object to be converted to JSON format
	 * 	@return \string 	- JSON string that can be used in Vue components
	 */
	public function toJSON( $obj ) {
		if( is_object( $obj ) || is_array( $obj ) )
			return stripslashes( htmlspecialchars( json_encode( $obj ), ENT_QUOTES, 'UTF-8' ) );
		else
			return stripslashes( htmlspecialchars( $obj, ENT_QUOTES, 'UTF-8' ) );
	}

	/** 
	 * 	Check if Elementor is installed and active
	 * 	@return \bool 	- true if Elementor is installed and active, false otherwise
	 */
	public function isElementorActive() {
		return did_action( 'elementor/loaded' );
	}

	/** 
	 * 	Check if Elementor is in edit mode
	 * 	@return \bool 	- true if Elementor is in edit mode, false otherwise
	 */
	public function isElementorEditMode() {
		if( $this->isElementorActive() ) {
			$plugin = \Elementor\Plugin::instance();
			return $plugin->editor->is_edit_mode();
		}

		return false;
	}

	/** 
	 * 	Check if a post is an Elementor page or post
	 * 	@return \bool 	- true if it is an Elementor page, false otherwise
	 */
	public function isElementorPage( $postId = null ) {
		if( $this->isElementorActive() ) {
			if( $postId === null )
				$postId = get_the_ID();
				
			return \Elementor\Plugin::$instance->db->is_built_with_elementor($postId);
		}

		return false;
	}

	/**
	 * login the user or customer to WP
	 * @return (WP_User|WP_Error) WP_User on success, WP_Error on failure.
	 */
	public function loginCustomer( $login, $password, $remember, $secureCookie = false ) {
		$creds = [
			'user_login'    => $login,
			'user_password' => $password,
			'remember'      => $remember
		];

		return wp_signon( $creds, $secureCookie );
	}

	/**
	 * Used to hide all wordpress notices in the plugin pages
	 */
	public function hideAdminNotices() {
		remove_all_actions( 'admin_notices' );
	}

	/**
	 * Convert bool values to string
	 * If the value provided is not a boolean, it will return the same value provided
	 */
	public function boolToString( $val ) {
		if( !is_bool( $val ) ) 
			return $val;

		return $val ? 'true' : 'false';
	}

	/**
	 * Generate a random string to use as an ID for the Vuetify v-app component. This is 
	 * used to prevent conflicts with the Vuetify components when multiple Awesome components
	 * are used in a single page
	 * @return string New ID for the Vuetify v-app component
	 */
	public function getNewAppID( $length = 8 ) {
		return substr( str_shuffle( MD5( microtime() ) ), 0, $length );
	}

	/**
	 * Write to log file whenever Wordpress and the plugin are in debug mode
	 * @param $message object or message to write in the log
	 */
	public function writeLog( $message ) {
		if (true === WP_DEBUG && true === StWcAwesome::DEBUG ) {
            if (is_array( $message ) || is_object( $message )) {
                error_log( print_r( $message, true ) );
            } else {
                error_log( $message );
            }
        }
	}
}