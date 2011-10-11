<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Data 
{
    public function __construct ()
    {
        
    }
    
    
    /**
     * add / change a formula in / to datastore
     * @param
     * @return string json result
     */
    public static function submitFormula ( $form )
    {
        $json = array ();
        
        // error
        if ( null == $form )
        {
            $json ['status'] = 'error';
            $json ['message'] = 'form not set';
        }
        else
        {
            // JSON decode ( string to array)
            $form = json_decode ( $form, true );
         
            // error
            if ( null == $form )
            {
                $json ['status'] = 'error';
                
                // from
                // http://de.php.net/manual/en/function.json-last-error.php
                switch ( json_last_error() )
                {
                    case JSON_ERROR_NONE:
                        $json ['message'] = 'No errors';
                    break;
                    case JSON_ERROR_DEPTH:
                        $json ['message'] = 'Maximum stack depth exceeded';
                    break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $json ['message'] = 'Underflow or the modes mismatch';
                    break;
                    case JSON_ERROR_CTRL_CHAR:
                        $json ['message'] = 'Unexpected control character found';
                    break;
                    case JSON_ERROR_SYNTAX:
                        $json ['message'] = 'Syntax error, malformed JSON';
                    break;
                    case JSON_ERROR_UTF8:
                        $json ['message'] = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                    default:
                        $json ['message'] = 'Unknown error';
                    break;                    
                }
            }
            
            // $form is valid JSON
            else
            {
                $json ['status'] = 'ok';
                
                // build a formula instance
                $form = Formula::initByArray ( $form );
                
                if ( false === Formula::isValid ( $form ) )
                {
                    
                }
                else
                {
                    // Add formula data to backend
                    if ( 'add' == $form->getMode () )
                        Data::addFormulaData ( $form );
                        
                    elseif ( 'edit' == $form->getMode () )
                        Data::changeFormulaData ( $form );
                        
                    $json ['message'] = implode ( ' ', $form->getLabelpartValues () );
                }
                
                // $json = $form->getDataAsArrays ();
            }
        }
         
        return json_encode ( $json );
    }
    
    
    /**
     * Add a formula to backend
     */
    public static function addFormulaData ( $f )
    {
        $targetClass = $f->getTargetClass ();
        
        config::get ( 'titleHelper' )->addResource( $targetClass );
                    
        // generate a new unique resource uri based on the target class
        $resource = Data::generateUniqueUri ( $f );
        
        Data::addStmt ( 
            $resource,
            config::get ( 'predicate_type' ),
            $targetClass 
        );
        
        return $resource;
    }
    
    
    /**
     * Change formula data in backend
     */
    public static function changeFormulaData ( )
    {
        // TODO 
    }
    
    
    public static function addStmt ( $s, $p, $o )
    {
        // set type (uri or literal)
        $type = preg_match("/^(http(s?):\/\/|ftp:\/\/{1})((\w+\.){1,})\w{2,}$/i", $o )
                    ? 'uri' 
                    : 'literal';
        
        // add a triple to datastore
        return config::get('store')->addStatement (
            config::get ('selectedModelUri'), 
            $s, $p, 
            array ( 'value' => $o, 'type' => $type )
        );
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
     * 
     */
    public static function replaceNamespaces ( $s )
	{
        //TODO: no use of fix Uri                                   
		return str_replace ( 'architecture:', 'http://als.dispedia.info/architecture/c/20110504/', $s );
	}
}
