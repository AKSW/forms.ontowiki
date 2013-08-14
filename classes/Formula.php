<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Formula 
{
    /**
     * Stores all data about this formula
     */
    private $_dataInstance;
    
    private $_architectureUri;
    
    
    /**
     * 
     */
    public function __construct($index)
    {
        $this->_data = array ();
        
        $this->setIndex ($index);       
        $this->_data ['targetclass'] = '';
        $this->_data ['targetmodel'] = '';
        $this->_data ['requestmodel'] = '';
        $this->_data ['modelnamespace'] = '';
        $this->_data ['events'] = array();
        $this->_data ['moduleContexts'] = array();
        $this->_data ['labelparts'] = array ();
        $this->_data ['labelpartsoption'] = '';
        $this->_data ['title'] = '';
        $this->_data ['description'] = '';
        $this->_data ['mode'] = 'new';
        $this->_data ['resource'] = "";
        $this->_data ['sections'] = array ();
        $this->_data ['selectResourceOfType'] = '';
        $this->_data ['formulaType'] = 'normal';
        $this->_data ['formulaParameter'] = array ();
    }
    
    
    // -----------------------------------------------------------------
    
    
    /**
     * @return void 
     */
    public function setFormulaParameter ($value)
    {
        $this->_data ['formulaParameter'] = $value;
    }
    
    
    /**
     * @return string 
     */
    public function getFormulaParameter ()
    {
        return $this->_data ['formulaParameter'];
    }
    
    
    /**
     * @return void 
     */
    public function setFormulaType ($value)
    {
        $this->_data ['formulaType'] = $value;
    }
    
    
    /**
     * @return string 
     */
    public function getFormulaType ()
    {
        return $this->_data ['formulaType'];
    }
    
    
    /**
     * @return void 
     */
    public function setDescription ($value)
    {
        $this->_data ['description'] = $value;
    }
    
    
    /**
     * @return string 
     */
    public function getDescription ()
    {
        return $this->_data ['description'];
    }
    
    
    /**
     * @return string 
     */
    public function getIndex ()
    {
        return (string) $this->_data ['index'];
    }
    
    
    /**
     * @param $v new index
     */
    public function setIndex ($v)
    {
        $this->_data ['index'] = (string) $v;
    }
    
    
    /**
     * @return void 
     */
    public function setMode ($value)
    {
        $this->_data ['mode'] = $value;
    }
    
    
    /**
     * @return string 
     */
    public function getMode ()
    {
        return $this->_data ['mode'];
    }
    
    
    /**
     * @param $value URI
     * @return void 
     */
    public function setResource ($value)
    {
        $this->_data ['resource'] = $value;
    }
    
    
    /**
     * @return string
     */
    public function getResource ()
    {
        return $this->_data ['resource'];
    }
    
    /**
     * @return array
     */
    public function getEvents ()
    {
        return $this->_data ['events'];
    }
    
    /**
     * @param $value event name
     * @return void 
     */
    public function setEvent ($value)
    {
        $this->_data ['events'][] = $value;
    }
    
    /**
     * @param $value array with event names
     * @return void 
     */
    public function setEvents ($value)
    {
        $this->_data ['events'] = $value;
    }
    
    /**
     * @return array
     */
    public function getModuleContexts ()
    {
        return $this->_data ['moduleContexts'];
    }
    
    /**
     * @param $value context name
     * @return void 
     */
    public function setModuleContext ($value)
    {
        $this->_data ['moduleContexts'][] = $value;
    }
    
    /**
     * @param $value array with context names
     * @return void 
     */
    public function setModuleContexts ($value)
    {
        $this->_data ['moduleContexts'] = $value;
    }
    /**
     * @param $value URI of target class
     * @return void 
     */
    public function setSelectResourceOfType ($value)
    {
        $this->_data ['selectResourceOfType'] = $value;
    }
    
    /**
     * @param $sections $sections to add
     * @return void 
     */
    public function setSections ($sections)
    {
        $this->_data ['sections'] = $sections;
    }
    
    /**
     * @param $sectionNumber number of section
     * @param $sectionEntryNumber number of section
     * @param $key array key to change
     * @param $value new value
     * @return void 
     */
    public function setSectionKey ($sectionNumber, $sectionEntryNumber, $key, $value)
    {
        $this->_data ['sections'][$sectionNumber][$sectionEntryNumber][$key] = $value;
    }
    
    /**
     * @param $sectionNumber number of section
     * @param $sectionEntryNumber number of section
     * @param $key array key to change
     * @return section value 
     */
    public function getSectionKey ($sectionNumber, $sectionEntryNumber, $key)
    {
        return $this->_data ['sections'][$sectionNumber][$sectionEntryNumber][$key];
    }
    
    
    /**
     * @return string 
     */
    public function getSelectResourceOfType ()
    {
        return $this->_data ['selectResourceOfType'];
    }
    
    
    /**
     * @param $value URI of target class
     * @return void 
     */
    public function setTargetClass ($value)
    {
        $this->_data ['targetclass'] = $value;
    }

    /**
     * @return string 
     */
    public function getTargetClass ()
    {
        return $this->_data ['targetclass'];
    }
    
    
    /**
     * @param $value URI of target model
     * @return void 
     */
    public function setTargetModel ($value)
    {
        $this->_data ['targetmodel'] = $value;
    }
    
    /**
     * @param $value URI of request model
     * @return void 
     */
    public function setRequestModel ($value)
    {
        $this->_data ['requestmodel'] = $value;
    }

    /**
     * @return string 
     */
    public function getTargetModel ()
    {
        return $this->_data ['targetmodel'];
    }
    
    /**
     * @return string 
     */
    public function getRequestModel ()
    {
        return $this->_data ['requestmodel'];
    }
    
    
    /**
     * @param $value URI of target model
     * @return void 
     */
    public function setModelNamespace ($value)
    {
        $this->_data ['modelnamespace'] = $value;
    }
    
    /**
     * @return string 
     */
    public function getModelNamespace ()
    {
        return $this->_data ['modelnamespace'];
    }
    
     
    /**
     * @return void 
     */
    public function setTitle ($value)
    {
        $this->_data ['title'] = $value;
    }
    
    
    /**
     * @return string 
     */
    public function getTitle ()
    {
        return $this->_data ['title'];
    }
    
    
    /**
     * @return void 
     */
    public function setXmlFile ($value)
    {
        $this->_data ['xmlfile'] = $value;
    }
    
    
    /**
     * @return string 
     */
    public function getXmlFile ()
    {
        return $this->_data ['xmlfile'];
    }
    
    
    // -----------------------------------------------------------------
    
     
    /**
     * Extract field type.
     * @return string
     */
    public function getFieldType ($predicate, $t)
    {
        $t = (string) $t; 
        
        if (true == isset ($t) AND '' != $t)
        {
            return $t;
        }
        
        else
        {
            // TODO determine field type by predicate url
            $type = 'xsd:string';
                
            return $type;
        }
    }
    
    
    /**
     * 
     */
    public function addLabelpart ($value)
    {
        $this->_data ['labelparts'] [] = $value;
    }
    
    
    /**
     * 
     */
    public function getLabelparts ()
    {
        return $this->_data ['labelparts'];
    }
    
    /**
     * 
     */
    public function getLabelpartsOption ()
    {
        return $this->_data ['labelpartsoption'];
    }
    
    
    /**
     * 
     */
    public function removeLabelparts ()
    {
        $this->_data ['labelparts'] = array ();
    }
    
    
    /**
     * @param $value Array of URIs
     * @return void 
     */
    public function setLabelparts ($value)
    {
        $this->_data ['labelparts'] = $value;
    }
    
    /**
     * @param $value String of option
     * @return void 
     */
    public function setLabelpartsOption ($value)
    {
        $this->_data ['labelpartsoption'] = $value;
    }
    
    
    /**
     * @return void
     */
    public function addSection ($value)
    {
        $this->_data ['sections'] [] = $value;
    }

    
    /**
     * @return array
     */
    public function getSections ()
    {
        return $this->_data ['sections'];
    }
    
    
    /**
     * 
     */
    public function getData ()
    {
        return $this->_data;
    }
    
    /**
     * @return string
     */
    public function toString ($offsetString = '')
    {        
        $return = '<br/>' . $offsetString . '- title: '. $this->getTitle () .
                '<br/>' . $offsetString . '- index: '. $this->getIndex () .
                '<br/>' . $offsetString . '- description: '. $this->getDescription () .
                '<br/>' . $offsetString . '- label parts: '. implode (', ', $this->getLabelparts ()) .
                '<br/>' . $offsetString . '- label parts option: '. $this->getLabelpartsOption () .
                '<br/>' . $offsetString . '- mode: '. $this->getMode () .
                '<br/>' . $offsetString . '- resource: '. $this->getResource () .
                '<br/>' . $offsetString . '- target class: '. $this->getTargetClass () .
                '<br/>' . $offsetString . '- target model: '. $this->getTargetModel () .
                '<br/>' . $offsetString . '- request model: '. $this->getRequestModel () .
                '<br/>' . $offsetString . '- model namespace: '. $this->getModelNamespace () .
                '<br/>' . $offsetString . '- XML config: '. $this->getxmlfile () .
                '<br/>' . $offsetString . '- formtype: '. $this->getFormulaType ();
                
        $return .= '<br/>' . $offsetString . '- events: ';
        foreach ($this->getEvents () as $eventName)
        {
            $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;- ' . $eventName;
        }
        $return .= '<br/>' . $offsetString . '- module contexts: ';
        foreach ($this->getModuleContexts () as $contextName)
        {
            $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;- ' . $contextName;
        }
        $return .= '<br/>' . $offsetString . '- sections: ';
          
        foreach ($this->getSections () as $section)
        {
            foreach ($section as $s)
                if (isset($s['sectiontype']) && 'predicate' == $s['sectiontype'])
                {
                    $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;+ predicate ';
                    $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp; - index: '. $s ['index'];
                    $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp; - title: '. $s ['title'];
                    $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp; - name: '. $s ['name'];
                    $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp; - predicateuri: '. $s ['predicateuri'];
                    if (isset($s ['typeparameter'][0]))
                    {
                        $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp; - typeparameter:';
                        foreach ($s ['typeparameter'][0] as $key => $value)
                        {
                            $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . $key . ': ' . (true == is_array($value) ? implode($value, "; ") : $value);
                        }
                    }
                    $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp; - value: '. $this->getPredicateValue($s ['index']);
                }
                elseif (isset($s['sectiontype']) && 'nestedconfig' == $s ['sectiontype'])
                {
                    $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;+ nestedconfig ';
                    $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp;- typeclass: ' . $s ['typeclass'];
                    
                    foreach ($s ['relations'] as $relation)
                        $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp;- relation: ' . $relation;
                        
                    if (0 < count($s ['forms']))
                    {
                        $return .= '<br/>' . $offsetString . '&nbsp;&nbsp;&nbsp; - forms: '. count($s ['forms']);
                        foreach ($s ['forms'] as $nestedform)
                        {
                            $return .= '<br/>' . $offsetString;
                            $return .= $nestedform->toString ($offsetString . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                        }
                    }
                }
            }
        return $return;
    }
    
    
    /**
     * @return array
     */
    public function getDataAsArrays ()
    {
        $arr = array (
            'title'                 => $this->getTitle (),
            'index'                 => $this->getIndex (),
            'formulaType'           => $this->getFormulaType (),
            'formulaParameter'      => $this->getFormulaParameter (),
            'description'           => $this->getDescription (),
            'selectResourceOfType'  => $this->getSelectResourceOfType (),
            'labelparts'            => $this->getLabelparts (),
            'labelpartsoption'      => $this->getLabelpartsOption (),
            'mode'                  => $this->getMode (),
            'resource'              => $this->getResource (),
            'targetclass'           => $this->getTargetClass (),
            'targetmodel'           => $this->getTargetModel (),
            'modelnamespace'        => $this->getModelNamespace (),
            'events'                => $this->getEvents (),
            'moduleContects'        => $this->getModuleContexts (),
            'xmlfile'               => $this->getXmlFile (),
            'sections'              => array ()
        );
        
        foreach ($this->getSections () as $entry)
        {
            $newSection = array ();
            $newSection ['title'] = $entry ['title'];
            
            for ($i = 0; $i < (count ($entry)-1); ++$i)
            {
                $s = $entry [$i];
                
                if ('predicate' == $s ['sectiontype'])
                {
                    $newSection [] = array (
                        'index'         => $s ['index'],
                        'title'         => $s ['title'],
                        'name'          => $s ['name'],
                        'value'         => $s ['value'],
                        'mandatory'     => $s ['mandatory'],
                        'predicateuri'  => $s ['predicateuri'],
                        'sectiontype'   => $s ['sectiontype'],
                        'type'          => $s ['type'],
                        'typeparameter' => $s ['typeparameter']
                   );
                }
                elseif ('nestedconfig' == $s ['sectiontype'])
                {
                    $forms = array();
                    foreach ($s ['forms'] as $form)
                        $forms[] = $form->getDataAsArrays ();
                    
                    $newSection [] = array (
                        'sectiontype'   => $s ['sectiontype'],
                        'relations'     => $s ['relations'],
                        'index'         => $s ['index'],
                        'xmlfile'       => $s ['xmlfile'],
                        'forms'         => $forms
                   );
                }
            }
            
            $arr ['sections'][] = $newSection;
        }
                
        return $arr;
    }

    
    /**
     * @return Formula
     */
    public function initByArray ($formArray)
    {  
        // init a new Formula instance
        $form = new Formula ($formArray ['index']);

        $form->setTitle ($formArray ['title']);
        
        $form->setDescription ($formArray ['description']);
        
        $form->setSelectResourceOfType ($formArray ['selectResourceOfType']);
        
        $form->setLabelparts ($formArray ['labelparts']);
        
        $form->setLabelpartsOption ($formArray ['labelpartsoption']);
        
        $form->setMode ($formArray ['mode']);
        
        $form->setResource ($formArray ['resource']);
        
        $form->setTargetClass ($formArray ['targetclass']);
        
        $form->setTargetModel ($formArray ['targetmodel']);
        
        $form->setModelNamespace ($formArray ['modelnamespace']);
        
        $form->setEvents ($formArray ['events']);
        
        $form->setModuleContexts ($formArray ['moduleContexts']);
        
        $form->setXmlFile ($formArray ['xmlfile']);
        
        $form->setFormulaType ($formArray ['formulaType']);
        
        $form->setFormulaParameter ($formArray ['formulaParameter']);
        
                        
        foreach ($formArray ['sections'] as $entry)
        {
            $newSection = array ('title' => $entry ['title']);
            
            foreach ($entry as $section)
            {
                if (false == isset($section ['sectiontype']) )
                    continue;
                
                if ('predicate' == $section ['sectiontype'])
                {
                    if (!isset($section ['value']))
                        continue;
                    
                    $newSection [] = array (
                        'index'         => $section ['index'],
                        'name'          => $section ['name'],
                        'value'         => $section ['value'],
                        'predicateuri'  => $section ['predicateuri'],
                        'type' 		    => $section ['type'],
                        'typeparameter' => $section ['typeparameter'],
                        'title'	        => $section ['title'],
                        'mandatory'     => $section ['mandatory'],
                        'sectiontype'   => 'predicate'
                   );
                }
                elseif ('nestedconfig' == $section ['sectiontype'])
                {                                
                    $forms = array();
                    foreach ($section ['forms'] as $nestedForm)
                        $forms[] = $this->initByArray ($nestedForm);
                    
                    $newSection [] = array (
                        'xmlfile'      => $section ['forms'][0]['xmlfile'],
                        'index'        => $section ['forms'][0]['index'],
                        'relations'    => $section ['relations'],
                        //TODO: use forms instead of form
                        'forms'        => $forms,
                        'sectiontype'  => 'nestedconfig'
                   );
                }    
            }
            
            $form->addSection ($newSection);
        }
        
        return $form;
    }
    
    
    /** 
     * Check a formula
     * @return boolean 
     */
    public function isValid ($f)
    {
        if ('new' == $f->getMode ())
        {
            
        }
        
        elseif ('add' == $f->getMode ())
        {
            foreach ($f->getSections () as $sectionEntries)
            {
                // extract title from array and delete it
                // so there only predicate and nestedconfig elements in it
                $title = array_shift($sectionEntries);
            }
        }
        
        // TODO implement Formula::isValid
        return true;
    }
    
    
    /**
     * extracts the values of as labelpart marked predicates
     * @return array list of labelpart values
     */
    public function getLabelpartValues ()
    {
        $values = array ();
        
        foreach ($this->getLabelparts () as $lp)
        {
            foreach ($this->getSections () as $sectionEntries)
            {
                // extract title from array and delete it
                // so there only predicate and nestedconfig elements in it
                array_shift($sectionEntries);
                
                foreach ($sectionEntries as $entry)
                {
                    if ('predicate' == $entry ['sectiontype'] &&
                         $lp == $entry ['predicateuri'])
                    {
                        $values [] = $entry ['value'];
                    }
                } 
            }
        }
        
        return $values;
    }
    
    
    /**
     * 
     */
    public function setPredicateValue ($index, $value)
    {
        // $sections = array_slice($this->getSections(), 1);
        $sections = $this->getSections ();
        $count = count ($sections);
            
        //for ( $i = 0; $i < $count; ++$i )
        foreach ($sections as $keySection => $sectionEntries) 
        {
            // $countEntries = count($sections [$i]);
            // echo '<br><br>-'. $keySection;

            // for ($j = 0; $j < $countEntries; ++$j)
            foreach ($sectionEntries as $keyEntry => $entry) 
            {
                // echo '<pre>'; var_dump ( $this->_data ['sections'] [$keySection] [$keyEntry] ); echo '</pre><hr>';
                
                if (true == isset($entry ['index']) && $index == $entry ['index'])
                {
                    $this->_data ['sections'] [$keySection] [$keyEntry] ['value'] = $value;
                }
            }
        }
    }
    
    
    /**
     *
     *
     */
    public function getPredicateValue ($index)
    {
        if ($this->getIndex () == $index)
        {
            return $this;
        }
        
        foreach ($this->getSections () as $sectionEntries) 
        {
            // extract title from array and delete it
            // so there only predicate and nestedconfig elements in it
            array_shift($sectionEntries);
            
            foreach ($sectionEntries as $entry)
            {                
                // predicate
                if ('predicate' == $entry ['sectiontype'] && $index == $entry ['index'])
                {
                    return $entry ['value'];
                }
                
                elseif ('nestedconfig' == $entry ['sectiontype'] && $index == $entry ['index'])
                {
                    return $entry ['forms'];
                } 
                
                // sub formula
                elseif ('nestedconfig' == $entry ['sectiontype'])
                {
                    foreach ($entry ['forms'] as $nestedForm)
                    {
                        $result = $nestedForm->getPredicateValue($index);
                        
                        if ('' != $result)
                            return $result;
                    }
                }
                
            } 
        }
        return '';
    }
}
