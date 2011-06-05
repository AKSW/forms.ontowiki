<?php

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_files
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: FilesController.php 4090 2009-08-19 22:10:54Z christian.wuerker $
 */
class FormgeneratorController extends OntoWiki_Controller_Component
{
    protected $_configModel;
    
    /**
     * Default action. Forwards to get action.
     */
    public function __call($action, $params)
    {
        $this->_forward('get', 'files');
    }	    
    
    public function formAction()
    {
        require 'classes/Form.php';		
        require 'classes/Tools.php';

        
        // Get model
        $m = $this->_owApp->selectedModel;
        $mUrl = (string) $this->_owApp->selectedModel;


        // Load XML Config
		$exampleForm = new Form ( $m );
        $exampleForm->loadConfig ( realpath(dirname(__FILE__)) . '/formconfigs/patient.xml' );


        ## Output XML content ##
        

        // Content of "headline" tag
        echo '<h1>'. $exampleForm->headline .'</h1>';
        
        // Content of "introduceText" tag
        echo $exampleForm->introduceText;
                
        // Iterate about sections
        foreach ( $exampleForm->sections as $section )
        {
            echo '<br><br>';
            echo '<h3>'. $section ['caption'] .'</h3>';
            
            ## Iterate about predicates, only if predicate was set ##
            if ( true == isset ( $section ['predicate'] ) )
                foreach ( $section ['predicate'] as $predicate )
                {
                    echo '<br>'. $predicate ['caption'] . '('. $predicate ['type'] .'): ';

                    // Output HTML code which belongs to this type    
                    echo $this->getHtmlForType ( $predicate ['type'],
                                                 $predicate ['typeparameter'], 
                                                 $predicate ['predicateuri'],
                                                 $exampleForm->targetclass );
                }
                            
            
            ## Iterate about nestedconfig, only if nestedconfig was set ##
            if ( true == isset ( $section ['nestedconfig'] ) )
            {
                
                // Include formulas from nested configs
                foreach ( $section ['nestedconfig'] as $nestedconfig )
                {
                    foreach ( $nestedconfig ['form']->sections as $section )
                    {                        
                        // Iterate about predicates, only if predicate was set
                        if ( true == isset ( $section ['predicate'] ) )
                            foreach ( $section ['predicate'] as $predicate )
                            {
                                echo '<br>'. $predicate ['caption'] . '('. $predicate ['type'] .'): ';

                                // Output HTML code which belongs to this type    
                                echo $this->getHtmlForType ( $predicate ['type'], 
                                                             $predicate ['typeparameter'],  
                                                             $predicate ['predicateuri'],
                                                             $nestedconfig ['form']->targetclass );
                            }
                    }
                }
                
            }
        }
    }
    
    /**
     * Interpret field type and build custom HTML code. The $name and $class 
     * parameter will be used to build a HTML wide unique name for every textfield.
     * @param $type Type of field.
     * @param $name Name of the predicate.
     * @param $class The class to which this field is belonged.
     */
    private function getHtmlForType ( $type, $typeparameter, $name, $class )
    {
        $fieldName = md5 ( $class . $name );
        
        switch ( $type )
        {
            // List 
            case 'list':
            
                $s = '<select name="'. $fieldName .'">';
                
                foreach ( $typeparameter as $ele )
                    $s .= '<option>'. $ele .'</option>';
                
                $s .= '</select>';
            
                return $s;
            
            
            // Date - Birthdate 
            case 'birthdate':
            
                $currentYear = date ( 'Y', time ());
            
                // Build day
                $s = '<select name="'. $fieldName .'_day">';                
                for ( $i = 1; $i < 32; ++$i ) $s .= '<option>'. $i .'</option>';
                $s .= '</select> ';
                
                // Build month
                $s .= '<select name="'. $fieldName .'_month">';                
                for ( $i = 1; $i < 13; ++$i ) $s .= '<option>'. $i .'</option>';
                $s .= '</select> ';
                
                // Build year
                $s .= '<select name="'. $fieldName .'_year">';                
                for ( $i = 1920; $i < $currentYear; ++$i ) $s .= '<option>'. $i .'</option>';
                $s .= '</select>';
                                                
                return $s;
            
            
            // Default: xsd:string ( A simple textfield ) 
            default: 
                
                return '<input type="text" name="'. $fieldName .'">';
            
                break;
        }
    }
}

