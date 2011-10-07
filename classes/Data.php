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
     * add / change a formula to datastore
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
            $form = json_decode ( $form );
         
            // error
            if ( null == $form )
            {
                $json ['status'] = 'error';
                $json ['message'] = 'form not valid json';
            }
            
            // $form is valid JSON
            else
            {
                $json = $form;
                
                // check mandatory fields
            }
        }
        
        return $json;
    }
}
