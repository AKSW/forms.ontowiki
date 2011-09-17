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
    public static function generateUniqueUri ( $modelUri, $className, $label, $uriParts )
    {
        $time = time ();
        
        // if a / is at the end of the modelUri, remove it
        if ( '/' == substr ( $modelUri, strlen($modelUri) - 1 ) )
            $modelUri = substr ( $modelUri, 0, strlen($modelUri) - 1 );
        
        // replace placeholder
        $newUri = str_replace('%modeluri%', $modelUri, $uriParts);
        $newUri = str_replace('%hash%', substr ( md5 ($time . $className . rand() ), 0, 6 ), $newUri);
        $newUri = str_replace('%date%', date ( 'Ymd', $time ), $newUri);
        $newUri = str_replace('%labelparts%', $label, $newUri);
        $newUri = str_replace('%classname%', $className, $newUri);
                
        return $newUri;
    }
}
