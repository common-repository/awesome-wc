<?php defined('ABSPATH') or exit;

class stWcAwesomeBusinessHoursSettings {
    /**
     *  stWcAwesomeBusinessHours settings object
     *  @var \stWcAwesomeBusinessHoursSettings
     */

    private static $instance = null;

    /** settings */
    private $enabled            = true;
    private $sunday             = ['opens' => '08:00', 'closes' => '17:00', 'closed' => false];
    private $monday             = ['opens' => '08:00', 'closes' => '17:00', 'closed' => false];
    private $tuesday            = ['opens' => '08:00', 'closes' => '17:00', 'closed' => false];
    private $wednesday          = ['opens' => '08:00', 'closes' => '17:00', 'closed' => false];
    private $thursday           = ['opens' => '08:00', 'closes' => '17:00', 'closed' => false];
    private $friday             = ['opens' => '08:00', 'closes' => '17:00', 'closed' => false];
    private $saturday           = ['opens' => '08:00', 'closes' => '17:00', 'closed' => false];
    private $highlightCurrent   = false;
    private $highlightStyle     = 'light';
    private $highlightColor     = '#eeeeee';
    private $currentDayOnly     = false;
    private $dense              = false;
    private $format24hour       = false;

    /** time format patterns */
    private $pattern24hour      = '([01]?[0-9]|2[0-3]):[0-5][0-9]';
    private $pattern12hour      = '(1[0-2]|0?[1-9]):[0-5][0-9]\s?([ap]m)';

    /**
     *  Return current instance of stWcAwesomeBusinessHours settings object
     *  @return \stWcAwesomeBusinessHoursSettings
     */
    public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
        }
        
		return self::$instance;
    }

    /** init */
    private function __construct() {
        $this->loadConfiguration();
    }

    /**
     *  Init settings
     *  @return \void
     */
    function loadConfiguration() {
        $this->enabled          = get_option(stWcAwesomeBusinessHours::internalName . '_enabled', true);
        $this->sunday           = get_option(stWcAwesomeBusinessHours::internalName . '_sunday',    ['opens' => '08:00', 'closes' => '17:00', 'closed' => false]);
        $this->monday           = get_option(stWcAwesomeBusinessHours::internalName . '_monday',    ['opens' => '08:00', 'closes' => '17:00', 'closed' => false]);
        $this->tuesday          = get_option(stWcAwesomeBusinessHours::internalName . '_tuesday',   ['opens' => '08:00', 'closes' => '17:00', 'closed' => false]);
        $this->wednesday        = get_option(stWcAwesomeBusinessHours::internalName . '_wednesday', ['opens' => '08:00', 'closes' => '17:00', 'closed' => false]);
        $this->thursday         = get_option(stWcAwesomeBusinessHours::internalName . '_thursday',  ['opens' => '08:00', 'closes' => '17:00', 'closed' => false]);
        $this->friday           = get_option(stWcAwesomeBusinessHours::internalName . '_friday',    ['opens' => '08:00', 'closes' => '17:00', 'closed' => false]);
        $this->saturday         = get_option(stWcAwesomeBusinessHours::internalName . '_saturday',  ['opens' => '08:00', 'closes' => '17:00', 'closed' => false]);
        $this->highlightCurrent = get_option(stWcAwesomeBusinessHours::internalName . '_highlightCurrent', false);
        $this->highlightStyle   = get_option(stWcAwesomeBusinessHours::internalName . '_highlightStyle', 'light');
        $this->highlightColor   = get_option(stWcAwesomeBusinessHours::internalName . '_highlightColor', '#eeeeee');
        $this->currentDayOnly   = get_option(stWcAwesomeBusinessHours::internalName . '_currentDayOnly', false);
        $this->dense            = get_option(stWcAwesomeBusinessHours::internalName . '_dense', false);
        $this->format24hour     = get_option(stWcAwesomeBusinessHours::internalName . '_format24hour', false);
    }

    /**
     *  Return current settings for product list
     *  @return \object
     */
    function getSettings() {
        return (object) [
            'enabled'           => filter_var($this->enabled, FILTER_VALIDATE_BOOLEAN),
            'sunday'            => $this->sunday,
            'monday'            => $this->monday,
            'tuesday'           => $this->tuesday,
            'wednesday'         => $this->wednesday,
            'thursday'          => $this->thursday,
            'friday'            => $this->friday,
            'saturday'          => $this->saturday,
            'highlightCurrent'  => filter_var($this->highlightCurrent, FILTER_VALIDATE_BOOLEAN),
            'highlightColor'    => $this->highlightColor,
            'highlightStyle'    => $this->highlightStyle,
            'currentDayOnly'    => filter_var($this->currentDayOnly, FILTER_VALIDATE_BOOLEAN),
            'dense'             => filter_var($this->dense, FILTER_VALIDATE_BOOLEAN),
            'format24hour'      => filter_var($this->format24hour, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /** 
     *  Return settings form data
     *  @return \array - form data
    */
    function getFormData() {
        return [
            ['option' => 'sunday', 'type' => 'day'],
            ['option' => 'monday', 'type' => 'day'],
            ['option' => 'tuesday', 'type' => 'day'],
            ['option' => 'wednesday', 'type' => 'day'],
            ['option' => 'thursday', 'type' => 'day'],
            ['option' => 'friday', 'type' => 'day'],
            ['option' => 'saturday', 'type' => 'day'],
            ['option' => 'format24hour', 'title' => '24 Hour Format', 'type' => 'switch'],
            ['option' => 'dense', 'title' => 'Dense', 'type' => 'switch'],
            ['option' => 'currentDayOnly', 'title' => 'Current Day Only', 'type' => 'switch'],
            ['option' => 'highlightCurrent', 'title' => 'Highlight Current', 'type' => 'switch'],
            ['option' => 'highlightColor', 'type' => 'color-picker'],
            ['option' => 'highlightStyle', 'type' => 'select'],
        ];
    }

    /** 
     *  Return regular expression pattern for time formats
     *  @return \string - pattern
    */
    function regexp() {
        $separator     = '\s?-\s?';
        $regexp24hour  = '(^' . $this->pattern24hour . $separator . $this->pattern24hour . '$)';
        $regexp12hour  = '(^' . $this->pattern12hour . $separator . $this->pattern12hour . '$)';
        return '/' . $regexp24hour . '|' . $regexp12hour . '/mi';
    }
    /**
     *  Return setting for product list
     *  @param \string \$name - class member (setting)
     *  @return \mixed
     */
    function getValue($name) {
        switch ($name) {
            case 'enabled':
            case 'currentDayOnly':
            case 'highlightCurrent':
            case 'dense':
            case 'format24hour':
                return filter_var($this->$name, FILTER_VALIDATE_BOOLEAN);
            default:
                return $this->$name;
        }
    }

    /**
     *  Validate setting option
     *  @param \string $option  - option name in stWcAwesomeProductListSettings class
     *  @param \mixed value     - expected allowed option value
     *  @return \boolean        - setting validity
     */
    private function isValid($option, $value) {
        switch ($option) {
            case 'enabled':
            case 'currentDayOnly':
            case 'highlightCurrent':
            case 'dense':
            case 'format24hour':
                return in_array($value, [true, false], true);
            case 'sunday':
            case 'monday':
            case 'tuesday':
            case 'wednesday':
            case 'thursday':
            case 'friday':
            case 'saturday':
                $closed     = in_array($value->closed, [true, false], true);
                $hours      = preg_match($this->regexp(), $value->opens . '-' . $value->closes);
                return $closed and $hours;
            case 'highlightColor':
                return preg_match('/^#(?>[[:xdigit:]]{3}){1,2}$/', $value);
            case 'highlightStyle':
                return in_array($value, ['light', 'dark'], true);
            default:
                return false;
        }
    }    

    /**
     *  Validate and set value for setting
     *  @param \string $option - class member (setting)
     *  @param \mixed  $value - value for class member
     *  @return \bool
     */
    function setValue($option, $value) {
        if ($this->isValid($option, $value)) {
            update_option(stWcAwesomeBusinessHours::internalName . '_' . $option, STAwesome()->fn->boolToString($value));
            $this->$option = $value;
            return true;
        } else {
            return false;
        }
    }
}