<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class StWcAwesomeUser {

    const OPTION_PREFIX     = 'st_wc_awesome_user';

    // getters
    public static function getFirstName() {
        return get_option( self::OPTION_PREFIX . '_first_name' );
    }
    public static function getLastName() {
        return get_option( self::OPTION_PREFIX . '_last_name' );
    }
    public static function getName() {
        return self::getFirstName() . ' ' . self::getLastName();
    }
    public static function getEmail() { 
        return get_option( self::OPTION_PREFIX . '_email' );
    }
    public static function getToken() { 
        return get_option( self::OPTION_PREFIX . '_token' );
    }
    public static function getUser() {
        return [
            'firstName'     => self::getFirstName(),
            'lastName'      => self::getLastName(),
            'fullName'      => self::getName(),
            'email'         => self::getEmail()
        ];
    }

    // setters
    public static function setFirstName( $firstName ) {
        return update_option( self::OPTION_PREFIX . '_first_name', $firstName );
    }
    public static function setLastName( $lastName ) { 
        return update_option( self::OPTION_PREFIX . '_last_name', $lastName );
    }
    public static function setEmail( $email ) { 
        return update_option( self::OPTION_PREFIX . '_email', $email );
    }
    public static function setToken( $token ) { 
        return update_option( self::OPTION_PREFIX . '_token', $token );
    }

    public static function removeUser() {
        delete_option( self::OPTION_PREFIX . '_first_name' );
        delete_option( self::OPTION_PREFIX . '_last_name' );
        delete_option( self::OPTION_PREFIX . '_email' );
        delete_option( self::OPTION_PREFIX . '_token' );

        // remove all components licenses
        STAwesome()->license->deactivateAll();
    }

    public static function isLoggedIn() {
        if( empty( self::getToken() ) || empty( self::getEmail() ) )
            return false;

        return true;
    }
}