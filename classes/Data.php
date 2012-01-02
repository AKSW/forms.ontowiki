<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright(c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License(GPL)
 */
class Data
{
    private $_predicateType;
    private $_selectedModel;
    private $_selectedModelUri;
    private $_store;
    private $_titleHelper;
    private $_uriParts;
    private $_form;
    
    public function __construct($predicateType, $selectedModel, $selectedModelUri, $store, $titleHelper, $uriParts, &$form)
    {
        $this->_predicateType = $predicateType;
        $this->_selectedModel = $selectedModel;
        $this->_selectedModelUri = $selectedModelUri;
        $this->_store = $store;
        $this->_titleHelper = $titleHelper;
        $this->_uriParts = $uriParts;
        $this->_form = $form;
    }
    
    
    /**
     * add / change a formula in / to datastore
     * @param
     * @return string json result
     */
    public function submitFormula($form, $formOld)
    {
        $json = array();
        
        // error
        if (null == $form) {
            $json ['status'] = 'error';
            $json ['message'] = 'form not set';
        } else {
            // JSON decode(string to array)
            $form = json_decode($form, true);
            $formOld = json_decode($formOld, true);
         
            // error
            if (null == $form) {
                $json ['status'] = 'error';
                
                // from
                // http://de.php.net/manual/en/function.json-last-error.php
                switch(json_last_error())
                {
                    case JSON_ERROR_NONE:
                        $json ['message'] = 'No errors';
                        break;
                    case JSON_ERROR_DEPTH:
                        $json ['message'] = 'Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $json ['message'] = 'Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $json ['message'] = 'Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $json ['message'] = 'Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        $json ['message'] = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        $json ['message'] = 'Unknown error';
                        break;                    
                }
            // $form is valid JSON
            } else {
                // build a formula instance
                $this->_form = $this->_form->initByArray($form);
                $formOld = $this->_form->initByArray($formOld);
                                
                if (false === $this->_form->isValid($this->_form)) {
                    
                } else {
                    // Add formula data to backend
                    if ('add' == $this->_form->getMode())
                        $json = $this->addFormulaData($this->_form);
                        
                    elseif ('edit' == $this->_form->getMode())
                        $json = $this->changeFormulaData($this->_form, $formOld);
                }
            }
        }
         
        return json_encode($json);
    }
    
    
    /**
     * adds a formula to backend
     * @param $f formula instance
     */
    public function addFormulaData(&$f, $upperResource = null, $relations = array())
    {
        $targetClass = $f->getTargetClass();
        
        $this->_titleHelper->addResource($targetClass);
                    
        // generate a new unique resource uri based on the target class
        $resource = Resource::generateUniqueUri($f, $this->_selectedModel, $this->_titleHelper, $this->_uriParts);
        
        // add resource - rdf:type - targetclass
        $this->addStmt(
            $resource,
            $this->_predicateType,
            $targetClass 
       );
        
        $f->setResource($resource);
        
        // add relations between a upper resource and a new resource 
        if (null != $upperResource && 0 < count($relations)) {
            foreach ($relations as $relation) {
                $this->addStmt(
                    $upperResource,
                    $relation,
                    $resource
               );
            }
        }
        
        
        foreach ($f->getSections() as $sectionEntries) {
            // extract title from array and delete it
            // so there only predicate and nestedconfig elements in it
            array_shift($sectionEntries);
            
            foreach ($sectionEntries as $entry) {
                // predicate
                if ('predicate' == $entry ['sectiontype']) {
                    $this->addStmt(
                        $resource,
                        $entry ['predicateuri'],
                        $entry ['value'] 
                   );
                // sub formula
                } elseif ('nestedconfig' == $entry ['sectiontype']) {
                    $this->addFormulaData(
                        $entry ['form'],
                        $resource,
                        $entry ['relations'] 
                   );
                }
            } 
        }
        
        $json = array();
        $json['status'] = 'ok';
        $json['form'] = $f->getDataAsArrays();
        
        return $json;
    }
    
    
    /**
     * Change formula data in backend
     */
    public function changeFormulaData($form, $formOld, $upperResource = '', $relations = array())
    {
        if ('' == $upperResource)
        {
            $json = array();
            $json['status'] = 'ok';            
            $json['formOld'] = $formOld->getDataAsArrays();
        }
        else
            $log = array ();
            
        // -------------------------------------------------------------
        $para = $form->getFormulaParameter();
        
        if (0 < count ( $para ))
        {
            $selectedResource = $form->getResource();
            $has = $para ['predicateToHealthState'];
            $healthState = $para ['healthState'];
            
            // Check if there is an relation between selectedResource
            // (e.g. a patient) and a healthState instance
            $result = $this->_selectedModel->sparqlQuery(
                'SELECT ?instance
                 WHERE {
                     <'. $selectedResource .'> <'. $has .'> ?instance .
                     ?instance <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <'. $healthState .'> .
                 };'
            );
            
            // no relation, no healthState instance
            if ( 0 == count ( $result ) ) 
            {
                /**
                 * Creates following relations:
                 * 
                 * - SelectedResource       has                         healthState-Instance
                 * - healthStateInstance    includesAffectedProperties  ALSFRSPropertySet-Instance
                 * - healthStateInstance    includesSymptoms            ALSFRSSymptomSet-Instance
                 */
                                
                // create a new healthState instance
                $healthStateInstance = $para['healthStateInstanceUri'] . substr ( md5 (rand(0,rand(500,2000))), 0, 8 );
                $this->addStmt( 
                    $healthStateInstance, 
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                    $para['healthState']
                );
                
                // Add a timestamp
                $this->addStmt( 
                    $healthStateInstance, 
                    'http://purl.org/dc/terms/created',
                    date ( 'Y-m-d H:i:s', time () )
                );
                
                // selectedResource  has  healthState instance
                $this->addStmt( $selectedResource, $has, $healthStateInstance );
                
                
                // create a new propertySet instance
                $propertySetInstance = $para['propertySetInstanceUri'] . substr ( md5 (rand(0,rand(500,2000))), 0, 8 );
                $this->addStmt( 
                    $propertySetInstance,
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                    $para['propertySet'] 
                );
                
                // HealthState-instance  includesAffactedProperties  PropertySet-instance
                $this->addStmt( $healthStateInstance, $para['predicateToPropertySet'], $propertySetInstance );
                
                
                // create a new propertySet instance
                $symptomSetInstance = $para['symptomSetInstanceUri'] . substr ( md5 (rand(0,rand(500,2000))), 0, 8 );
                $this->addStmt( 
                    $symptomSetInstance,
                    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                    $para['symptomSet'] 
                );
                
                // HealthState-instance  includesAffactedProperties  PropertySet-instance
                $this->addStmt( $healthStateInstance, $para['predicateToSymptomSet'], $symptomSetInstance );
            }
            
            // relation founded
            else { // 1 == $count
                
                /**
                 * Collect following relations:
                 * 
                 * - SelectedResource       has                         healthState-Instance
                 * - healthStateInstance    includesAffectedProperties  ALSFRSPropertySet-Instance
                 * - healthStateInstance    includesSymptoms            ALSFRSSymptomSet-Instance
                 */
                
                // healthState
                $healthStateInstance = $result [0]['instance'];
                
                // propertySet
                $result = $this->_selectedModel->sparqlQuery(
                    'SELECT ?instance
                     WHERE {
                         <'. $healthStateInstance .'> <'. $para['predicateToPropertySet'] .'> ?instance .
                         ?instance <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <'. $para['propertySet']  .'> .
                     };'
                );
                
                $propertySetInstance = $result [0]['instance'];
                
                // symptomSet
                $result = $this->_selectedModel->sparqlQuery(
                    'SELECT ?instance
                     WHERE {
                         <'. $healthStateInstance .'> <'. $para['predicateToSymptomSet'] .'> ?instance .
                         ?instance <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <'. $para['symptomSet']  .'> .
                     };'
                );
                
                $symptomSetInstance = $result [0]['instance'];
            }
        }
        
        // -------------------------------------------------------------
        foreach ($form->getSections() as $sectionEntries) 
        {
            // extract title from array and delete it
            // so there only predicate and nestedconfig elements in it
            array_shift($sectionEntries);
            
            foreach ($sectionEntries as $entry) {
                
                $oldValue = $formOld->getPredicateValue($entry ['index']);
                
                // $log [] = 'found alsfrs question with value: '. $entry ['value'];
                if(true == isset ( $entry ['type'] ) && 'alsfrsquestion' == $entry ['type'])
                {
                    if ( 'PropertySet' == $entry ['typeparameter']['pertainsTo'] )
                    {
                        // check that is a relation between propertySet instance
                        // and this option value
                        $result = $this->_selectedModel->sparqlQuery(
                            'SELECT ?score
                             WHERE {
                                 <'. $propertySetInstance .'> <'. $para['predicateToPropertyOption'] .'> ?score .
                                 <'. $propertySetInstance .'> <'. $para['predicateToPropertyOption'] .'> <'. $oldValue .'> .
                             };'
                        );
                        
                        if ( 0 == count ( $result ) ) {
                            $this->addStmt(
                                $propertySetInstance,
                                $para['predicateToPropertyOption'],
                                $entry ['value']
                            );
                        } else {
                            
                            // delete old value
                            $this->removeStmt(
                                $propertySetInstance,
                                $para['predicateToPropertyOption'],
                                $oldValue
                            );
                            
                            // add new one
                            $this->addStmt(
                                $propertySetInstance,
                                $para['predicateToPropertyOption'],
                                $entry ['value']
                            );
                        }
                    }
                    
                    // -------------------------------------------------
                    
                    elseif ( 'SymptomSet' == $entry ['typeparameter']['pertainsTo'] )
                    {
                        // check that is a relation between symptomSet instance
                        // and this option value
                        $result = $this->_selectedModel->sparqlQuery(
                            'SELECT ?score
                             WHERE {
                                 <'. $symptomSetInstance .'> <'. $para['predicateToSymptomOption'] .'> ?score .
                                 <'. $symptomSetInstance .'> <'. $para['predicateToSymptomOption'] .'> <'. $oldValue .'> .
                             };'
                        );
                        
                        if ( 0 == count ( $result ) ) {
                            $this->addStmt(
                                $symptomSetInstance,
                                $para['predicateToSymptomOption'],
                                $entry ['value']
                            );
                        } else {
                            
                            // delete old value
                            $this->removeStmt(
                                $symptomSetInstance,
                                $para['predicateToSymptomOption'],
                                $oldValue
                            );
                            
                            // add new one
                            $this->addStmt(
                                $symptomSetInstance,
                                $para['predicateToSymptomOption'],
                                $entry ['value']
                            );
                        }
                    }
                    
                    continue;
                }
                
                // predicate
                if ('predicate' == $entry ['sectiontype'] && false === is_object ($oldValue)) {
                    
                    if ($entry ['value'] != $oldValue) 
                    {
                        // if a sub formula resource not exists, create it on the fly
                        if ('' == $form->getResource())
                        {
                            // for example:
                            // in case of create a doctor (firstname and lastname) but use
                            // the main resource in a person formula, which has additionally 
                            // a birthday, gender and sub formula address 
                            
                            // generate a new unique resource uri based on the target class
                            $resource = Resource::generateUniqueUri(
                                $form, $this->_selectedModel, $this->_titleHelper, $this->_uriParts
                            );
                            
                            // on-the-fly add resource - rdf:type - targetclass
                            $this->addStmt(
                                $resource,
                                $this->_predicateType,
                                $form->getTargetClass () 
                            );
                            
                            $log [] = 'on-the-fly add ' . $resource . ' > '. $this->_predicateType  . ' > '. $form->getTargetClass () .' (index='. $entry ['index'] .')';
                            
                            // add relations between a upper resource and the new resource 
                            if ( '' != $upperResource && 0 < count($relations)) 
                            {
                                $log [] = 'on the fly relations: '. implode (',', $relations);
                                
                                foreach ($relations as $relation) {
                                    $this->addStmt(
                                        $upperResource,
                                        $relation,
                                        $resource
                                   );
                                   $log [] = 'on-the-fly add ' . $upperResource . ' > '. $relation  . ' > '. $resource .' (index='. $entry ['index'] .')';
                                }
                            }
                            else
                                $log [] = 'on the fly $upperResource='. $upperResource .', relations count='. count($relations);
                            
                            // save new generated resource
                            $form->setResource($resource);
                        }
                        else
                        {
                            $this->removeStmt($form->getResource(), $entry ['predicateuri'], $oldValue);
                            
                            if ('' == $upperResource)
                                $json['log'][] = 'remove ' . $form->getResource() . ' > '. $entry ['predicateuri']  . ' > '. $oldValue;
                            else
                                $log [] = 'remove ' . $form->getResource() . ' > '. $entry ['predicateuri']  . ' > '. $oldValue .' (index='. $entry ['index'] .')';
                        }
                        
                        $this->addStmt($form->getResource(), $entry ['predicateuri'], $entry ['value']);
                        
                        if ('' == $upperResource)
                            $json['log'][] = 'add ' . $form->getResource() .' > '. $entry ['predicateuri'] .' > '. $entry ['value'];
                        else
                            $log [] = 'add ' . $form->getResource() .' > '. $entry ['predicateuri'] .' > '. $entry ['value'].' (index='. $entry ['index'] .')';
                    }
                    else
                    {
                        if ('' == $upperResource)
                            $json['log'][] = 'nothing to do for '. $form->getResource() .' > '. $entry ['predicateuri'] .' > new:'. $entry ['value'] .' = old:'. $oldValue . ' (index='. $entry ['index'] .')';
                        else
                            $log [] = 'nothing to do for '. $form->getResource() .' > '. $entry ['predicateuri'] .' > new:'. $entry ['value'] .' = old:'. $oldValue . ' (index='. $entry ['index'] .')';
                    }
                } 
                
                // sub formula
                elseif ('nestedconfig' == $entry ['sectiontype'] && true === is_object ($oldValue)) 
                {
                    if ('' == $upperResource) 
                        $json ['log'] [] = $this->changeFormulaData ($entry['form'], $oldValue, $form->getResource(), $entry['relations']);
                    else
                        $log = array_merge ($log, $this->changeFormulaData ($entry ['form'], $oldValue, $form->getResource(), $entry['relations']));
                }
            }
        }
        
        if ('' == $upperResource)
        {
            $json['form'] = $form->getDataAsArrays();
            return $json;
        }
        else
            return $log;
    }
    
    
    /**
     * adds a triple to datastore
     */
    public function addStmt($s, $p, $o)
    {
        // set type(uri or literal)
        $type = true == Erfurt_Uri::check($o) 
            ? 'uri'
            : 'literal';
        
        // add a triple to datastore
        return $this->_store->addStatement(
            $this->_selectedModelUri, 
            $s,
            $p, 
            array('value' => $o, 'type' => $type)
       );
    }
    
    
    /**
     *
     */
    public function removeStmt($s, $p, $o)
    {
        // set type(uri or literal)
        $type = true == Erfurt_Uri::check($o) 
            ? 'uri'
            : 'literal';
            
        // aremove a triple form datastore
        return $this->_store->deleteMatchingStatements(
            $this->_selectedModelUri,
            $s,
            $p,
            array('value' => $o, 'type' => $type)
       );
    }
    
    
    /**
     * get all properties of a resource from the datastore
     * @param $resourceUri URI of the resource
     * @return Array assoziative array
     */
    public function getResourceProperties($resourceUri)
    {
        $properties = array();
        
        // fetch properties of a resource
        $results = $this->_selectedModel->sparqlQuery(
            'SELECT ?property ?value 
            WHERE {<' . $resourceUri . '> ?property ?value.}'
        );

        // build an assoziative array
        foreach ($results as $result)
        {
            // $properties[$result['property']] = $result['value'];
            $properties[] = array ( 
                'property' => $result['property'],
                'value' => $result['value'],
                'used' => false
            );
        }

        return $properties;
    }

    
    /**
     * loads data from a given resource
     * @param $resource 
     * @param $form Formula instance to be filled with fetched data
     */
    public function fetchFormulaData ($resource, &$form)
    {
        // fetch direct neighbours of the resource
        $properties = $this->getResourceProperties($resource);
        
        // echo '<pre>'; var_dump ( $properties ); echo '</pre>';
        
        if ( 0 == count($properties)) return;
        else
        {
            // save sections
            $sections = $form->getSections();
            
            // 
            foreach ($sections as $sectionEntries) 
            {
                foreach ($sectionEntries as $entry) 
                {
                    if (true === is_array($entry))
                    {                        
                        if ('predicate' == $entry ['sectiontype'])
                        {
                            $value = $this->getPropertyValue($properties, $entry['predicateuri']);
                            
                            if (false == isset ($entry['predicateuri']) || false == isset ($value))
                                continue;
                            else
                                $form->setPredicateValue ($entry['index'], $value);
                        }
                        elseif ('nestedconfig' == $entry ['sectiontype'])
                        {
                            $value = $this->getPropertyValue($properties,$entry ['relations'] [0]);
                            
                            if (false == isset ($value))
                                continue;
                            else
                                $this->fetchFormulaData ($value, $entry ['form']);
                        }                    
                    }
                }
            }
            
            // set forms resource
            $form->setResource ($resource);
        }
    }
    
    
    /**
     * 
     */
    public function getPropertyValue (&$properties, $property)
    {
        if (0 == count($properties)) {
            return null;
        } else {
            $i = 0;
            foreach ($properties as $p) {
                if ($p['property'] == $property && false == $p['used']){
                    
                    // this value can be only used one time
                    $properties [$i]['used'] = true;
                    
                    return $p['value'];
                }
                $i++;
            }
        }
            
        return null;
    }
    
    
    /**
     * get type (targetclass) of a resource
     * @param $resourceUri URI of the resource
     * @return string type of the resource
     */
    public function getResourceType($resourceUri)
    {
        $properties = $this->getResourceProperties ($resourceUri);
        $value = $this->getPropertyValue ($properties,$this->_predicateType);
        
        // if a type was set
        if (true == isset ($value)) {
            return $this->getResourceTitle ($value);
        }
        else
            return null;
    }
    
    
    /**
     * shortcut function for TitleHelper's addResource + getTitle
     * @param $resource 
     * @return string title
     */
    public function getResourceTitle ($resource)
    {
        $this->_titleHelper->addResource ($resource);
        return $this->_titleHelper->getTitle ($resource);
    }
    
    
    /**
     * 
     */
    public static function getResTitle ($model, $resource, $language)
    {
        $result = $model->sparqlQuery (
            'SELECT ?label
              WHERE {
                    <'. $resource .'> <http://www.w3.org/2000/01/rdf-schema#label> ?label .
                    FILTER (langmatches(lang(?label), "'. $language .'"))
              } 
              LIMIT 1;'
        );
        
        return true == isset ( $result [0] ['label'] ) ? $result [0] ['label'] : '';
    }    
}
