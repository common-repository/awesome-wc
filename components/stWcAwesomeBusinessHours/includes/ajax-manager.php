<?php
/** 
 *  Update business hours setting - verify expected args and type cast before set
 *  @return \object
*/
if (!function_exists('st_wc_business_hours_update_option')) {
    function st_wc_business_hours_update_option() {
        if(!isset($_POST['option']) or !isset($_POST['value'])) {
            echo wp_send_json([
                'status'    => false,
                'message'   => 'Missing arg(s)',
            ]);
        } else {
            $option     = sanitize_text_field( $_POST['option'] );
            $value      = sanitize_text_field( $_POST['value'] );
            $value      = st_wc_business_hours_type_cast($option, $value);
            $success    = stWcAwesomeBusinessHours::getInstance()->settings->setValue($option, $value);
    
            echo wp_send_json([
                'status'    => $success,
                'message'   => $success ? 'Business Hours have been updated' : 'Some error ocurred',
            ]);
        }
    }
}

/** 
 *  Type cast setting value
 *  @param \option $option
 *  @param \string $value
 *  @return \mixed
*/
if (!function_exists('st_wc_business_hours_type_cast')) {
    function st_wc_business_hours_type_cast($option, $value) {
        switch ($option) {
            case 'enabled':
            case 'currentDayOnly':
            case 'highlightCurrent':
            case 'dense':
            case 'format24hour':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'sunday':
            case 'monday':
            case 'tuesday':
            case 'wednesday':
            case 'thursday':
            case 'friday':
            case 'saturday':
                return json_decode(stripslashes($value));
            default:
                return $value;
        }
    }
}