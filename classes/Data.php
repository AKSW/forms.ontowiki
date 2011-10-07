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
            // return $form;
            $form = json_decode ( $form, true );
         
            // error
            if ( null == $form )
            {
                $json ['status'] = 'error';
                $json ['message'] = 'form not valid json';
            }
            
            // $form is valid JSON
            else
            {
                // build a formula instance
                $form = Formula::initByJson ( $form );
                
                $json = json_encode ( $form->getDataAsArrays () );
            }
        }
        
        return $json;
    }
}
