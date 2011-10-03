<?php

require_once 'helper.php';
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
     * init controller
     */     
    public function init()
    {
        parent::init();
        
        config::set ( 'url', $this->_componentUrlBase );
    }    
    
    /**
     * form action
     */
    public function formAction()
    {   
        $this->view->headLink()->appendStylesheet( config::get ('url') .'css/form.css' );
        $this->view->headLink()->appendStylesheet( config::get ('url') .'css/jshtmlplugins.css' );
        
        config::set ( 'selectedModel', $this->_owApp->selectedModel );
                
        // load xml configuration file
        $this->view->form = XmlConfig::loadFile ( 
            config::get ( 'dirXmlConfigurationFiles' ) .'patient.xml'
        );
        
        echo $this->view->form->toString ();
    }
}

