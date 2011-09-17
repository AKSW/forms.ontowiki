<?php

/**
 * Controller for Formgenerator.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
 
// extends include_path 
set_include_path(
    get_include_path() . PATH_SEPARATOR . 
    ONTOWIKI_ROOT . 'extension/formgenerator/classes/' . PATH_SEPARATOR
);
 
class FormgeneratorController extends OntoWiki_Controller_Component
{    
    /**
     * Default action. Forwards to get action.
     */
    public function __call($action, $params)
    {
        $this->_forward('get', 'files');
    }	 
     
    /**
     * 
     */
    public function formAction()
    {   
        
    }
}

