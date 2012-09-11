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
    private $_dirXmlConfigurationFiles;
    private $_dirJsHtmlPlugins;
    private $_predicateType;
    private $_dispediaModel;
    private $_selectedModel;
    private $_selectedModelUri;
    private $_store;
    private $_titleHelper;
    private $_resourceHelper;
    private $_uriParts;
    private $_url;
    private $_lang;
    
    /**
     * init controller
     */     
    public function init()
    {
        parent::init();
        
        // sets default model
        $model = new Erfurt_Rdf_Model ($this->_privateConfig->defaultModel);
        
        $this->_dirXmlConfigurationFiles = dirname (__FILE__) . '/' . $this->_privateConfig->dirXmlConfigurationFiles;
        $this->_dirJsHtmlPlugins = dirname (__FILE__) . '/' . $this->_privateConfig->dirJsHtmlPlugins;
        $this->_predicateType = $this->_privateConfig->predicateType;
        $this->_selectedModel = $model;
        $this->_selectedModelUri = (string) $model;
        $this->_store = Erfurt_App::getInstance()->getStore();
        $this->_lang = OntoWiki::getInstance()->config->languages->locale;
        $this->_resourceHelper = new Resource();

        $this->_dispediaModel = new Erfurt_Rdf_Model ($this->_privateConfig->dispediaModel);
        $this->_titleHelper = new OntoWiki_Model_TitleHelper();
        
        $this->_uriParts = $this->_privateConfig->uriParts;        
        $this->_url = $this->_componentUrlBase;
        
        //$this->_owApp->selectedModel = $model;
        
        // main instance of a form
        $this->_form = new Formula(0, $this->_selectedModel);
        
        // instance of Data class for communicate with backend
        $this->_data = new Data (
            $this->_predicateType,
            $this->_selectedModel,
            $this->_selectedModelUri,
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
        $this->view->alsfrsModel = new Erfurt_Rdf_Model ($this->_privateConfig->alsfrsModel);
        $this->view->store = $this->_store;
        
        $this->view->layout = $this->_request->getParam('layout');
        
        $file = null;
        
        $currentResource = '';

        // get selectedResource if it is set
        $selectedResource = $this->_owApp->__get("selectedResource");
        if (isset($selectedResource))
            $currentResource = $selectedResource->getIri();;
        
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
            $this->_titleHelper,
            $this->_dispediaModel,
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
            // not dynamic! TODO find a solution to get a label for every resource!
            if (false !== strpos ($this->_form->getSelectResourceOfType (), 'Patient') || 
                false !== strpos ($this->_form->getSelectResourceOfType (), 'Person') )
            {
                $this->view->resourcesOfType = $this->_selectedModel->sparqlQuery(
                    'SELECT ?uri ?firstname ?lastname
                     WHERE {
                         ?uri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <'. $this->_form->getSelectResourceOfType () .'>.
                         ?uri <http://www.dispedia.de/o/firstName> ?firstname.
                         ?uri <http://www.dispedia.de/o/lastName> ?lastname.
                     };'
                );
                
                // combines firstname and lastname to label
                if ( false == function_exists ('toSelectBox') ) {
                    function toSelectBox (&$item, $key){
                        $item['label'] = $item['firstname'] .' '. $item['lastname'];
                    }
                }
                
                array_walk ( $this->view->resourcesOfType, 'toSelectBox' );
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
            if (false == isset($this->view->resourcesOfType) && isset($dispediaSession->selectedPatientUri) && '' != $dispediaSession->selectedPatientUri && '' == $this->_request->getParam('r'))
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
                'url'  => "javascript:submitFormula(urlMvc, " . ("box" == $this->view->layout ? 'boxdata' : 'data') . ", 'edit')"
            ));
            $toolbar->appendButton(OntoWiki_Toolbar :: ADD, array(
                'id'   => 'addResource',
                'class'=> ('' != $this->_form->getSelectResourceOfType() ? ' hidden' : ''),
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
                {
                    {
                        {<' . $currentResource . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ?class.}
                        UNION{
                            <' . $currentResource . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> _:a.
                            _:a <http://www.w3.org/2000/01/rdf-schema#subClassOf> ?class.
                        }
                    }
                    UNION{
                        <' . $currentResource . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> _:b.
                        _:b <http://www.w3.org/2000/01/rdf-schema#subClassOf> _:c.
                        _:c <http://www.w3.org/2000/01/rdf-schema#subClassOf> ?class.
                    }
                }
                UNION{
                    <' . $currentResource . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> _:d.
                    _:d <http://www.w3.org/2000/01/rdf-schema#subClassOf> _:e.
                    _:e <http://www.w3.org/2000/01/rdf-schema#subClassOf> _:f.
                    _:f <http://www.w3.org/2000/01/rdf-schema#subClassOf> ?class.
                }
            };'
        );
        $resourceClasses = array();
        $resourceHelper = new Resource();
        foreach ($resourceClassesResult as $resourceClass) {
            $newRessourceClassName = strtolower($resourceHelper->extractClassNameFromUri($resourceClass['class']));
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

