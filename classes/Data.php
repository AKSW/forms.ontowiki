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
                }
                
                $json = $form->getDataAsArrays ();
            }
        }
         
        return json_encode ( $json );
    }
    
    
    /**
     * Add a formula to backend
     */
    public static function addFormulaData ( )
    {
        
    }
    
    
    /**
     * Change formula data in backend
     */
    public static function changeFormulaData ( )
    {
        // TODO 
    }
}
