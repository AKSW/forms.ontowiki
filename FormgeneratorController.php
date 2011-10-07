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
        config::set ( 'selectedModel', $this->_owApp->selectedModel );
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
                
        // load xml configuration file
        $this->view->form = XmlConfig::loadFile ( 
            config::get ( 'dirXmlConfigurationFiles' ) .'patient.xml'
        );
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
        
        // 
        $json = array ();
        
        if ( false == isset ( $_REQUEST ['form'] ) )
        {
            $json ['status'] = 'error';
            $json ['message'] = 'form not set';
        }
        else
        {
            $form = json_decode ( $_REQUEST ['form'] );
            
            if ( null == $form )
            {
                $json ['status'] = 'error';
                $json ['message'] = 'form not valid json';
            }
            else
            {
                
            }
        }
        
        echo json_encode ( $json );
    }
}

