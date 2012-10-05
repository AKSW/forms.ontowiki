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
    private $_lang;
    private $_resourceHelper;
    
    public function __construct($predicateType, $selectedModel, $selectedModelUri, $store, $titleHelper, $uriParts, &$form, $lang)
    {
        $this->_predicateType = $predicateType;
        $this->_selectedModel = $selectedModel;
        $this->_selectedModelUri = $selectedModelUri;
        $this->_store = $store;
        $this->_titleHelper = $titleHelper;
        $this->_uriParts = $uriParts;
        $this->_form = $form;
        $this->_lang = $lang;
        $this->_resourceHelper = new Resource();
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
        
        $model = $this->_selectedModel;
        
        if ("" != $f->getTargetModel())
        {
            $model = $f->getTargetModel();
            $this->_selectedModel = new Erfurt_Rdf_Model ($model);
            $this->_selectedModelUri = $model;

        }
        
        if ("" != $f->getModelNamespace())
            $model .= $f->getModelNamespace() . '/';
        
        // generate a new unique resource uri based on the target class
        $resource = Resource::generateUniqueUri($f, $model, $this->_titleHelper, $this->_uriParts);
        
        // add resource - rdf:type - targetclass
        $this->addStmt(
            $resource,
            $this->_predicateType,
            $targetClass 
        );
        
        // generate resource label
        $resourceLabel = implode (' ', $f->getLabelpartValues ());
        
        // add resource - rdfs:label - resourceLabel
        $this->_store->addStatement(
            $this->_selectedModelUri, 
            $resource,
            "http://www.w3.org/2000/01/rdf-schema#label", 
            array('value' => $resourceLabel, 'type' => 'literal', 'lang' => $this->_lang)
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
                // predicate, only if is it not the label predicate, this is writen above
                if ('predicate' == $entry ['sectiontype'] && "http://www.w3.org/2000/01/rdf-schema#label" != $entry ['predicateuri']) {
                    if (is_array($entry ['value']))
                    {
                        foreach ($entry ['value'] as $valueNumber => $value)
                        {
                            if (isset($entry ['typeparameter'][0]['order']) && 1 == $entry ['typeparameter'][0]['order'] && 0 < $valueNumber)
                                $this->addStmt(
                                    $value,
                                    $entry ['typeparameter'][0]['successor'],
                                    $entry ['value'][$valueNumber - 1]
                                );
                            $this->addStmt(
                                $resource,
                                $entry ['predicateuri'],
                                $value
                            );
                        }
                    }
                    else
                        if ("" != $entry ['value'])
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
        $json['newresources'] = array(
            'classUri' => $targetClass,
            'className' => strtolower($this->_resourceHelper->extractClassNameFromUri($targetClass)),
            'md5' => md5($resource),
            'resourceUri' => $resource,
            'label' => $resourceLabel,
            'checked' => 'checked'
            );
        $json['status'] = 'ok';
        $json['form'] = $f->getDataAsArrays();
        
        return $json;
    }
    
    
    /**
     * Change formula data in backend
     */
    public function changeFormulaData($form, $formOld, $upperResource = '', $relations = array())
    {
        // set model to write
        if ("" != $form->getTargetModel())
        {
            $this->_selectedModel = new Erfurt_Rdf_Model ($form->getTargetModel());
            $this->_selectedModelUri = $form->getTargetModel();
        }
        
        if ('' == $upperResource)
        {
            $json = array();
            $json['status'] = 'ok';            
            $json['formOld'] = $formOld->getDataAsArrays();
        }
        else
            $log = array ();
        
        
        // update label if needed
        $resourceLabel = implode (' ', $form->getLabelpartValues ());
        $resourceLabelOld = implode (' ', $formOld->getLabelpartValues ());
        
        if ($resourceLabel != $resourceLabelOld) {
            $this->_store->deleteMatchingStatements(
                $this->_selectedModelUri,
                $form->getResource(),
                "http://www.w3.org/2000/01/rdf-schema#label",
                array('value' => $resourceLabelOld, 'type' => 'literal', 'lang' => $this->_lang)
            );

            $this->_store->addStatement(
                $this->_selectedModelUri, 
                $form->getResource(),
                "http://www.w3.org/2000/01/rdf-schema#label", 
                array('value' => $resourceLabel, 'type' => 'literal', 'lang' => $this->_lang)
            );
        }
        
        // -------------------------------------------------------------
        $para = $form->getFormulaParameter();
        
        if (0 < count ( $para ))
        {
            $selectedResource = $form->getResource();
            
            $has = $para ['predicateToHealthState'];
            $healthState = $para ['healthState'];
            $healthStateTime = time ();
            $selectedReseourceName = $this->_resourceHelper->extractClassNameFromUri($selectedResource);
            
            /**
             * Creates following relations:
             * 
             * - SelectedResource       has                         healthState-Instance
             * - healthStateInstance    includesAffectedProperties  ALSFRSPropertySet-Instance
             * - healthStateInstance    includesSymptoms            ALSFRSSymptomSet-Instance
             */
                            
            // create a new healthState instance
            $healthStateInstance = $para['healthStateInstanceUri'] . 'HS' . $selectedReseourceName . date ( 'YmdHis', $healthStateTime );
            $this->addStmt(
                $healthStateInstance, 
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                $para['healthState']
            );
            
            // Add a timestamp
            $this->addStmt( 
                $healthStateInstance, 
                'http://www.dispedia.de/o/hasDate',
                date ( 'Y-m-d H:i:s', $healthStateTime )
            );
            
            // selectedResource  has  healthState instance
            $this->addStmt( $selectedResource, $has, $healthStateInstance );
            
            
            // create a new propertySet instance
            $propertySetInstance = $para['propertySetInstanceUri'] . 'PS' . $selectedReseourceName . date ( 'YmdHis', $healthStateTime );
            $this->addStmt( 
                $propertySetInstance,
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                $para['propertySet'] 
            );
            
            // HealthState-instance  includesAffactedProperties  PropertySet-instance
            $this->addStmt( $healthStateInstance, $para['predicateToPropertySet'], $propertySetInstance );
            
            
            // create a new propertySet instance
            $symptomSetInstance = $para['symptomSetInstanceUri'] . 'SS' . $selectedReseourceName . date ( 'YmdHis', $healthStateTime );
            $this->addStmt( 
                $symptomSetInstance,
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                $para['symptomSet'] 
            );
            
            // HealthState-instance  includesAffactedProperties  PropertySet-instance
            $this->addStmt( $healthStateInstance, $para['predicateToSymptomSet'], $symptomSetInstance );
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
                if ('predicate' == $entry ['sectiontype'] && false === is_object ($oldValue) && "http://www.w3.org/2000/01/rdf-schema#label" != $entry ['predicateuri']) {
                    
                    // TODO: mehrwertige Values werten hier falsch verglichen, also immer als unterschiedlich behandelt
                    if ($entry ['value'] != $oldValue || is_array($entry ['value']) || is_array($oldValue)) 
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
                            if (is_array($oldValue) && isset($entry['typeparamter'][0]['order'])  && 1 == $entry ['typeparameter'][0]['order'])
                            {
                                foreach ($oldValue as $valueNumber => $value)
                                {
                                    $this->removeStmt(
                                        $value,
                                        $entry ['typeparameter'][0]['successor'],
                                        null
                                    );
                                }
                            }
                            
                            if ('' == $upperResource)
                                $json['log'][] = 'remove ' . $form->getResource() . ' > '. $entry ['predicateuri']  . ' > '. $oldValue;
                            else
                                $log [] = 'remove ' . $form->getResource() . ' > '. $entry ['predicateuri']  . ' > '. $oldValue .' (index='. $entry ['index'] .')';
                        }
                        
                        if (is_array($entry ['value']))
                        {
                            foreach ($entry ['value'] as $valueNumber => $value)
                            {
                                if (isset($entry ['typeparameter'][0]['order']) && 1 == $entry ['typeparameter'][0]['order'] && 0 < $valueNumber)
                                    $this->addStmt(
                                        $value,
                                        $entry ['typeparameter'][0]['successor'],
                                        $entry ['value'][$valueNumber - 1]
                                    );
                                $this->addStmt(
                                    $form->getResource(),
                                    $entry ['predicateuri'],
                                    $value
                                );
                            }
                        }
                        else
                            if ("" != $entry ['value'])
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
        // TODO: hinzufügen von mehreren object values sollte nicht so statfinden, nur ne zwischenlösung.
        // Diese Funktion sollte nur einmal pro Tripel aufgerufen werden
        if (is_array($o))
        {
            foreach ($o as $object)
            {
                // set type(uri or literal)
                $type = true == Erfurt_Uri::check($object) 
                    ? 'uri'
                    : 'literal';
                
                // add a triple to datastore
                $this->_store->addStatement(
                    $this->_selectedModelUri, 
                    $s,
                    $p, 
                    array('value' => $object, 'type' => $type, 'lang' => $this->_lang)
                );
            }
            return;
        }
        else
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
                array('value' => $o, 'type' => $type, 'lang' => $this->_lang)
            );
        }
    }
    
    
    /**
     *
     */
    public function removeStmt($s, $p, $o)
    {
        $deletedStatements = 0;
        // TODO: löschen von mehreren object values sollte nicht so statfinden, nur ne zwischenlösung.
        // Diese Funktion sollte nur einmal pro Tripel aufgerufen werden
        if (is_array($o))
        {
            foreach ($o as $object)
            {
                // set type(uri or literal)
                $type = true == Erfurt_Uri::check($object) 
                    ? 'uri'
                    : 'literal';
                
                // aremove a triple form datastore
                $deletedStatements += $this->_store->deleteMatchingStatements(
                    $this->_selectedModelUri,
                    $s,
                    $p,
                    isset($o) ? array('value' => $object, 'type' => $type) : null
                );
            }
        }
        else
        {
            // set type(uri or literal)
            $type = true == Erfurt_Uri::check($o) 
                ? 'uri'
                : 'literal';
            
            // aremove a triple form datastore
            $deletedStatements =  $this->_store->deleteMatchingStatements(
                $this->_selectedModelUri,
                $s,
                $p,
                isset($o) ? array('value' => $o, 'type' => $type) : null
            );
        }
        
        return $deletedStatements;
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
        $results = $this->_store->sparqlQuery(
            'SELECT ?property ?value 
            WHERE {
                <' . $resourceUri . '> ?property ?value.
                FILTER (langmatches(lang(?value), "de") || REGEX(lang(?value), "^$")
            }'
        );

        // build an assoziative array
        foreach ($results as $result)
        {
            // TODO: muss das sein?
            // little QuickHack that the targetclass type relation will not
            // deleted by a form, this triple will be omitted
            if ("http://www.w3.org/1999/02/22-rdf-syntax-ns#type" == $result['property']
                && $this->_form->getTargetClass() == $result['value'])
                continue;
            
            // $properties[$result['property']] = $result['value'];
            if (isset($properties[$result['property']]))
            {
                if (is_array($properties[$result['property']]['value']))
                    $properties[$result['property']]['value'][] = $result['value'];
                else
                    $properties[$result['property']]['value'] = array(
                        0 => $properties[$result['property']]['value'],
                        1 => $result['value']
                    );
            }
            else
                $properties[$result['property']] = array ( 
                    'property' => $result['property'],
                    'value' => $result['value'],
                    'used' => false
                );
        }
        return $properties;
    }

    /**
     * loads data they are needed by some plugins in the form
     * @param $form Formula instance to be filled with fetched data
     */
    public function loadContextData (&$form)
    {
        // save sections
        $sections = $form->getSections();
        
        // 
        foreach ($sections as $sectionNumber => $sectionEntries) 
        {
            foreach ($sectionEntries as $entryNumber => $entry) 
            {
                if (true === is_array($entry))
                {                        
                    if ('predicate' == $entry ['sectiontype'])
                    {
                        
                        if ('class' == $entry ['type'])
                            // save classname from classuri
                            $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['classname'] = strtolower($this->_resourceHelper->extractClassNameFromUri($sections[$sectionNumber][$entryNumber]['typeparameter'][0]['class']));
                        if ('multiple' == $entry ['type'])
                        {
                            $order = "";
                            if (isset($entry['typeparameter'][0]['order']) && 1 == $entry['typeparameter'][0]['order'])
                                $order = $entry['typeparameter'][0]['successor'];
                            // save classname from classuri
                            $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['classname'] = strtolower($this->_resourceHelper->extractClassNameFromUri($sections[$sectionNumber][$entryNumber]['typeparameter'][0]['class']));
                            if ("" != $form->getResource())
                            {
                                if (isset($entry['typeparameter'][0]['filter']) && 'onlyBoundToThisResource' == $entry['typeparameter'][0]['filter'] )
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = $this->loadInstances($entry['typeparameter'][0]['class'], $entry['predicateuri'], $form->getResource(), $order);
                                else
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = $this->loadInstances($entry['typeparameter'][0]['class'], $order);
                            }
                            else
                            {
                                if (isset($entry['typeparameter'][0]['filter']) && 'onlyBoundToThisResource' == $entry['typeparameter'][0]['filter'] )
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = array();
                                else
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = $this->loadInstances($entry['typeparameter'][0]['class'], $order);
                            }
                            if (isset($entry['typeparameter'][0]['addotherinstances']) && 1 == $entry['typeparameter'][0]['addotherinstances'])
                                $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['allinstances'] = array_diff_key(
                                    $this->loadInstances($entry['typeparameter'][0]['class']),
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances']
                                );
                        }
                    }
                    elseif ('nestedconfig' == $entry ['sectiontype'])
                    {
                        $this->loadContextData ($entry ['form']);
                    }                    
                }
            }
        }
        $form->setSections($sections);
    }
    
    /**
     * loads all instances of a class
     * @param $classUri uri of the class which instances are seearched
     */
    public function loadInstances ($classUri, $filterProperty = '', $filterResource = '', $order = '')
    {
        $instances = array();
        $filter = '';
        if ('' != $filterProperty && '' != $filterResource)
            $filter = '<' . $filterResource . '> <' . $filterProperty . '> ?instanceUri.';
        
        if ('' != $order)
            $order = 'OPTIONAL {?instanceUri <' . $order . '> ?successorUri.}';
        
        $instancesResult = $this->_store->sparqlQuery(
            'SELECT ?instanceUri ?successorUri
            WHERE {
              ?instanceUri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <' . $classUri . '>.
              ' . $filter  . '
              ' . $order . '
            };'
        );

        $this->_titleHelper->reset();
        $this->_titleHelper->addResources($instancesResult, "instanceUri");
        $orderList = array();
        $orderlyInstances = array();
        $successorList = array();
        foreach ($instancesResult as $instance)
        {
            $instances[$instance['instanceUri']] = $this->_titleHelper->getTitle($instance['instanceUri'], $this->_lang);
            $successorList[$instance['successorUri']] = $instance['instanceUri'];
            if ("" != $order && "" == $instance['successorUri'])
            {
                $orderList[0] = $instance['instanceUri'];
                $orderlyInstances[$instance['instanceUri']] = $instances[$instance['instanceUri']];
            }
        }
        if ("" != $order)
        {
            for ($i = 0; $i < count($successorList) - 1; $i++)
            {
                $orderList[$i + 1] = $successorList[$orderList[$i]];
                $orderlyInstances[$successorList[$orderList[$i]]] = $instances[$successorList[$orderList[$i]]];
            }
            $instances = $orderlyInstances;
        }
        
        return $instances;
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
        $form->setMode('edit');
        if ( 0 == count($properties)) return;
        else
        {
            // save sections
            $sections = $form->getSections();
            
            // 
            foreach ($sections as $sectionNumber => $sectionEntries) 
            {
                foreach ($sectionEntries as $entryNumber => $entry) 
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
                            
                            $values = array();
                            
                            if (is_array($value))
                            {
                                if ("" != $entry ['typeclass'])
                                {
                                    foreach ($value as $relationValue)
                                    {
                                        if (false != in_array((string) $entry ['typeclass'], $this->getResourceTypeUris($relationValue)))
                                        {
                                            $values[] = $relationValue;
                                        }
                                    }
                                }
                                else
                                    $values = $value;
                            }
                            else
                                $values[] = $value;

                            foreach($values as $nestedValue)
                            {
                                if (false == isset ($nestedValue))
                                    continue;
                                else
                                {
                                    $newForm = clone $entry ['form'];
                                    $this->fetchFormulaData ($nestedValue, $newForm);
                                    $newForm->setFormulaType($form->getFormulaType());
                                    $forms = $form->getSectionKey($sectionNumber, $entryNumber, 'forms');
                                    $forms[] = clone $newForm;
                                    $form->setSectionKey($sectionNumber, $entryNumber, 'forms', $forms);
                                }
                            }
                            //setr old 'form' field
                            $forms = $form->getSectionKey($sectionNumber, $entryNumber, 'forms');
                            if (0 < count($forms))
                                $entry ['form'] = $forms[0];
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
            foreach ($properties as $index => $p) {
                if ($p['property'] == $property && false == $p['used']){
                    
                    if (!is_array($p))
                        // this value can be only used one time if it is not an array
                        $properties [$index]['used'] = true;
                    
                    return $p['value'];
                }
            }
        }
            
        return null;
    }
    
    /**
     * get type (targetclass) uris of a resource
     * @param $resourceUri URI of the resource
     * @return type uris of the resource
     */
    public function getResourceTypeUris($resourceUri)
    {
        $uris = array();
        $properties = $this->getResourceProperties ($resourceUri);
        $value = $this->getPropertyValue ($properties,$this->_predicateType);
        if (!is_array($value))
            $uris[] = $value;
        else
          $uris = $value;
        
        return $uris;
    }
    
    /**
     * get type (targetclass) of a resource
     * @param $resourceUri URI of the resource
     * @return string type of the resource
     */
    public function getResourceType($resourceUri)
    {
        $value = $this->getResourceTypeUris($resourceUri);
        // if a type was set
        if (true == isset ($value[0])) {
            return $this->getResourceTitle ($value[0]);
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
        $this->_titleHelper->reset();
        $this->_titleHelper->addResource($resource);
        return $this->_titleHelper->getTitle ($resource, $this->_lang);
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
