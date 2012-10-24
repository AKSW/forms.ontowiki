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
    public function generateUniqueUri ($f, $selectedModel, $titleHelper, $uriParts)
    {
        // set essential parts
        $targetClass = $f->getTargetClass ();
        
        $selectedModel = (string) $selectedModel;
        
        $className   = $titleHelper->getTitle ($targetClass);
        $className   = true == Erfurt_Uri::check($className)
                       ? Resource::extractClassNameFromUri ($className)
                       : $className;
                    
        $label       = quoted_printable_decode(implode ('', $f->getLabelpartValues ()));
        
        $time = time ();
        
        // if a / is at the end of the modelUri, remove it
        if ('/' == substr ($selectedModel, strlen($selectedModel) - 1))
            $selectedModel = substr ($selectedModel, 0, strlen($selectedModel) - 1);
        
        // replace placeholders in $uriParts
        $newUri = str_replace('%modeluri%', $selectedModel, $uriParts);
        $newUri = str_replace('%hash%', substr (md5 ($time . $className . rand()), 0, 4), $newUri);
        $newUri = str_replace('%date%', date ('Ymd', $time), $newUri);
        $newUri = str_replace('%labelparts%', $label, $newUri);
        $newUri = str_replace('%classname%', $className, $newUri);
        $newUri = str_replace(' ', '', $newUri);
                
        return $newUri;
    }
    
    
    /**
     * extracts class name from an uri
     * @param classUri Uri of class
     * @return classname as a string
     */
    public function extractClassNameFromUri($classUri)
    {
        if (strrpos ($classUri, '/') < strrpos ($classUri, '#'))
            $seperator = '#';
        elseif (strrpos ($classUri, '/') > strrpos ($classUri, '#'))
            $seperator = '/';
        else
            $seperator = ':';
             
        $classUri = substr($classUri, strrpos ($classUri, $seperator));
        
        return false === strpos ($classUri, $seperator)
            ? $classUri
            : substr ($classUri, 1);
    }
}
