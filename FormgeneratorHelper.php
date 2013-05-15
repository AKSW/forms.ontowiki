<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class FormgeneratorHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        $owApp = OntoWiki::getInstance();
        $owNav = $owApp->getNavigation();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $c = $request->getControllerName();
        $a = $request->getActionName();
        $lastRoute = $owApp->lastRoute;
        $selectedResource = $owApp->selectedResource;
        $selectedClass = OntoWiki_Model_Instances::getSelectedClass ();

        // If a model has been selected
        if ($owApp->selectedModel != null)
        {
            // A class was selected
            if ( 'instances' == $a || 'newform' == $a )
            {
                $action = 'newform';
                // Add entry in tab list
                $owNav->register (
                    'formgenerator_newform', 
                    array(
                        'controller' => 'formgenerator', 
                        'action'     => $action, 
                        'name'       => 'NewInstance'
                    )
                );
            }
            // If an Resource was selected
            else if ( (string) $owApp->selectedModel !== $selectedResource
                        && ( 'properties' ==  $a || 'form' == $a || 'report' == $a )
                        && isset($selectedResource)
                        && "" != $selectedResource)
            {
                $action = 'form';
                // Add entry in tab list
                $owNav->register (
                    'formgenerator_form', 
                    array(
                        'controller' => 'formgenerator', 
                        'action'     => $action, 
                        'name'       => 'EditResource'
                    )
                );
                $currentResourceDescription = $selectedResource->getDescription();
                $selectedResourceClass = $currentResourceDescription[$selectedResource->getIri()]["http://www.w3.org/1999/02/22-rdf-syntax-ns#type"][0]['value'];
                if (false !== stristr($selectedResourceClass, 'proposal')) {
                    // Add entry in tab list
                    $owNav->register (
                       'formgenerator_report', 
                       array(
                           'controller' => 'formgenerator', 
                           'action'     => 'report', 
                           'name'       => 'reportResource'
                       )
                   );
                }
            }
        }
        parent::init();
    }
    
    public function onRouteStartup($event)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $a = $request->getActionName();
        
        if ("" == $request->getParam('r', '') && 'explore' != $a) {
            OntoWiki::getInstance()->lastRoute = 'instances';
        }
        
        $formFound = false;
        $owApp = OntoWiki::getInstance();
        $request = Zend_Controller_Front::getInstance()->getRequest();

        if ("" != $request->getParam('file')) {
            $currentForm = $request->getParam('file');
            if (isset($owApp->selectedModel)) {
                $enabledForms = $owApp->Erfurt-getStore()->sparqlQuery(
                    'SELECT ?formUri ?fileName
                    WHERE {
                        ?formUri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://forms.dispedia.de/c/Form> .
                        ?formUri <http://forms.dispedia.de/p/Filename> ?fileName
                    };'
                );

                foreach ($enabledForms as $enabledForm)
                {
                    if ($currentForm == $enabledForm['fileName']) {
                        $formFound = true;
                        break;
                    }
                }
                
                if (false == $formFound) {
                    $owApp->appendMessage(
                        new OntoWiki_Message(
                            $this->_owApp->translate->_('noformularfound'),
                            OntoWiki_Message::ERROR
                        )
                    );
                    
                    // get current route info
                    $front  = Zend_Controller_Front::getInstance();
                    $router = $front->getRouter();

                    // we must set a new route so that the navigation class knows,
                    $route = new Zend_Controller_Router_Route(
                        'formgenerator/form',         // hijack 'resource/properties' shortcut
                        array(
                            'controller' => 'formgenerator', // map to 'semanticsitemap' controller and
                            'action'     => 'xmlfilenotfound'     // 'sitemap' action
                        )
                    );
                    $route->setMatchedPath('formgenerator/form');
                    // add the new route
                    $router->addRoute('formgenerator', $route);
                }
            }
        }
    }
}
