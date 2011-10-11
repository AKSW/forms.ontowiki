<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Resource 
{
    public function __construct ()
    {
        
    }
    
        
    /**
     * Generate a unique resource uri. 
     * @param $modelUri Model uri
     * @param $className Class from which the resource is an instance of
     * @param $label A label
     * @param $uriParts From default.ini
     * @return string
     */
    public static function generateUniqueUri ( $f )
    {
        // set essential parts
        $targetClass = $f->getTargetClass ();
        $modelUri    = (string) config::get ( 'selectedModel' );
        
        $className   = config::get ( 'titleHelper' )->getTitle ( $targetClass );
        $className   = true == Resource::isUri ( $className )
                       ? Resource::extractClassNameFromUri ( $className )
                       : $className;
                    
        $label       = implode ( '', $f->getLabelpartValues () );
        $uriParts    = config::get ( 'uriParts' );
        
        $time = time ();
        
        // if a / is at the end of the modelUri, remove it
        if ( '/' == substr ( $modelUri, strlen($modelUri) - 1 ) )
            $modelUri = substr ( $modelUri, 0, strlen($modelUri) - 1 );
        
        // replace placeholders in $uriParts
        $newUri = str_replace('%modeluri%', $modelUri, $uriParts);
        $newUri = str_replace('%hash%', substr ( md5 ($time . $className . rand() ), 0, 6 ), $newUri);
        $newUri = str_replace('%date%', date ( 'Ymd', $time ), $newUri);
        $newUri = str_replace('%labelparts%', $label, $newUri);
        $newUri = str_replace('%classname%', $className, $newUri);
                
        return $newUri;
    }
    

    /**
     * @param $o
     * @return string uri or literal
     */
    public static function determineObjectType ( $o )
    {
        return Resource::isUri ( $o )
            ? 'uri' 
            : 'literal';
    }
    
    
    /**
     * @param $o
     * @return string uri or literal
     */
    public static function isUri ( $o )
    {
        if ( 0 <= strpos ( $o, ':' ) )
            return true;
        else
            return preg_match('/^(http|ftp|https|architecture):\/\/|ftp:\/\/{1})((\w+\.){1,})\w{2,}$/i', $o )
                ? false
                : true;
    }
    
    
    /**
     * extracts class name from an uri
     * @param classUri Uri of class
     * @return classname as a string
     */
    public static function extractClassNameFromUri( $classUri )
    {
        if (strrpos ( $classUri, '/' ) < strrpos ( $classUri, '#' ) )
            $seperator = '#';
        elseif (strrpos ( $classUri, '/' ) > strrpos ( $classUri, '#' ) )
            $seperator = '/';
        else
            $seperator = ':';
             
        $classUri = substr($classUri, strrpos ( $classUri, $seperator ));
        
        return false === strpos ( $classUri, ':' )
            ? $classUri
            : substr ( $classUri, 1 );
    }
}
