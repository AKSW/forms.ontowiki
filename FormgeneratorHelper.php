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
    public function __construct()
    {
        $owApp = OntoWiki::getInstance();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $c = $request->getControllerName();
        $a = $request->getActionName();
        $lastRoute = $owApp->session->lastRoute;
        $selectedResource = $owApp->session->selectedResource;
        $selectedClass = OntoWiki_Model_Instances::getSelectedClass ();

        // If a model has been selected
        if ($owApp->selectedModel != null 
            && ( 'formgenerator' == $c || 'resource' == $c ) )
        {
            // A class was selected
            if ( -1 !== $selectedClass
                && ( 'instances' ==  $a || 'newform' == $a ) )
            {
                // Add entry in tab list
                OntoWiki_Navigation::register (
                    'formgenerator_form', 
                    array(
                        'controller' => 'formgenerator', 
                        'action'     => 'newform', 
                        'name'       => 'NewInstance'
                    )
                );
            }
            // If an Resource was selected
            else if ( (string) $owApp->selectedModel !== $selectedResource
                        && ( 'properties' ==  $a  || 'form' == $a ) )
            {
                // Add entry in tab list
                OntoWiki_Navigation::register (
                    'formgenerator_form', 
                    array(
                        'controller' => 'formgenerator', 
                        'action'     => 'form', 
                        'name'       => 'EditResource'
                    )
                );
            }
        }
    }
}
