<?php

/**
 * Forms - Main Menu
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @copyright  Copyright (c) 2013
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SwitchformModule extends OntoWiki_Module
{
    protected $_owApp;
    protected $_ontologies;
    protected $_shouldShow = false;
    protected $_titleHelper;
    
    /**
     * 
     */
    public function init(){
        parent::init();
        
        $this->_owApp = OntoWiki::getInstance();
        
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
        
        $this->_titleHelper = new OntoWiki_Model_TitleHelper();
        
        // include javascript files
        $basePath = $this->_config->staticUrlBase . 'extensions/formgenerator/';
        $baseJavascriptPath = $basePath .'js/';
        
        $this->view->headScript()
            ->prependFile($baseJavascriptPath. 'switchform.js', 'text/javascript');
        
        $this->view->headLink()->appendStylesheet($basePath . 'css/switchform.css');
    }

    /**
     * Returns the title of the module
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Dispedia Forms';
    }

    /**
     * Maybe we should disable the app module in some case?
     *
     * @return string
     */
    public function shouldShow()
    {
        return true;
    }

    /**
     * Returns the menu of the module
     *
     * @return string
     */
    public function getMenu()
    {
        /*$menuRegistry = OntoWiki_Menu_Registry::getInstance();
        $menuRegistry->getMenu('formsmainmenu')->getSubMenu('View')->setEntry('Hide Knowledge Bases Box', '#');
        
        return OntoWiki_Menu_Registry::getInstance()->getMenu('application');*/
        
        // No Menu
        return new OntoWiki_Menu ();
    }
    
    /**
     * Returns the content for the model list.
     */
    public function getContents()
    {
        $request = new OntoWiki_Request();
        $this->view->currentForm = $request->getParam('file');
        
        $currentResource = $this->_owApp->__get('selectedResource');
        
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
        $resourceClassesResult = $this->_store->getTransitiveClosure($this->_ontologies['dispediaForms']['namespace'], 'http://www.w3.org/2000/01/rdf-schema#subClassOf', $resourceClasses, true);
        $resourceClassesResult = array_merge($resourceClassesResult, $this->_store->getTransitiveClosure($this->_ontologies['dispediaForms']['namespace'], 'http://www.w3.org/2000/01/rdf-schema#subClassOf', $resourceClasses, false));
        
        $statementStr = "";
        foreach ($resourceClassesResult as $resourceClass) {
            $this->_shouldShow = true;
            if ("" == $statementStr)
                $statementStr = '{?formUri <http://forms.dispedia.de/p/usefulForClass> <' . $resourceClass['node'] . '> .}';
            else
                $statementStr .= ' UNION {?formUri <http://forms.dispedia.de/p/usefulForClass> <' . $resourceClass['node'] . '> .}';
        }

        $alternativeForms = $this->_owApp->Erfurt->getStore()->sparqlQuery(
            'SELECT DISTINCT ?formUri ?fileName
            WHERE {
            ' . $statementStr . '
                ?formUri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://forms.dispedia.de/c/Form> .
                ?formUri <http://forms.dispedia.de/p/Filename> ?fileName .
            };
            '
        );
        
        $this->_titleHelper->reset();
        $this->_titleHelper->addResources($alternativeForms, 'formUri');
        
        foreach ($alternativeForms as $alternativeFormIndex => $alternativeForm)
        {
            $alternativeForms[$alternativeFormIndex]['label'] = $this->_titleHelper->getTitle($alternativeForm['formUri'], $this->_lang);
        }

        $this->view->alternativeForms = $alternativeForms;
        $this->view->themeUrlBase = $this->_owApp->getUrlBase()
                                    . $this->_owApp->config->themes->path
                                    . $this->_owApp->config->themes->default;
        
        return $this->render('templates/formgenerator/switchform_module');
    }
    
    public function allowCaching()
    {
        // no caching
        return false;
    }
}


