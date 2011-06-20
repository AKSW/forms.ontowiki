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
        
        // If a model has been selected
        if ($owApp->selectedModel != null) {
            
            // A class was selected
            if ( -1 !== OntoWiki_Model_Instances::getSelectedClass () )
            {
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
}
