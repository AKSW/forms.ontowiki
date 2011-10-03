<?php

/**
 * helper provides a simple global array to store information.
 *
 * Usage
 *  - config::set ( name, value ) > saves name/value pair
 *  - config::get ( name ) > returns saved value
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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
