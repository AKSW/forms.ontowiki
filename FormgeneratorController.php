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
        $this->view->headScript()->appendFile( config::get ('url') .'libraries/jquery.json.min.js');
                
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
        
        // processes a formula and output the result
        $form = true == isset ( $_REQUEST ['form'] ) ? $_REQUEST ['form'] : null;
        echo Data::submitFormula ( $form );
    }
    
    public function fooAction ()
    {
        // disable auto-rendering
        $this->_helper->viewRenderer->setNoRender();

        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();
        
        echo '<pre>';
        var_dump ( json_decode ( '{"title":"Patient","index":0,"labelparts":{"architecture:firstName":"architecture:firstName","architecture:lastName":"architecture:lastName"}}', true ) );
        echo '</pre>';
        
        /*{"title":"Patient","index":0,"description":"Lorem i","labelparts":{"architecture:firstName":"architecture:firstName","architecture:lastName":"architecture:lastName"},"mode":"add","resources":[],"targetclass":"architecture:Patient","xmlfile":"F:...patient.xml","sections":[{"index":"0,0","title":"first name","name":"fc3ce29e4c","value":"","mandatory":1,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/firstName","sectiontype":"predicate"},{"index":"0,1","title":"last name","name":"d192e0c4ad","value":"","mandatory":1,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/lastName","sectiontype":"predicate"},{"index":"0,2","title":"date of birth","name":"93e7709cfd","value":"","mandatory":0,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/birthdate","sectiontype":"predicate"},{"index":"0,3","title":"gender","name":"aa2cccd504","value":"","mandatory":0,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/gender","sectiontype":"predicate"},{"sectiontype":"nestedconfig","form":{"title":"Person","index":"0,4","description":"Lorem ipsum dolor sit amet, consetetur sadipscing elitr.","labelparts":{"architecture:firstName":"architecture:firstName","architecture:lastName":"architecture:lastName"},"mode":"add","resources":[],"targetclass":"architecture:Person","xmlfile":"F:\\k00ni\\Datending\\xampp\\htdocs\\dispedia\\extensions\\formgenerator\/xmlfileurationfiles\/person.xml","sections":[{"index":"0,4,0","title":"first name","name":"b655c19275","value":"","mandatory":1,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/firstName","sectiontype":"predicate"},{"index":"0,4,1","title":"last name","name":"ee4c7ea533","value":"","mandatory":1,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/lastName","sectiontype":"predicate"},{"index":"0,4,2","title":"date of birth","name":"24300bc40a","value":"","mandatory":0,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/birthdate","sectiontype":"predicate"},{"index":"0,4,3","title":"gender","name":"abdbc6fb08","value":"","mandatory":0,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/gender","sectiontype":"predicate"},{"sectiontype":"nestedconfig","form":{"title":"Address","index":"0,4,4","description":"Lorem ipsum dolor sit amet, consetetur sadipscing elitr.","labelparts":{"architecture:street":"architecture:street","architecture:city":"architecture:city"},"mode":"add","resources":[],"targetclass":"architecture:Address","xmlfile":"F:\\k00ni\\Datending\\xampp\\htdocs\\dispedia\\extensions\\formgenerator\/xmlfileurationfiles\/address.xml","sections":[{"index":"0,4,4,0","title":"street","name":"a4bf36ab66","value":"","mandatory":1,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/street","sectiontype":"predicate"},{"index":"0,4,4,1","title":"city","name":"71811b5999","value":"","mandatory":1,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/city","sectiontype":"predicate"}]}}]}},{"sectiontype":"nestedconfig","form":{"title":"Doctor","index":"0,5","description":"Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.","labelparts":{"architecture:firstName":"architecture:firstName","architecture:lastName":"architecture:lastName"},"mode":"add","resources":[],"targetclass":"architecture:Person","xmlfile":"F:\\k00ni\\Datending\\xampp\\htdocs\\dispedia\\extensions\\formgenerator\/xmlfileurationfiles\/doctor.xml","sections":[{"index":"0,5,0","title":"first name","name":"589f88cc23","value":"","mandatory":1,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/firstName","sectiontype":"predicate"},{"index":"0,5,1","title":"last name","name":"4e7e309ebf","value":"","mandatory":1,"predicateuri":"http:\/\/als.dispedia.info\/architecture\/c\/20110504\/lastName","sectiontype":"predicate"}]}}]}*/
    }
}

