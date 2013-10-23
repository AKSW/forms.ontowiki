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
    private $_store;
    private $_titleHelper;
    private $_uriParts;
    private $_form;
    private $_lang;
    private $_resourceHelper;
    private $_ontologies;
    private $_selectedModel;
    
    public function __construct($predicateType, $ontologies, $store, $titleHelper, $uriParts, &$form, $lang)
    {
        $this->_predicateType = $predicateType;
        $this->_ontologies = $ontologies;
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
                        
                    elseif ('changed' == $this->_form->getMode())
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
        
        if ("" != $f->getTargetModel())
        {
            $this->_selectedModel = $this->_ontologies[$this->_ontologies['namespaces'][$f->getTargetModel()]]['instance'];
        }
        
        if ("" != $f->getModelNamespace())
            $resourceNamespace .= $f->getTargetModel() . $f->getModelNamespace() . '/';
        else
            $resourceNamespace = $f->getTargetModel();
        
        // generate a new unique resource uri based on the target class
        $resource = $this->_resourceHelper->generateUniqueUri($f, $resourceNamespace, $this->_titleHelper, $this->_uriParts);
        
        // add resource - rdf:type - targetclass
        $this->_selectedModel->addStatement(
            $resource,
            $this->_predicateType, 
            array('value' => $targetClass, 'type' => 'uri')
        );
        
        // generate resource label
        $resourceLabel = implode (' ', $f->getLabelpartValues ());
        
        // add resource - rdfs:label - resourceLabel
        $this->_selectedModel->addStatement(
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
                    foreach ($entry ['forms'] as $nestedForm)
                    {
                        $this->addFormulaData(
                            $nestedForm,
                            $resource,
                            $entry ['relations'] 
                        );
                    }
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
        if ("" != $formOld->getTargetModel())
        {
            $this->_selectedModel = $this->_ontologies[$this->_ontologies['namespaces'][$formOld->getTargetModel()]]['instance'];
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
            $this->_selectedModel->deleteMatchingStatements(
                $form->getResource(),
                "http://www.w3.org/2000/01/rdf-schema#label",
                array('value' => $resourceLabelOld, 'type' => 'literal', 'lang' => $this->_lang)
            );
            //TODO: delete statement with and without language, later every propberty should have information about languge or not
            $this->_selectedModel->deleteMatchingStatements(
                $form->getResource(),
                "http://www.w3.org/2000/01/rdf-schema#label",
                array('value' => $resourceLabelOld, 'type' => 'literal')
            );

            $this->_selectedModel->addStatement(
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
            $selectedReseourceName = array();
            $selectedReseourceName[1] = '';
            preg_match('/urn:dispedia:pn\/([a-zA-Z0-9]+)\//', $selectedResource, $selectedReseourceName);

            /**
             * Creates following relations:
             * 
             * - SelectedResource       has                         healthState-Instance
             * - healthStateInstance    includesAffectedProperties  ALSFRSPropertySet-Instance
             * - healthStateInstance    includesSymptoms            ALSFRSSymptomSet-Instance
             */
                            
            // create a new healthState instance
            $healthStateInstance = $para['healthStateInstanceUri'] . 'HS.' . $selectedReseourceName[1] . '.' . date ( 'YmdHis', $healthStateTime );
            $this->addStmt(
                $healthStateInstance, 
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                $para['healthState']
            );
            
            // Add a timestamp
            $this->addStmt( 
                $healthStateInstance, 
                'http://www.dispedia.de/o/hasDate',
                date ( 'c', $healthStateTime )
            );
            
            // selectedResource  has  healthState instance
            $this->addStmt( $selectedResource, $has, $healthStateInstance );
            
            
            // create a new propertySet instance
            $propertySetInstance = $para['propertySetInstanceUri'] . 'PS.' . $selectedReseourceName[1] . '.' . date ( 'YmdHis', $healthStateTime );
            $this->addStmt( 
                $propertySetInstance,
                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                $para['propertySet'] 
            );
            
            // HealthState-instance  includesAffactedProperties  PropertySet-instance
            $this->addStmt( $healthStateInstance, $para['predicateToPropertySet'], $propertySetInstance );
            
            
            // create a new propertySet instance
            $symptomSetInstance = $para['symptomSetInstanceUri'] . 'SS.' . $selectedReseourceName[1] . '.' . date ( 'YmdHis', $healthStateTime );
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
                        $this->addStmt(
                            $propertySetInstance,
                            $para['predicateToPropertyOption'],
                            $entry ['value']
                        );
                    }
                    
                    elseif ( 'SymptomSet' == $entry ['typeparameter']['pertainsTo'] )
                    {
                        $this->addStmt(
                            $symptomSetInstance,
                            $para['predicateToSymptomOption'],
                            $entry ['value']
                        );
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
                            // in case of create a doctor but use
                            // the main resource in a person formula, which has additionally 
                            // a birthday, gender and sub formula address 
                            
                            // generate a new unique resource uri based on the target class
                            $resource = $this->_resourceHelper->generateUniqueUri(
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
                            
                            if (is_array($oldValue)) {
                                foreach ($oldValue as $valueNumber => $value)
                                {
                                    if (isset($entry['typeparameter'][0]['order']) && 1 == $entry ['typeparameter'][0]['order'])
                                    {
                                        $this->removeStmt(
                                            $value,
                                            $entry ['typeparameter'][0]['successor'],
                                            null
                                        );
                                        if ('' == $upperResource)
                                            $json['log'][] = 'remove ' . $value .' > '. $entry ['typeparameter'][0]['successor'] .' > '. null;
                                        else
                                            $log [] = 'remove ' . $value .' > '. $entry ['typeparameter'][0]['successor'] .' > '. null;
                                    }
                                    $this->removeStmt(
                                        $form->getResource(),
                                        $entry ['predicateuri'],
                                        $value
                                    );
                                    if ('' == $upperResource)
                                        $json['log'][] = 'remove ' . $form->getResource() . ' > '. $entry ['predicateuri']  . ' > '. $value;
                                    else
                                        $log [] = 'remove ' . $form->getResource() . ' > '. $entry ['predicateuri']  . ' > '. $value;
                                }
                            } else {
                                $this->removeStmt(
                                    $form->getResource(),
                                    $entry ['predicateuri'],
                                    $oldValue
                                );
                                
                                if ('' == $upperResource)
                                    $json['log'][] = 'remove ' . $form->getResource() . ' > '. $entry ['predicateuri']  . ' > '. $oldValue;
                                else
                                    $log [] = 'remove ' . $form->getResource() . ' > '. $entry ['predicateuri']  . ' > '. $oldValue .' (index='. $entry ['index'] .')';
                            }
                        }
                        
                        if (is_array($entry ['value']))
                        {
                            foreach ($entry ['value'] as $valueNumber => $value)
                            {
                                if (isset($entry ['typeparameter'][0]['order']) && 1 == $entry ['typeparameter'][0]['order'] && 0 < $valueNumber)
                                {
                                    $this->addStmt(
                                        $value,
                                        $entry ['typeparameter'][0]['successor'],
                                        $entry ['value'][$valueNumber - 1]
                                    );
                                    if ('' == $upperResource)
                                        $json['log'][] = 'add ' . $value .' > '. $entry ['typeparameter'][0]['successor'] .' > '. $entry ['value'][$valueNumber - 1];
                                    else
                                        $log [] = 'add ' . $value .' > '. $entry ['typeparameter'][0]['successor'] .' > '. $entry ['value'][$valueNumber - 1];
                                }
                                $this->addStmt(
                                    $form->getResource(),
                                    $entry ['predicateuri'],
                                    $value
                                );
                                
                                if ('' == $upperResource)
                                    $json['log'][] = 'add ' . $form->getResource() .' > '. $entry ['predicateuri'] .' > '. $value;
                                else
                                    $log [] = 'add ' . $form->getResource() .' > '. $entry ['predicateuri'] .' > '. $value;
                            }
                        }
                        else
                        {
                            if ("" != $entry ['value'])
                            {
                                $this->addStmt($form->getResource(), $entry ['predicateuri'], $entry ['value']);
                                
                                if ('' == $upperResource)
                                    $json['log'][] = 'add ' . $form->getResource() .' > '. $entry ['predicateuri'] .' > '. $entry ['value'];
                                else
                                    $log [] = 'add ' . $form->getResource() .' > '. $entry ['predicateuri'] .' > '. $entry ['value'] .' (index='. $entry ['index'] .')';
                            }
                        }
                        
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
                elseif ('nestedconfig' == $entry ['sectiontype']) 
                {
                    foreach ($entry['forms'] as $nestedFormKey => $nestedForm)
                    {
                        if (true === is_object ($oldValue[$nestedFormKey]))
                        {
                            if ('add' == $nestedForm->getMode())
                            {
                                foreach ($entry ['forms'] as $nestedForm)
                                {
                                    if ('' == $upperResource) 
                                        $json ['log'] [] = $this->addFormulaData(
                                            $nestedForm,
                                            $form->getResource(),
                                            $entry ['relations'] 
                                        );
                                    else
                                        $log = array_merge (
                                            $log,
                                            $this->addFormulaData(
                                                $nestedForm,
                                                $form->getResource(),
                                                $entry ['relations'] 
                                            )
                                        );
                                }
                            }
                            elseif ('changed' == $nestedForm->getMode())
                            {
                                if ('' == $upperResource) 
                                    $json ['log'] [] = $this->changeFormulaData (
                                        $nestedForm,
                                        $oldValue[$nestedFormKey],
                                        $form->getResource(),
                                        $entry['relations']
                                    );
                                else
                                    $log = array_merge (
                                        $log,
                                        $this->changeFormulaData (
                                            $nestedForm,
                                            $oldValue[$nestedFormKey],
                                            $form->getResource(),
                                            $entry['relations']
                                            )
                                        );
                            }
                        }
                    }
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
                $this->_selectedModel->addStatement(
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
            return $this->_selectedModel->addStatement(
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
                $deletedStatements += $this->_selectedModel->deleteMatchingStatements(
                    $s,
                    $p,
                    isset($o) ? array('value' => $object, 'type' => $type, 'lang' => $this->_lang) : null
                );
                //TODO: delete statement with and without language, later every propberty should have information about languge or not
                $deletedStatements += $this->_selectedModel->deleteMatchingStatements(
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
            $deletedStatements =  $this->_selectedModel->deleteMatchingStatements(
                $s,
                $p,
                isset($o) ? array('value' => $o, 'type' => $type, 'lang' => $this->_lang) : null
            );
            //TODO: delete statement with and without language, later every propberty should have information about languge or not
            $deletedStatements =  $this->_selectedModel->deleteMatchingStatements(
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
        
        if ("" != $this->_form->getRequestModel())
            $requestModel = $this->_ontologies[$this->_ontologies['namespaces'][$this->_form->getRequestModel()]]['instance'];
        else
            $requestModel = $this->_ontologies[$this->_ontologies['namespaces'][$this->_form->getTargetModel()]]['instance'];

        $resource = new OntoWiki_Resource($resourceUri, $requestModel);
        $resourceValues = $resource->getDescription();

        if (isset($resourceValues[$resourceUri]))
        {
            foreach ($resourceValues[$resourceUri] as $property => $values)
            {
                foreach ($values as $value)
                {
                    // TODO: muss das sein?
                    // little QuickHack that the targetclass type relation will not
                    // deleted by a form, this triple will be omitted
                    if ("http://www.w3.org/1999/02/22-rdf-syntax-ns#type" == $property
                        && $this->_form->getTargetClass() == $value['value'])
                        continue;
                
                    
                    if (isset ($value['lang']) && null != $value['lang'] && $value['lang'] != $this->_lang)
                        continue;
                    
                    // $properties[$result['property']] = $result['value'];
                    if (isset($properties[$property]))
                    {
                        if (is_array($properties[$property]['value']))
                            $properties[$property]['value'][] = $value['value'];
                        else
                            $properties[$property]['value'] = array(
                                0 => $properties[$property]['value'],
                                1 => $value['value']
                            );
                    }
                    else
                        $properties[$property] = array ( 
                            'property' => $property,
                            'value' => $value['value'],
                            'used' => false
                        );
                }
            }
        }
        
        return $properties;
    }

    /**
     *recursiv generation of an closure array from an store closure array
     */
    public function generateClosureArray($closureArray, $currentEntry)
    {
        $resultArray = array();
            
        foreach ($closureArray as $entryName => $entry)
        {
            if ($currentEntry == $entry['parent'])
                $resultArray[$entryName] = $this->generateClosureArray($closureArray, $entryName);
        }
        
        return $resultArray;
    }
    
    /**
     * loads data they are needed by some plugins in the form
     * @param $form Formula instance to be filled with fetched data
     */
    public function loadContextData (&$form)
    {
        // save sections
        $sections = $form->getSections();
        
        foreach ($sections as $sectionNumber => $sectionEntries) 
        {
            foreach ($sectionEntries as $entryNumber => $entry) 
            {
                if (true === is_array($entry))
                {                        
                    if ('predicate' == $entry ['sectiontype'])
                    {
                        if ('class' == $entry ['type'])
                        {
                            // save classname from classuri
                            $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['classname'] = strtolower($this->_resourceHelper->extractClassNameFromUri($sections[$sectionNumber][$entryNumber]['typeparameter'][0]['class']));
                            
                            //get all classes (transitive closure)
                            $transitiveClosureClasses = $this->_store->getTransitiveClosure($entry['typeparameter'][0]['classOntology'], 'http://www.w3.org/2000/01/rdf-schema#subClassOf', $entry['typeparameter'][0]['class']);
                            $classes = $this->generateClosureArray($transitiveClosureClasses, $entry['typeparameter'][0]['class']);
                            
                            $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['classes'] = $classes;
                            $this->_titleHelper->addResources(array_keys($transitiveClosureClasses));
                            
                            if (isset($entry['typeparameter'][0]['instanceOntology']))
                            {
                                $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = $this->loadInstances (
                                    $entry['typeparameter'][0]['instanceOntology'],
                                    $entry['typeparameter'][0]['class'],
                                    isset($entry['typeparameter'][0]['filter']) ? $entry['typeparameter'][0]['filter'] : null,
                                    isset($entry['typeparameter'][0]['filterProperty']) ? $entry['typeparameter'][0]['filterProperty'] : null,
                                    $form->getResource(),
                                    null,
                                    true
                                );
                            }
                            else
                                $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = array();
                            
                        }
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
                                {
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = $this->loadInstances(
                                        $form->getTargetModel(),
                                        $entry['typeparameter'][0]['class'],
                                        $entry['typeparameter'][0]['filter'],
                                        $entry['predicateuri'],
                                        $form->getResource(),
                                        $order
                                    );
                                    //overwrite the values of the entry because of there orderng is not correct
                                    $sections[$sectionNumber][$entryNumber]['value'] = array_keys($sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances']);
                                }
                                else
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = $this->loadInstances(
                                        $form->getTargetModel(),
                                        $entry['typeparameter'][0]['class'],
                                        $entry['typeparameter'][0]['filter'],
                                        null,
                                        null,
                                        $order
                                    );
                            }
                            else
                            {
                                if (isset($entry['typeparameter'][0]['filter']) && 'onlyBoundToThisResource' == $entry['typeparameter'][0]['filter'] )
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = array();
                                else
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances'] = $this->loadInstances(
                                        $form->getTargetModel(),
                                        $entry['typeparameter'][0]['class'],
                                        $entry['typeparameter'][0]['filter'],
                                        null,
                                        null,
                                        $order
                                    );
                            }
                            if (isset($entry['typeparameter'][0]['addotherinstances']) && 1 == $entry['typeparameter'][0]['addotherinstances'])
                                $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['allinstances'] = array_diff_key(
                                    $this->loadInstances(
                                        $form->getTargetModel(),
                                        $entry['typeparameter'][0]['class']
                                    ),
                                    $sections[$sectionNumber][$entryNumber]['typeparameter'][0]['instances']
                                );
                        }
                        if ('resource' == $entry ['type'])
                        {
                            $sections[$sectionNumber][$entryNumber]['typeparameter']['resources'] = $resources = OntoWiki_Utils::getClosureInstances(
                                $entry ['typeparameter']['resourceOntology'],
                                $this->_lang,
                                'http://www.w3.org/2000/01/rdf-schema#subClassOf',
                                array($entry ['typeparameter']['resourceClass']),
                                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
                            );
                        }
                    }
                    elseif ('nestedconfig' == $entry ['sectiontype'])
                    {
                        foreach ($entry ['forms'] as $nestedForm)
                            $this->loadContextData ($nestedForm);
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
    public function loadInstances (
        $modelIri,
        $classUri,
        $filterType = '',
        $filterProperty = '',
        $filterResource = '',
        $order = '',
        $classAsKey = false
    )
    {
        $instances = array();
        $filter = '';
        if ('onlyBoundToThisResource' == $filterType && '' != $filterProperty && '' != $filterResource)
            $filter = '  <' . $filterResource . '> <' . $filterProperty . '> ?instanceUri.';
        else if ('unbound' == $filterType)
        {
            $filter = '  OPTIONAL {?subject <' . $filterProperty . '> ?instanceUri .}' . "\n" .
                      '  FILTER (?subject = <' . $filterResource . '> OR !BOUND(?subject))';
        }
        
        if ('' != $order)
            $order = 'OPTIONAL {?instanceUri <' . $order . '> ?successorUri.}' . "\n";
        
        // get the closure
        $closureResults = $this->_store->getTransitiveClosure($modelIri, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', $classUri);
        $closureFilter = '  FILTER (' . "\n";
        
        foreach ($closureResults as $closureUri => $closureResult)
        {
            $closureFilter .= '    ?classUri = <'. $closureUri .'> OR ' . "\n";
        }
        
        $closureFilter .= '    FALSE' . "\n" . '  )';

        $instancesResult = $this->_store->sparqlQuery(
            'SELECT ?instanceUri ?classUri' . ('' != $order ? ' ?successorUri ' : ' ') . "\n" .
            'WHERE {' . "\n" .
            '  ?instanceUri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ?classUri .' . "\n" .
              $filter . "\n" .
              $closureFilter . "\n" .
              $order . "\n" .
            '};'
        );
        
        $this->_titleHelper->reset();
        $this->_titleHelper->addResources($instancesResult, "instanceUri");
        
        $orderList = array();
        $orderlyInstances = array();
        $successorList = array();
        foreach ($instancesResult as $instance)
        {
            $title = $this->_titleHelper->getTitle($instance['instanceUri'], $this->_lang);
            if (true == $classAsKey)
                $instances[$instance['classUri']][$instance['instanceUri']] = $title;
            else
                $instances[$instance['instanceUri']] = $title;
                
            if ("" != $order)
            {
                $successorList[$instance['successorUri']] = $instance['instanceUri'];
                if ("" == $instance['successorUri'])
                {
                    $orderList[0] = $instance['instanceUri'];
                    $orderlyInstances[$instance['instanceUri']] = $title;
                }
            }
        }
        
        if (true == $classAsKey)
            return $instances;

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
        // set model to write
        if ("" != $form->getTargetModel())
        {
            $this->_selectedModel = $this->_ontologies[$this->_ontologies['namespaces'][$form->getTargetModel()]]['instance'];
        }
        
        // fetch direct neighbours of the resource
        $properties = $this->getResourceProperties($resource);
        if (!isset($resource) || "" == $resource)
        {
            $form->setMode('new');
            $resource = "";
        }
        else
            $form->setMode('edit');
        // set forms resource
        $form->setResource ($resource);
        
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

                            $forms = array();
                            foreach($values as $nestedValue)
                            {
                                $newNestedForm = clone $entry ['forms'][0];
                                $this->fetchFormulaData ($nestedValue, $newNestedForm);
                                $newNestedForm->setFormulaType($form->getFormulaType());
                                $forms[] = $newNestedForm;
                            }
                            
                            if (0 < count($forms))
                                $form->setSectionKey($sectionNumber, $entryNumber, 'forms', $forms);
                        }                    
                    }
                }
            }
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
