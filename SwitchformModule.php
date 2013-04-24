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
    protected $_ontologies;
    protected $_shouldShow = false;
    protected $_titleHelper;
    
    /**
     * 
     */
    public function init(){
        parent::init();
        
        $this->_ontologies = $this->_config->ontologies->toArray();
        $this->_titleHelper = new OntoWiki_Model_TitleHelper();
        
        // include javascript files
        $basePath = $this->_config->staticUrlBase . 'extensions/formgenerator/';
        $baseJavascriptPath = $basePath .'js/';
        
        $this->view->headScript()
            ->prependFile($baseJavascriptPath. 'switchform.js', 'text/javascript');
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
        
        $enabledForms = $this->_owApp->Erfurt->getStore()->sparqlQuery(
            'SELECT ?formUri ?fileName
            WHERE {
                ?formUri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://forms.dispedia.de/c/Form> .
                ?formUri <http://forms.dispedia.de/p/Filename> ?fileName
            };
            '
        );
        $this->_titleHelper->reset();
        $this->_titleHelper->addResources($enabledForms, 'formUri');
        foreach ($enabledForms as $enabledFormIndex => $enabledForm)
        {
            $enabledForms[$enabledFormIndex]['label'] = $this->_titleHelper->getTitle($enabledForm['formUri'], $this->_lang);
        }
        
        $this->view->enabledForms = $enabledForms;
        
        return $this->render('templates/formgenerator/switchform_module');
    }
    
    public function allowCaching()
    {
        // no caching
        return false;
    }
}


