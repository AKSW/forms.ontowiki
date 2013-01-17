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
        $owNav = $owApp->getNavigation();
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
                && ( 'instances' ==  OntoWiki::getInstance()->lastRoute || 'newform' == $a ) )
            {
                $action = 'newform';
                if ('xmlfilenotfound' == $a)
                    $action = 'xmlfilenotfound';
                // Add entry in tab list
                $owNav->register (
                    'formgenerator_form', 
                    array(
                        'controller' => 'formgenerator', 
                        'action'     => $action, 
                        'name'       => 'NewInstance'
                    )
                );
            }
            // If an Resource was selected
            else if ( (string) $owApp->selectedModel !== $selectedResource
                        && ( 'properties' ==  OntoWiki::getInstance()->lastRoute  || 'form' == $a || 'report' == $a ) )
            {
                $action = 'form';
                if ('xmlfilenotfound' == $a)
                    $action = 'xmlfilenotfound';
                // Add entry in tab list
                $owNav->register (
                    'formgenerator_form', 
                    array(
                        'controller' => 'formgenerator', 
                        'action'     => $action, 
                        'name'       => 'EditResource'
                    )
                );
                if (false !== stristr($selectedClass, 'proposal')) {
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
    }
}
