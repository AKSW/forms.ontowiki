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
    protected $_form;
    protected $_data;
    protected $_dirXmlConfigurationFiles;
    protected $_dirJsHtmlPlugins;
    protected $_predicateType;
    protected $_dispediaModel;
    protected $_selectedModel;
    protected $_selectedModelUri;
    protected $_store;
    protected $_titleHelper;
    protected $_resourceHelper;
    protected $_uriParts;
    protected $_url;
    protected $_lang;
    protected $_configuration;
    
    // array for ontologie namespaces and instances
    protected $_ontologies;
    
    // array for output messages
    private $_messages;
    
    /**
     * init controller
     */     
    public function init()
    {
        
        parent::init();
        $this->_configuration = $this->_privateConfig->toArray();
        
        // get all models
        $this->_ontologies = $this->_config->ontologies->toArray();
        $this->_ontologies = $this->_ontologies['models'];
        $namespaces = array();
        // make model instances
        foreach ($this->_ontologies as $modelName => $model) {
            $this->_ontologies[$modelName]['instance'] = new Erfurt_Rdf_Model($model['namespace']);
            $namespaces[$model['namespace']] = $modelName;
        }
        $this->_ontologies['namespaces'] = $namespaces;

        
        $this->_selectedModel = $this->_ontologies['dispediaPatient']['instance'];
        $this->_dispediaModel = $this->_ontologies['dispediaCore']['namespace'];
        
        $this->_dirXmlConfigurationFiles = dirname (__FILE__) . '/' . $this->_configuration['uris']['dirXmlConfigurationFiles'];
        $this->_dirJsHtmlPlugins = dirname (__FILE__) . '/' . $this->_configuration['uris']['dirJsHtmlPlugins'];
        $this->_predicateType = $this->_configuration['uris']['predicateType'];
        $this->_store = Erfurt_App::getInstance()->getStore();
        $this->_lang = OntoWiki::getInstance()->config->languages->locale;
        $this->_resourceHelper = new Resource();

        $this->_titleHelper = new OntoWiki_Model_TitleHelper();
        
        $this->_uriParts = $this->_configuration['uris']['uriParts'];        
        $this->_url = $this->_componentUrlBase;
        
        //$this->_owApp->selectedModel = $model;
        
        // main instance of a form
        $this->_form = new Formula(0);
        
        // instance of Data class for communicate with backend
        $this->_data = new Data (
            $this->_predicateType,
            $this->_ontologies,
            $this->_store,
            $this->_titleHelper,
            $this->_uriParts,
            $this->_form,
            $this->_lang
        );
        
        $this->view->url = $this->_url;
        
    }
    
    /**
     * report action
     */
    public function reportAction()
    {
        $currentResource = $this->_owApp->selectedResource;
        $currentClasses = $this->getEligibleFormFiles($currentResource);
        $this->_request->setParam('file', key($currentClasses) . 'report');
        $this->formAction();
        $this->render('form');
    }

    /**
     * newform action
     */
    public function newformAction()
    {
        unset($this->_owApp->selectedResource);
        $this->formAction();
        $this->render('form');
    }
    
    /**
     * form action
     */
    public function formAction()
    {
        //show modules
        $this->addModuleContext('extension.formgenerator.form');
        
        // include CSS files
        $this->view->headLink()->appendStylesheet($this->_url .'css/form.css');
        $this->view->headLink()->appendStylesheet($this->_url .'css/jshtmlplugins.css');
        
        // include Javascript files
        $this->view->headScript()->appendFile($this->_url .'js/json-template.js');
        $this->view->headScript()->appendFile($this->_url .'js/edit.js');
        $this->view->headScript()->appendFile($this->_url .'js/form.js');
        
        // set form relevant variables
        $this->view->dirJsHtmlPlugins = $this->_dirJsHtmlPlugins;
        $this->view->predicateType = $this->_predicateType;
        $this->view->selectedModel = $this->_selectedModel;
        $this->view->dispediaModel = $this->_dispediaModel;
        $this->view->alsfrsModel = new Erfurt_Rdf_Model ($this->_configuration['uris']['alsfrsModel']);
        $this->view->store = $this->_store;
        
        $this->view->layout = $this->_request->getParam('layout');
        
        $file = null;
        
        $currentResource = '';

        // get selectedResource if it is set
        $selectedResource = $this->_owApp->__get("selectedResource");
        if (isset($selectedResource))
            $currentResource = $selectedResource->getIri();
        
        $this->view->selectedResource = $currentResource;

        // if parameter r was set, get the eligible classes of this resource
        if ('' != $currentResource)
        {
            $currentClasses = $this->getEligibleFormFiles($currentResource);
        }
        else
            $currentClasses = array();

        // set file to load, if parameter file was set
        if ('' != $this->_request->getParam('file'))
        {
            $file = $this->_request->getParam('file');
            $this->view->resourceSelected = true;
            
            // if file is not in eligible classes array then redirect to new plain form
            if ('' != $currentResource && !array_key_exists($file, $currentClasses))
            {
                $this->_redirect("formgenerator/newform?file=" . $file);
                return;
            }
        // set resource to load, if parameter r was set
        } elseif ('' != $currentResource)
        {
            if (false !== $currentClasses && 0 < count($currentClasses))
            {
                $file = key($currentClasses);
            }
        // set file based on selected class
        } elseif ('' != OntoWiki_Model_Instances::getSelectedClass ())
        {
            $file = strtolower($this->_resourceHelper->extractClassNameFromUri(OntoWiki_Model_Instances::getSelectedClass ()));
        }
        
        // If file was not set or not found
        if (null == $file || false == file_exists ($this->_dirXmlConfigurationFiles . $file .'.xml')) {
            if ("box" != $this->view->layout)
            {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message(
                        $this->_owApp->translate->_('noformularfound'),
                        OntoWiki_Message::ERROR
                    )
                );
                $this->_redirect($this->_config->urlBase . 'formgenerator/xmlfilenotfound/', array());
            }
            else
            {
                // disable auto-rendering
                $this->_helper->viewRenderer->setNoRender();
        
                // disable layout for Ajax requests
                $this->_helper->layout()->disableLayout();
                echo 'noformularfound';
                return;
            }
        }
        
        // load xml configuration file
        $xmlconfig = new XmlConfig(
            $this->_data,
            $this->_resourceHelper,
            $this->_titleHelper,
            $this->_dirXmlConfigurationFiles,
            $this->_lang
        );
        
        $this->view->selectedLanguage = $this->_lang;

        // read the formlist to the view
        $this->view->formList = $xmlconfig->getFormList();
        
        $this->_form = $xmlconfig->loadFile($file . '.xml', $this->_form);
        
        // loading resource of type
        if ('' != $this->_form->getSelectResourceOfType ())
        {
            //TODO: (not dynamic!) find a solution to get a label for every resource!
            if (false !== strpos ($this->_form->getSelectResourceOfType (), 'Patient') || 
                false !== strpos ($this->_form->getSelectResourceOfType (), 'Person') )
            {
                $this->view->resourcesOfType = $this->_selectedModel->sparqlQuery(
                    'SELECT ?uri
                     WHERE {
                         ?uri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <'. $this->_form->getSelectResourceOfType () .'>.
                     };'
                );
                
                $this->_titleHelper->reset();
                $this->_titleHelper->addResources($this->view->resourcesOfType, 'uri');
                
                foreach ($this->view->resourcesOfType as $resourceIndex => $resource)
                {
                    $this->view->resourcesOfType[$resourceIndex]['label'] = $this->_titleHelper->getTitle($resource['uri'], $this->_lang);
                }
                
            }
            
            if ( '' == $currentResource)
                $this->view->showForm = false;
            else
                $this->view->showForm = true;
        }
        else
            $this->view->showForm = true;
        
        // if resource set ...
        if ('' != $currentResource)
        {
            if (false == isset($this->view->resourcesOfType) && isset($dispediaSession->selectedPatientUri) && '' != $dispediaSession->selectedPatientUri && '' == $currentResource)
                $test = 0;
            else
            {
                // ... load triples into formula instance
                $this->_data->fetchFormulaData($currentResource,$this->_form);

                // delete the current file/class from the array, so only other eligible classes are in this array
                unset($currentClasses[$file]);
                
                // add ohter possible form buttons if the form is no box and no report
                if ("box" != $this->view->layout && "report" != $this->_form->getFormulaType())
                    // set other eligible classes as buttons for simple switching
                    foreach ($currentClasses as $className => $fileName) {
                        // build toolbar
                        $toolbar = $this->_owApp->toolbar;
                        $toolbar->appendButton(
                            OntoWiki_Toolbar :: EDITADD,
                            array('name' => ucfirst($className),
                                  'url' => $this->_config->urlBase . 'formgenerator/form/?file=' . $className)
                        );
                        $this->view->placeholder('main.window.toolbar')->set($toolbar);
                    }
            }
        }
        
        // load all needed context data
        $this->_data->loadContextData($this->_form);
        
        // show toolbar only if the form is no report
        if ("report" != $this->_form->getFormulaType())
        {
            //add buttons to toolbar
            $toolbar = $this->_owApp->toolbar;
            
            $toolbar->appendButton(OntoWiki_Toolbar :: SEPARATOR);
            
            $toolbar->appendButton(OntoWiki_Toolbar :: SAVE, array(
                'id'   => 'changeResource',
                'class'=> ('new' == $this->_form->getMode() ? ' hidden' : ''),
                'url'  => "javascript:submitFormula(urlMvc, " . ("box" == $this->view->layout ? 'boxdata' : 'data') . ", 'changed')"
            ));
            $toolbar->appendButton(OntoWiki_Toolbar :: ADD, array(
                'id'   => 'addResource',
                'class'=> (('' != $this->_form->getSelectResourceOfType() || 'edit' == $this->_form->getMode()) ? ' hidden' : ''),
                'url'  => "javascript:submitFormula(urlMvc, " . ("box" == $this->view->layout ? 'boxdata' : 'data') . ", 'add')"
            ));
            if ("box" != $this->view->layout)
            {
                $toolbar->appendButton(OntoWiki_Toolbar :: CANCEL, array(
                        'url'  => 'javascript:history.back();'
                    ));
                $this->view->placeholder('main.window.toolbar')->set($toolbar);
            }
            else
            {
                $toolbar->appendButton(OntoWiki_Toolbar :: CANCEL, array(
                        'url'  => 'javascript:closeBoxForm();'
                    ));
                $this->view->boxtoolbar = $toolbar->__toString();
            }
        }

        $this->view->urlBase = $this->_config->urlBase;
        $this->view->titleHelper = $this->_titleHelper;
        $this->view->form = $this->_form;
        $this->view->formulaParameter = $this->_form->getFormulaParameter ();
    }
    
    /**
     * Determined which formular files fit to a resource.
     * Check the classes of a resource and the superclasses of these classes and
     * compare this list with the existing form files.
     * The matched class names will be returned as a list.
     * @param $currentResource
     * @return array of classnames
     */
    private function getEligibleFormFiles($currentResource)
    {
        $resourceClassesResult = $this->_store->sparqlQuery (
            'SELECT ?class
            WHERE {
                <' . $currentResource . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ?class.
            };'
        );
        $resourceClasses = array();
        foreach ($resourceClassesResult as $resourceClass)
        {
            $resourceClasses[] = $resourceClass['class'];
        }
        $resourceClassesResult = $this->_store->getTransitiveClosure($this->_selectedModel->getModelUri(), 'http://www.w3.org/2000/01/rdf-schema#subClassOf', $resourceClasses, true);
        $resourceClassesResult = array_merge($resourceClassesResult, $this->_store->getTransitiveClosure($this->_selectedModel->getModelUri(), 'http://www.w3.org/2000/01/rdf-schema#subClassOf', $resourceClasses, false));

        $resourceClasses = array();
        $resourceHelper = new Resource();
        foreach ($resourceClassesResult as $resourceClassUri => $resourceClass) {
            $newRessourceClassName = strtolower($resourceHelper->extractClassNameFromUri($resourceClassUri));
            $resourceClasses[$newRessourceClassName] = $newRessourceClassName . '.xml';
        }
        $files = scandir($this->_dirXmlConfigurationFiles);
        
        $currentClasses = array_intersect($resourceClasses, $files);
        
        return $currentClasses;
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
        $form = $this->_request->getParam('form');
        
        $formOld = $this->_request->getParam('formOld');
        
        $response = $this->_data->submitFormula ($form, $formOld);
        
        if ('error' != $response['status'])
            $message = new OntoWiki_Message('formsaved', OntoWiki_Message::SUCCESS);
        else
            $message = new OntoWiki_Message('formnotsaved', OntoWiki_Message::ERROR);
        $this->_owApp->appendMessage($message);
        echo $response;
    }
    
    
    /**
     * 
     */
    public function xmlfilenotfoundAction()
    {
        
    }
}

