<?php

require_once 'config.php';
require_once 'classes/XmlConfig.php';
require_once 'classes/Formula.php';
require_once 'classes/Resource.php';

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
class FormgeneratorController extends OntoWiki_Controller_Component
{         
    /**
     * 
     */
    public function formAction()
    {   
        config::set ( 'selectedModel', $this->_owApp->selectedModel );
                
        // load xml configuration file
        $this->view->form = XmlConfig::loadFile ( 
            config::get ( 'dirXmlConfigurationFiles' ) .'patient.xml'
        );
        
        echo $this->view->form->toString ();
    }
}

