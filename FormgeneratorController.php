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
    private $_form;
    private $_data;
    private $_defaultXmlConfigurationFile;
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
        $model = new Erfurt_Rdf_Model ($this->_privateConfig->defaultModel);
        
        $this->_defaultXmlConfigurationFile = $this->_privateConfig->defaultXmlConfigurationFile;
        $this->_dirXmlConfigurationFiles = dirname (__FILE__) . '/' . $this->_privateConfig->dirXmlConfigurationFiles;
        $this->_dirJsHtmlPlugins = dirname (__FILE__) . '/' . $this->_privateConfig->dirJsHtmlPlugins;
        $this->_predicateType = $this->_privateConfig->predicateType;
        $this->_selectedModel = $model;
        $this->_selectedModelUri = (string) $model;
        $this->_store = Erfurt_App::getInstance()->getStore();
        $this->_titleHelper = new OntoWiki_Model_TitleHelper ($this->_selectedModel);
        $this->_uriParts = $this->_privateConfig->uriParts;        
        $this->_url = $this->_componentUrlBase;
        
        $this->_owApp->selectedModel = $model;
        
        // main instance of a form
        $this->_form = new Formula(0, $this->_selectedModel);
        
        // instance of Data class for communicate with backend
        $this->_data = new Data (
            $this->_predicateType, $this->_selectedModel, $this->_selectedModelUri,
            $this->_store, $this->_titleHelper, $this->_uriParts,
            $this->_form
        );
    }    


    /**
     * form action
     */
    public function formAction()
    {
        // include CSS files
        $this->view->headLink()->appendStylesheet($this->_url .'css/form.css');
        $this->view->headLink()->appendStylesheet($this->_url .'css/jshtmlplugins.css');
        
        // include Javascript files
        $this->view->headScript()->appendFile($this->_url .'js/form.js');           
        $this->view->headScript()->appendFile($this->_url .'libraries/jquery.json.min.js');
        
        // set form relevant variables
        $this->view->dirJsHtmlPlugins = $this->_dirJsHtmlPlugins;
        $this->view->url = $this->_url;
        
        // set resource to load, if parameter r was set
        if ('' != $this->_request->getParam('r'))
        {
            if ('' != $this->_request->getParam('andFile'))
            {
                $file = $this->_request->getParam('andFile');
                $this->view->resourceSelected = true;
            }
            else
            {
                $file = $this->_data->getResourceType ($this->_request->getParam('r'));
            }
            
            if (null == $file)
                $file = $this->_defaultXmlConfigurationFile;
        }
        
        // set file to load, if parameter file was set
        elseif ('' != $this->_request->getParam('file'))
        {
            $file = $this->_request->getParam('file');
        }
            
        // set file based on selected class
        elseif ('' != OntoWiki_Model_Instances::getSelectedClass ())
        {
            $this->_titleHelper->addResource (OntoWiki_Model_Instances::getSelectedClass ());
            $file = strtolower($this->_titleHelper->getTitle (OntoWiki_Model_Instances::getSelectedClass ()));
        }
        
        // if a clear call of form action
        else
            $file = $this->_defaultXmlConfigurationFile;
        
        
        // load xml configuration file
        $xmlconfig = new XmlConfig(
            $this->_titleHelper,
            $this->_dirXmlConfigurationFiles,
            $this->_defaultXmlConfigurationFile . '.xml'
        );
        
        $this->_form = $xmlconfig->loadFile($file . '.xml', $this->_form);
        
        // if resource set ...
        if ('' != $this->_request->getParam('r'))
        {
            // ... load triples into formula instance
            $this->_data->fetchFormulaData($this->_request->getParam('r'));
            $this->_form->setMode ('edit');
        }
        
        $this->view->form = $this->_form;
        
        // loading resource of type
        if ('' != $this->_form->getSelectResourceOfType ())
        {
            // not dynamic! TODO find a solution to get a label for every resource!
            if (false !== strpos ($this->_form->getSelectResourceOfType (), 'Patient') || 
                false !== strpos ($this->_form->getSelectResourceOfType (), 'Person') )
            {
                $this->view->resourcesOfType = $this->_selectedModel->sparqlQuery(
                    'SELECT ?uri ?firstname ?lastname
                     WHERE {
                         ?uri <'. $this->_predicateType .'> <'. $this->_form->getSelectResourceOfType () .'>.
                         ?uri <'. $this->_form->replaceNamespaces ('architecture:') .'firstName> ?firstname.
                         ?uri <'. $this->_form->replaceNamespaces ('architecture:') .'lastName> ?lastname.
                     };'
                );
                
                // combines firstname and lastname to label
                function toSelectBox (&$item, $key){
                    $item['label'] = $item['firstname'] .' '. $item['lastname'];
                }
                
                array_walk ( $this->view->resourcesOfType, 'toSelectBox' );
            }
            
            if ( '' == $this->_request->getParam('r'))
                $this->view->showForm = false;
            else
                $this->view->showForm = true;
        }
        else
            $this->view->showForm = true;
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
        $form = true == isset ($_REQUEST ['form']) ? $_REQUEST ['form'] : null;
        $formOld =  true == isset ($_REQUEST ['formOld']) ? $_REQUEST ['formOld'] : null;
        
        echo $this->_data->submitFormula ($form, $formOld);
    }
}

