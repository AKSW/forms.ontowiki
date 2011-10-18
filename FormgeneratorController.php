<?php

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
    private $_dirXmlConfigurationFiles;
    private $_dirJsHtmlPlugins;
    private $_predicateType;
    private $_selectedModel;
    private $_selectedModelUri;
    private $_store;
    private $_titleHelper;
    private $_uriParts;
    private $_url;
    
    /**
     * init controller
     */     
    public function init()
    {
        parent::init();
        
        // sets default model
        $model = new Erfurt_Rdf_Model ( $this->_privateConfig->defaultModel );
        
        $this->_dirXmlConfigurationFiles = dirname ( __FILE__ ) . '/' . $this->_privateConfig->dirXmlConfigurationFiles;
        $this->_dirJsHtmlPlugins = dirname ( __FILE__ ) . '/' . $this->_privateConfig->dirJsHtmlPlugins;
        $this->_predicateType = $this->_privateConfig->predicateType;
        $this->_selectedModel = $model;
        $this->_selectedModelUri = (string) $model;
        $this->_store = Erfurt_App::getInstance()->getStore();
        $this->_titleHelper = new OntoWiki_Model_TitleHelper ( $this->_selectedModel );
        $this->_uriParts = $this->_privateConfig->uriParts;        
        $this->_url = $this->_componentUrlBase;
        $this->_owApp->selectedModel = $model;
        
        // config::set ( 'url', $this->_componentUrlBase );
        
        // config::set ( 'selectedModel', $this->_owApp->selectedModel );
        // config::set ( 'selectedModelUri', (string) config::get ( 'selectedModel' ) );
        // config::set ( 'titleHelper', new OntoWiki_Model_TitleHelper ( config::get ( 'selectedModel' ) ) );
        // config::set ( 'uriParts', $this->_privateConfig->uriParts );
        // config::set ( 'store', Erfurt_App::getInstance()->getStore() );
        
        // config::set ( 'predicate_type', $this->_privateConfig->predicateType );
    }    


    /**
     * form action
     */
    public function formAction()
    {   
        // include CSS files
        $this->view->headLink()->appendStylesheet( $this->_url .'css/form.css' );
        $this->view->headLink()->appendStylesheet( $this->_url .'css/jshtmlplugins.css' );
        
        // include Javascript files
        $this->view->headScript()->appendFile( $this->_url .'js/form.js');           
        $this->view->headScript()->appendFile( $this->_url .'libraries/jquery.json.min.js');
        
        // set form relevant variables
        $this->view->dirJsHtmlPlugins = $this->_dirJsHtmlPlugins;
        $this->view->url = $this->_url;
        
        // set file to load or default filename
        $file = '' != $this->_request->getParam('file')
            ? $this->_request->getParam('file') 
            : 'person';
        
        // load xml configuration file
        $xmlconfig = new XmlConfig($this->_titleHelper, $this->_dirXmlConfigurationFiles);
        $this->view->form = $xmlconfig->loadFile($file .'.xml');
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
        
        // instance of Data class for communicate with backend
        $data = new Data ( 
            $this->_predicateType, $this->_selectedModel, $this->_selectedModelUri,
            $this->_store, $this->_titleHelper, $this->_uriParts 
        );
        
        echo $data->submitFormula ( $form, $formOld );
    }
}

