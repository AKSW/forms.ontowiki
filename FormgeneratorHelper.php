<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 */
class FormgeneratorHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        $owApp = OntoWiki::getInstance();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $c = $request->getControllerName();
        
        // If a model has been selected
        if ($owApp->selectedModel != null 
        
            AND ( 'resource' == $c OR 'formgenerator' == $c )
            
            // A class was selected
            AND -1 !== OntoWiki_Model_Instances::getSelectedClass () ) {
             
            
            // Add entry in tab list
            OntoWiki_Navigation::register (
                'formgenerator_form', 
                array(
                    'controller' => 'formgenerator', 
                    'action'     => 'form', 
                    'name'       => 'New Instance'
                )
            );
        }
    }
}
