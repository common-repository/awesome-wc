<?php
if (!function_exists('st_wc_business_hours_shortcode')) {
	/** add dom element where vue will mount for the product list */
	function st_wc_business_hours_shortcode($atts = [], $content = null, $tag = '') {
		/** extract business hours settings */
		$data		= st_wc_extract_business_hours_shortcode_args($atts, $tag);
		$settings	= st_wc_parse_business_hours_data($data);

		if (!$settings->enabled) { return; }
		$settings = json_encode($settings);
		ob_start();
		?>
		<div class="st-awesome-app st-wc-business-hours-wrapper">
			<v-app id="awp-<?php echo STAwesome()->fn->getNewAppID(); ?>">
				<st-wc-business-hours :settings='<?php echo $settings ?>'></st-wc-business-hours>
			</v-app>
		</div>
		<?php

        return ob_get_clean();
	}
}

if (!function_exists('st_wc_parse_business_hours_data')) {
	/** 
	 * 	parse options (hyphen to camel case) and values
	 *	@param \array \$data	- business hours raw shortcode settings
	 *	@return \object 		- parsed business hours settings
	 */
	function st_wc_parse_business_hours_data($data) {
		$settings = []; // init
		foreach ($data as $option => $value) {
			$parsed	= str_replace('-', '', lcfirst(ucwords($option, '-')));
			if (is_string($value) and strpos($value, ',') !== false) {
				$day	= explode(',', $value);
				$value 	= [
					'opens'		=> $day[0] ?? '08:00',
					'closes' 	=> $day[1] ?? '17:00',
					'closed' 	=> $day[2] ?? false
				];
			}

			$settings[$parsed] = $value;
		}
		
		return (object) $settings;
	}
}

if (!function_exists('st_wc_extract_business_hours_shortcode_args')) {
	/** 
	 * 	extract and set default values for shortcode args
	 * 	@param \object $atts - values passed via shortcode
	 * 	@param \string $tag  - shortcode tag
	*/
	function st_wc_extract_business_hours_shortcode_args($atts, $tag) {
		$settings = stWcAwesomeBusinessHours::getInstance()->settings->getSettings(); // default settings
		return shortcode_atts([
			'enabled' 			=> $settings->enabled,
			'sunday' 			=> $settings->sunday,
			'monday' 			=> $settings->monday,
			'tuesday' 			=> $settings->tuesday,
			'wednesday' 		=> $settings->wednesday,
			'thursday' 			=> $settings->thursday,
			'friday' 			=> $settings->friday,
			'saturday' 			=> $settings->saturday,
			'format24hour' 		=> $settings->format24hour,
			'dense' 			=> $settings->dense,
			'current-day-only' 	=> $settings->currentDayOnly,
			'highlight-current'	=> $settings->highlightCurrent,
			'highlight-style' 	=> $settings->highlightStyle,
			'highlight-color'	=> $settings->highlightColor,
		], $atts, $tag);
	}
}