<?php

/**
 * helper provides a simple global array to store information.
 *
 * Usage
 *  - config::set ( name, value ) 
 *  - config::get ( name ) 
 */
class config { 
    private static $c;
    public static function set ($n, $v) { self::$c [$n] = $v; } 
    public static function get ($n) { return self::$c [$n]; } 
}

// configuration 
config::set ( 'dir', dirname ( __FILE__ ) );
config::set ( 'dirXmlConfigurationFiles', config::get ('dir') . '/xmlconfigurationfiles/' );
config::set ( 'dirJsHtmlPlugins', config::get ('dir') . '/jshtmlplugins/' );
