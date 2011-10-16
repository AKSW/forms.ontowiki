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
    public function __construct()
    {
        
    }
    
    
    /**
     * add / change a formula in / to datastore
     * @param
     * @return string json result
     */
    public static function submitFormula($form, $formOld)
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
                $form = Formula::initByArray($form);
                $formOld = Formula::initByArray($formOld);
                
                /*echo '<pre>'; var_dump ( $formOld->getDataAsArrays () ); echo '</pre>
                
                
                
                ';*/
                
                if (false === Formula::isValid($form)) {
                    
                } else {
                    // Add formula data to backend
                    if ('add' == $form->getMode())
                        $json = Data::addFormulaData($form);
                        
                    elseif ('edit' == $form->getMode())
                        $json = Data::changeFormulaData($form, $formOld);
                }
            }
        }
         
        return json_encode($json);
    }
    
    
    /**
     * adds a formula to backend
     * @param $f formula instance
     */
    public static function addFormulaData(&$f, $upperResource = null, $relations = array())
    {
        $targetClass = $f->getTargetClass();
        
        config::get('titleHelper')->addResource($targetClass);
                    
        // generate a new unique resource uri based on the target class
        $resource = Resource::generateUniqueUri($f);
        
        // add resource - rdf:type - targetclass
        Data::addStmt(
            $resource,
            config::get('predicate_type'),
            $targetClass 
        );
        
        $f->setResource($resource);
        
        // add relations between a upper resource and a new resource 
        if (null != $upperResource && 0 < count($relations)) {
            foreach ($relations as $relation) {
                Data::addStmt(
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
                    Data::addStmt(
                        $resource,
                        $entry ['predicateuri'],
                        $entry ['value'] 
                    );
                // sub formula
                } elseif ('nestedconfig' == $entry ['sectiontype']) {
                    Data::addFormulaData(
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
    public static function changeFormulaData($form, $formOld)
    {
        $json = array();
        $json['status'] = 'ok';
                
        foreach ($form->getSections() as $sectionEntries) {
            // extract title from array and delete it
            // so there only predicate and nestedconfig elements in it
            array_shift($sectionEntries);
            
            foreach ($sectionEntries as $entry) {
                
                $oldValue = $formOld->getPredicateValueByIndex($entry ['index']);
                
                // predicate
                if ('predicate' == $entry ['sectiontype']) {
                    
                    if ($entry ['value'] != $oldValue) {
                        Data::removeStmt($form->getResource(), $entry ['predicateuri'], $oldValue);
                        
                        $json['change'][] = 'remove ' . $form->getResource() . ' --- '. $entry ['predicateuri']  . ' --- '. $oldValue;
                        
                        Data::addStmt($form->getResource(), $entry ['predicateuri'], $entry ['value']);
                        
                        $json['change'][] = 'add ' . $form->getResource() .' ---- '. $entry ['predicateuri'] .' --- '. $entry ['value'];
                    }
                } 
                
                // sub formula
                elseif ('nestedconfig' == $entry ['sectiontype']) {
                    
                    if ( '0,5' == $entry ['index'] )
                    {
                        // echo '<pre>'; var_dump ( $entry ); echo '</pre>';
                    }
                    
                    Data::changeFormulaData ( $entry ['form'], $oldValue );
                  
                }
            }
        }
        
        $json['form'] = $form->getDataAsArrays();
        
        return $json;
    }
    
    
    /**
     * adds a triple to datastore
     */
    public static function addStmt($s, $p, $o)
    {
        // set type(uri or literal)
        $type = Resource::determineObjectType($o);
        
        // add a triple to datastore
        return config::get('store')->addStatement(
            config::get('selectedModelUri'), 
            $s,
            $p, 
            array('value' => $o, 'type' => $type)
        );
    }
    
    /**
     *
     */
    public static function removeStmt($s, $p, $o)
    {
        $type = Resource::determineObjectType($o);
        $object = array ('value'=>$o);
        
        if ('uri' == Erfurt_Uri::check($o)) {
            $object['type'] = Erfurt_Store::TYPE_IRI;
        } 
        else //  if ('literal' == $type) 
        {
            $object['type'] = Erfurt_Store::TYPE_LITERAL;
        }
        
        // aremove a triple form datastore
        return config::get('store')->deleteMatchingStatements(
            config::get('selectedModelUri'),
            $s,
            $p,
            $object
        );
    }
    
    
    /**
     * @param $s
     * @return string
     */
    public static function replaceNamespaces($s)
    {
        //TODO: no use of fix Uri       
	return str_replace('architecture:', 'http://als.dispedia.info/architecture/c/20110504/', $s);
    }
}
