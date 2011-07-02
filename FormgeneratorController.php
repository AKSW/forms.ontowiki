<?php

require 'classes/Form.php';		
require 'classes/Tools.php';

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
    /**
     * Default action. Forwards to get action.
     */
    public function __call($action, $params)
    {
        $this->_forward('get', 'files');
    }	 
     
    /**
     * 
     */
    public function formAction()
    {   
        // TODO Implement stuff for showing unfilled mandatory fields.
        
        // Build URL string for formula
        $this->view->actionUrl = (string)   
                                 new OntoWiki_Url ( 
                                    array('controller' => 'formgenerator',
                                          'action' => 'sendform') 
                                 );
                     
                     
        // If a template was selected.
        if ( true == isset ( $_REQUEST ['newFormConfig'] ) )
            $loadedFormConfig = $_REQUEST ['newFormConfig'];
        else
        {
            // If a class in left menu was selected.
            if ( -1 !== ( $selectedClass = OntoWiki_Model_Instances::getSelectedClass () ) )
                $loadedFormConfig = Tools::getClassRelevantConfigFile ( $selectedClass,
                                                                     $this->_owApp->selectedModel,
                                                                     $this->_privateConfig );
                
            // Default choice.
            else
                $loadedFormConfig = 'patient.xml';
        }
                      

        // Load XML Config
		$this->view->form = Tools::loadFormByXmlConfig ( $loadedFormConfig,
                                                         $this->_owApp->selectedModel );
                                       
                                       
        $this->view->loadedFormConfig = $loadedFormConfig;
    }

    /**
     * Check Form
     */
    public function checkformAction ()
    {
        $json = Array();
        $json['notset'] = Array();
             
        // Load XML config.
        $checkingForm = Tools::loadFormByXmlConfig ( $_REQUEST ['loadedFormConfig'],
                                                     $this->_owApp->selectedModel );
        
        
        // Make mapping between md5-fields and XML config.
        $fieldMappings = Tools::getFieldMappings ( $checkingForm );
        
                
        // Check field content (e.g. mandatory)
        foreach ( $fieldMappings as $entry )
        {
            if ( '1' == $entry ['mandatory'] 
                 AND 
                 ( '' == trim ( $_REQUEST [$entry ['md5']] ) OR null == $_REQUEST [$entry ['md5']] ) )
            {
                $json['notset'][] = $entry ['md5'];
            }
        }
        
        if (0 == count($json['notset']))
        {
            $encoded = $this->sendform ($checkingForm, $fieldMappings);

        }
        else
        {
            $encoded = json_encode($json);
        }
        
        echo $encoded;
    } 
    
    /**
     * Will be called after a form was sent.
     */
    protected function sendform (&$checkingForm, $fieldMappings)
    {              
        $json = Array();
        $json['triples'] = Array();
        $json['relations'] = Array();
        
        $modus = 'add';
        
        // TODO How to merge targetclasses and labelparts ?!
        $labelParts = Tools::getLabelParts ( $checkingForm );
        
        $targetClasses = Tools::getTargetClasses ( $checkingForm );
        
        
        // Creating resources from target classes.
        $resourceArray = array ();
        
        if (isset($_REQUEST['isedit']) && "" != $_REQUEST['isedit'] && 'true' == $_REQUEST['isedit'])
        {
            foreach ( $targetClasses as $targetClass ) 
            {
                $resourceArray [ $targetClass ] = $_REQUEST[$targetClass];
            }
            $modus = 'edit';
        }
        else
        {
            foreach ( $targetClasses as $targetClass ) 
            {
                $resourceArray [ $targetClass ] = Tools::generateUniqueUri ( 
                    (string) $this->_owApp->selectedModel, 
                    $targetClass, 
                    'foobar' // TODO Use labelparts!
                );
            }
            $json['resources'] = $resourceArray;
        }
        
        
        
        //echo "<br>Create following triples:";
        
        
        foreach ( $targetClasses as $class )
        {
            foreach ( $fieldMappings as $entry )
            {                
                // Only take predicates from current selected targetclass!
                if ( $class == $entry ['targetclass'] )
                {
                    if (isset($resourceArray [ $class ])     && '' != $resourceArray [ $class ]
                        && isset($entry ['predicateuri'])    && '' != $entry ['predicateuri']
                        && isset($_REQUEST [$entry ['md5']]) && '' != $_REQUEST [$entry ['md5']]
                    ) {
                        $triple = Array();
                        $triple['S'] = $resourceArray [ $class ] . '#debug';
                        $triple['P'] = $entry ['predicateuri'];
                        $triple['O'] = $_REQUEST [$entry ['md5']];
                        $triple['md5'] = $entry ['md5'];
                        $json['triples'][] = $triple;
                        
                        if(!$this->editTriple(   $resourceArray [ $class ] . '#debug',
                                                $entry ['predicateuri'],
                                                null,
                                                $_REQUEST [$entry ['md5']],
                                                'literal',
                                                $modus))
                        {
                            $json['error'] = Array();
                            $json['error']['message'] = 'add/edit triple';
                            $json['error']['subject'] = $triple['S'];
                            $json['error']['predicate'] = $triple['P'];
                            $json['error']['object'] = $triple['O'];
                        }
                    }
                }
            }
        }        
        
        
        // Get relations between main XML config and nestedconfig's
        $relationsArray = Tools::getNestedConfigRelations ( $checkingForm );
        
       // echo "<br><br><hr>";
        
       // echo "<br>Create following relations between resources:";
        
        foreach ( $relationsArray as $entry )
        {
            foreach ( $entry ['relations'] as $relation )
            {
                if (isset($resourceArray [ $targetClasses [0] ])
                    && "" != $resourceArray [ $targetClasses [0] ]
                    && isset($relation)
                    && "" != $relation
                    && isset($resourceArray [ $entry ['targetclass'] ])
                    && "" != $resourceArray [ $entry ['targetclass'] ]
                ) {
                    $relation_array = Array();
                    $relation_array['S'] = $resourceArray [ $targetClasses [0] ] . '#debug';
                    $relation_array['P'] = $relation;
                    $relation_array['O'] = $resourceArray [ $entry ['targetclass'] ];
                    $json['relations'][] = $relation_array;
                    
                    if (!$this->editTriple(   $relation_array['S'],
                                            $relation_array['P'],
                                            null,
                                            $relation_array['O'],
                                            'uri',
                                            $modus))
                    {
                        $json['error'] = Array();
                        $json['error']['message'] = 'add/edit relation';
                        $json['error']['subject'] = $relation_array['S'];
                        $json['error']['predicate'] = $relation_array['P'];
                        $json['error']['object'] = $relation_array['O'];
                    }
                }
            }
        }
        $encoded = json_encode($json);
        return $encoded;
    }
    
    /**
     * Interpret field type and build custom HTML code. The $name and $class 
     * parameter will be used to build a HTML wide unique name for every textfield.
     * @param $type Type of field.
     * @param $name Name of the predicate.
     * @param $class The class to which this field is belonged.
     */
    public static function getHtmlForType ( $type, $typeparameter, $name, $class )
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
                
                return '<input type="text" id="'. $fieldName .'" name="'. $fieldName .'" value="Test01">';
            
                break;
        }
    }
    
    /**
     * Chance a triple/relation in store, by delete the old one and add
     * the new one
     * @param oldSubjectUri old subject URI
     * @param oldPredicateUri old predicate URI
     * @param oldObject old object URI or literal
     * @param newObject new object URI or literal
     * @param type object type ('uri' or 'literal')
     * @return true if edit successful, else false
     */
    private function editTriple($subjectUri,
                                $predicateUri,
                                $oldObject,
                                $newObject,
                                $type,
                                $modus)
    {
        // get store
        $store = Erfurt_App::getInstance()->getStore();
        
        $options = Array();
        
        if ('uri' == $type)
        {
            $options['object_type'] = Erfurt_Store::TYPE_IRI;
        }
        else if ('literal' == $type)
        {
            $type == '';
            $options['object_type'] = Erfurt_Store::TYPE_LITERAL;
        }
        else
            return false;
    
        // only for sigilton predicates, deletes all triples with subject
        // and predicate and ignore the object
        // TODO: make it posible for mutliple predicates of the them type
        if ('edit' == $modus)
        {
            $deletedTriples = $store->deleteMatchingStatements (
                    (string) $this->_owApp->selectedModel,
                    $subjectUri,
                    $predicateUri,
                    null, //$oldObject,
                    $options
            );
            if (1 != $deletedTriples)
                return false;
        }
        if (null != $newObject)
        {
            $addedTriples = $store->addStatement (
                (string) $this->_owApp->selectedModel, 
                $subjectUri,
                $predicateUri, 
                array ( 'value' => $newObject, 'type' => $type)
            );
            if (1 != $addedTriples)
                return false;
        }
        
        return true;
    }
    
    /**
     * Debug Action to delete new added triples
     */
    public function deletenewtriplesAction()
    {
        $result = $this->_owApp->selectedModel->sparqlQuery(
            'SELECT * 
              WHERE {
                ?s ?p ?o.
                 FILTER regex( ?s, ".*#debug", "i")
              }'
        );
        
        //Tools::dumpIt($result);
        
        $store = Erfurt_App::getInstance()->getStore();
        $deletedTriples = 0;
        foreach ($result as $triples) {
            $deletedTriples += $store->deleteMatchingStatements (
                (string) $this->_owApp->selectedModel,
                $triples['s'],
                null,
                null,
                null
            );
        }
        echo $deletedTriples . ' triple(s) deleted';
    }
}

