<?php

require_once 'helper.php';
require_once 'classes/Data.php';
require_once 'classes/Formula.php';
require_once 'classes/Resource.php';
require_once 'classes/XmlConfig.php';

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
    private $_titleHelper;
    private $_selectedModel;
    private $_uriParts;
    private $_store;
    private $_predicateType;
    private $_dirXmlConfigurationFiles;
    
    /**
     * init controller
     */     
    public function init()
    {
        parent::init();
        
        $this->_selectedModel = $this->_owApp->selectedModel;
        $this->_titleHelper = new OntoWiki_Model_TitleHelper ( $this->_selectedModel );
        $this->_uriPart = $this->_privateConfig->uriParts;
        $this->_store = Erfurt_App::getInstance()->getStore();
        $this->_predicateType = $this->_privateConfig->predicateType;
        $this->_dirXmlConfigurationFiles = dirname ( __FILE__ ) . '/' . $this->_privateConfig->dirXmlConfigurationFiles;
        
        config::set ( 'url', $this->_componentUrlBase );
        
        config::set ( 'selectedModel', $this->_owApp->selectedModel );
        config::set ( 'selectedModelUri', (string) config::get ( 'selectedModel' ) );
        config::set ( 'titleHelper', new OntoWiki_Model_TitleHelper ( config::get ( 'selectedModel' ) ) );
        config::set ( 'uriParts', $this->_privateConfig->uriParts );
        config::set ( 'store', Erfurt_App::getInstance()->getStore() );
        
        config::set ( 'predicate_type', $this->_privateConfig->predicateType );
    }    


    /**
     * form action
     */
    public function formAction()
    {   
        // include CSS files
        $this->view->headLink()->appendStylesheet( config::get ('url') .'css/form.css' );
        $this->view->headLink()->appendStylesheet( config::get ('url') .'css/jshtmlplugins.css' );
        
        // include Javascript files
        $this->view->headScript()->appendFile( config::get ('url') .'js/form.js');        
        $this->view->headScript()->appendFile( config::get ('url') .'libraries/jquery.json.min.js');
        
        // load xml configuration file
        $xmlconfig = new XmlConfig($this->_titleHelper, $this->_dirXmlConfigurationFiles);
        $this->view->form = $xmlconfig->loadFile('patient.xml');
    }
    
    
    /**
     * submit action
     */
    public function submitAction()
    {   
        // disable auto-rendering
        $this->_helper->viewRenderer->setNoRender();

        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();
        
        // processes a formula and output the result
        $form = true == isset ( $_REQUEST ['form'] ) ? $_REQUEST ['form'] : null;
        $formOld =  true == isset ( $_REQUEST ['formOld'] ) ? $_REQUEST ['formOld'] : null;
        echo Data::submitFormula ( $form, $formOld );
    }
}

