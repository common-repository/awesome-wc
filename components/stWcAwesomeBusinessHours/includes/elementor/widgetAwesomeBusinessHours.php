<?php
class widgetAwesomeBusinessHours extends \Elementor\Widget_Base {

	/** data getters */ 
    public function get_name() 			{ return 'stWcAwesomeBusinessHours'; }
    public function get_title() 		{ return 'Awesome Business Hours'; }
    public function get_icon()	 		{ return 'awesome-icon-business-hours'; }
	public function get_categories()	{ return ['awesome']; }
	
    protected function _register_controls() { 
        $this->start_controls_section('general_section_business_hours', [
			'label' => 'General Settings',
			'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
		]);
        
		/** repeater */
		$repeater = new \Elementor\Repeater();

		$repeater->add_control('closed', [
			'label'         => 'Closed',
			'label_on'      => 'I',
			'label_off'     => 'O',
			'type'          => \Elementor\Controls_Manager::SWITCHER,
			'return_value'  => '1',
			'default'       => '',
		]);

		$repeater->add_control('hours', [
			'label'     => 'Hours',
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => '08:00AM - 05:00PM',
			'condition'	=> ['closed!' => '1'],
		]);

		$this->add_control('days-business-hours', [
			'label' 		=> 'Days',
			'type' 			=> \Elementor\Controls_Manager::REPEATER,
			'fields' 		=> $repeater->get_controls(),
			'title_field'	=> '{{{ day }}}',
			'default'		=> [
				['day' => 'Sunday'],
				['day' => 'Monday'],
				['day' => 'Tuesday'],
				['day' => 'Wednesday'],
				['day' => 'Thursday'],
				['day' => 'Friday'],
				['day' => 'Saturday'],
			],
		]);

        $this->add_control('format24hour', [
			'label'         => '24 Hour Format',
			'label_on'      => 'I',
			'label_off'     => 'O',
			'type'          => \Elementor\Controls_Manager::SWITCHER,
			'return_value'  => '1',
			'default'       => '',
		]);

        $this->add_control('dense', [
			'label'         => 'Dense',
			'label_on'      => 'I',
			'label_off'     => 'O',
			'type'          => \Elementor\Controls_Manager::SWITCHER,
			'return_value'  => '1',
			'default'       => '',
		]);

        $this->add_control('currentDayOnly', [
			'label'         => 'Current Day Only',
			'label_on'      => 'I',
			'label_off'     => 'O',
			'type'          => \Elementor\Controls_Manager::SWITCHER,
			'return_value'  => '1',
			'default'       => '',
		]);

        $this->add_control('highlightCurrent', [
			'label'         => 'Highlight Current',
			'label_on'      => 'I',
			'label_off'     => 'O',
			'type'          => \Elementor\Controls_Manager::SWITCHER,
			'return_value'  => '1',
			'default'       => '',
		]);

		$this->add_control('highlightStyle', [
			'label'     => 'Highlight Style',
			'type'      => \Elementor\Controls_Manager::SELECT,
			'options'   => ['light' => 'Light', 'dark' => 'Dark'],
			'default'   => 'light',
			'condition'	=> ['highlightCurrent' => '1'],
		]);

        $this->add_control('highlightColor', [
			'label'		=> 'Highlight Color',
			'type'		=> \Elementor\Controls_Manager::COLOR,
			'default'	=> '#ff0000',
			'condition'	=> ['highlightCurrent' => '1'],
		]);

		$this->end_controls_section();

        $this->start_controls_section('style_section', [
			'label' => 'Style',
			'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
			'name' 				=> 'typography',
			'label' 			=> 'Business Hours Typography',
			'scheme' 			=> \Elementor\Scheme_Typography::TYPOGRAPHY_1,
			'selector' 			=> '{{WRAPPER}} .awesome-business-hours',
			'fields_options' 	=> [
				'typography'	=> ['default' => 'yes'],
				'font_family'	=> ['default' => 'Roboto'],
				'font_size' 	=> ['default' => ['unit' => 'px', 'size' => 16]],
			],
		]);

		$this->end_controls_section();
    }

	/** generate component shortcode */
    private function generate_shortcode() {
		$settings		= $this->get_settings_for_display();
		$format24hour 	= $settings['format24hour'];
		$days 			= $this->get_days($settings['days-business-hours'], $format24hour);

		return '[st_wc_awesome_business_hours 
			sunday="' 				. $days['sunday'] .'"
			monday="' 				. $days['monday'] .'"
			tuesday="' 				. $days['tuesday'] .'"
			wednesday="' 			. $days['wednesday'] .'"
			thursday="' 			. $days['thursday'] .'"
			friday="' 				. $days['friday'] .'"
			saturday="' 			. $days['saturday'] .'"
			format24hour="'			. $format24hour .'"
			dense="' 				. $settings['dense'] .'"
			current-day-only="'		. $settings['currentDayOnly'] .'" 
			highlight-current="' 	. $settings['highlightCurrent'] .'"
			highlight-style="' 		. $settings['highlightStyle'] .'"
			highlight-color="' 		. $settings['highlightColor'] .'"]';
	}

	/** render component */
    protected function render() {
		if( !STAwesome()->fn->isElementorEditMode() ) {
			STAwesome()->loadAssets();
			stWcAwesomeBusinessHours::getInstance()->enqueueScripts();
			STAwesome()->loadVueForElementor();
			stWcAwesomeBusinessHours::getInstance()->enqueueVue( true );
		}
		
		echo do_shortcode(shortcode_unautop($this->generate_shortcode()));
	}

	/** render component */
	public function render_plain_content() {
		echo $this->generate_shortcode();
	}

	/** 
	 * 	extract/parse days data
	 * 	@param \array $raw_days 	- raw day data
	 * 	@param \bool  $format24hour	- 24 hour format flag
	 * 	@return \array 				- formatted days
	*/
	private function get_days($raw_days, $format24hour) {
		$days 	= []; // init
		$verify	= ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
		foreach ((array) $raw_days as $day) {
			$name			= strtolower($day['day']);
			$hours 			= $this->parseHours($day['hours'], $format24hour);
			$days[$name]	= trim($hours[0]) . ',' . trim($hours[1]) . ',' . $day['closed'];
			$index			= array_search($name, $verify, true);
			unset($verify[$index]);
		}

		/** set defaults for missing days */
		foreach ($verify as $day) { $days[$day] = '08:00,17:00,'; }
		return $days;
	}

	/**
	 * 	parse business hours
	 * 	@param \string \$hours - raw hours text
	 * 	@param \boolean \$format24hours - 24 hour format flag
	 * 	@return \array - opening and closing hours in array
	*/
	private function parseHours($hours, $format24hour) {
		$default 	= $format24hour ? ['08:00', '17:00'] : ['08:00AM', '05:00PM'];
		$hours 		= trim($hours);
		return preg_match($this->regexp(), $hours) ? explode('-', $hours) : $default;
	}

    /** 
     *  Return regular expression pattern for time formats
     *  @return \string - pattern
    */
    private function regexp() {
		$pattern24hour 	= '([01]?[0-9]|2[0-3]):[0-5][0-9]';
		$pattern12hour	= '(1[0-2]|0?[1-9]):[0-5][0-9]\s?([ap]m)';
        $separator     	= '\s?-\s?';
        $regexp24hour  	= '(^' . $pattern24hour . $separator . $pattern24hour . '$)';
        $regexp12hour  	= '(^' . $pattern12hour . $separator . $pattern12hour . '$)';
        return '/' . $regexp24hour . '|' . $regexp12hour . '/mi';
	}
}