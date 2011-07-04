<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Plugin
{
    private $_model;
    
	public function __construct ()
	{
	}
	
    
	/**
     * Interpret field type and build custom HTML code. The $name and $class 
     * parameter will be used to build a HTML wide unique name for every textfield.
     * @param $type Type of field.
     * @param $name Name of the predicate.
     * @param $class The class to which this field is belonged.
     */
    public static function includePlugin ( $type, $typeparameter, $name, $class, $resourceValue )
    {
        $fieldName = md5 ( $class . $name );
        $path = dirname ( __FILE__ ) .'/../plugins';
        
        switch ( $type )
        {
            // List 
            case 'list': require ( $path . '/list.phtml' ); break;
            
            
            // Date - Birthdate 
            case 'birthdate': require ( $path . '/birthdate.phtml' ); break;
            
            
            // Default: xsd:string ( A simple textfield ) 
            default: require ( $path . '/textfield.phtml' ); break;
        }
    }
}
