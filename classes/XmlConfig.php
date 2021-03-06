<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class XmlConfig 
{
    private $_dispediaModel;
    private $_titleHelper;
    private $_dirXmlConfigurationFiles;
    private $_language;
    private $_dataHelper;
    private $_resourceHelper;
    
    public function __construct ($dataHelper, $resourceHelper, $titleHelper, $dirXmlConfigurationFiles, $language)
    {
        $this->_titleHelper = $titleHelper;
        $this->_dirXmlConfigurationFiles = $dirXmlConfigurationFiles;
        $this->_language = $language; // de, en
        $this->_dataHelper = $dataHelper;
        $this->_resourceHelper = $resourceHelper;
    }
    
    /**
     * Loads a xml file.
     * @param $file name of XML configuration file.
     * @return Formula Filled formula instance
     */
    public function loadFile ($file, &$form)
    {
        $file = $this->_dirXmlConfigurationFiles . $file;
        
        // load file
        $xml = true === file_exists ($file)
            ? simpleXML_load_file ($file)
            : false; 
        
        if(false === $xml) 
        { 
           //deal with error 
           return;
        } 
        else
        {
            $xmlFile = substr($file, 1+strrpos ($file, '/')); // cut filename
            $xmlFile = substr($xmlFile, 0, strpos ($xmlFile, '.')); // delete extension
            $form->setXmlFile($xmlFile);
                
            // generate a specify XML 
            
            // ReadIn all readable data from XML-Config file.
            foreach ($xml as $nodeName => $nodeValue) 
            {	
                switch ($nodeName)
                {
                    case 'title':
                        $form->setTitle((string) $nodeValue [0]->{$this->_language});
                        break;
                        
                        
                    case 'description':
                        $form->setDescription ((string) $nodeValue [0]->{$this->_language});
                        break;
                        
                        
                    case 'selectResourceOfType':
                        $form->setSelectResourceOfType ((string) $nodeValue [0]);
                        break;
                    
                    case 'events':
                        foreach ($nodeValue [0] as $trigger)
                            $form->setEvent((string) $trigger);
                        break;
                    
                    case 'module':
                        foreach ($nodeValue [0] as $contextName)
                            $form->setModuleContext((string) $contextName);
                        break;
                        
                    case 'targetclass':
                        $form->setTargetClass ((string) $nodeValue [0]);
                        
                        // set title from targetclass if no title is set in xml
                        $title = $this->_dataHelper->getResourceTitle((string) $form->getTargetClass());

                        if (true == Erfurt_Uri::check($title) || '' == $title)
                            $title = $this->_resourceHelper->extractClassNameFromUri ($form->getTargetClass());
                            
                        $form->setTitle($title);
                        
                        break;
                        
                    case 'targetmodel':
                        $form->setTargetModel ((string) $nodeValue [0]);
                        
                        break;
                    
                    case 'requestmodel':
                        $form->setRequestModel ((string) $nodeValue [0]);
                        
                        break;
                        
                    case 'modelnamespace':
                        $form->setModelNamespace ((string) $nodeValue [0]);
                        
                        break;   
                        
                    case 'formulaType':
                        $form->setFormulaType ((string) $nodeValue [0]);
                        
                        break;
                        
                        
                    case 'formulaParameter':
                        
                        $p = array ();
                        
                        if ( 'alsfrs' == $form->getFormulaType () )
                        {
                            $p ['predicateToHealthState'] = (string) $xml->formulaParameter->predicateToHealthState;
                            $p ['healthState'] = (string) $xml->formulaParameter->healthState;
                            $p ['healthStateInstanceUri'] = (string) $xml->formulaParameter->healthStateInstanceUri;
                            
                            $p ['predicateToPropertySet'] = (string) $xml->formulaParameter->predicateToPropertySet;
                            $p ['propertySet'] = (string) $xml->formulaParameter->propertySet;
                            $p ['propertySetInstanceUri'] = (string) $xml->formulaParameter->propertySetInstanceUri;
                            
                            $p ['predicateToSymptomSet'] = (string) $xml->formulaParameter->predicateToSymptomSet;
                            $p ['symptomSet'] = (string) $xml->formulaParameter->symptomSet;
                            $p ['symptomSetInstanceUri'] = (string) $xml->formulaParameter->symptomSetInstanceUri;
                            
                            $p ['predicateToPropertyOption'] = (string) $xml->formulaParameter->predicateToPropertyOption;
                            $p ['predicateToSymptomOption'] = (string) $xml->formulaParameter->predicateToSymptomOption;
                            
                            $p ['optionUri'] = (string) $xml->formulaParameter->optionUri;
                        }
                        
                        $form->setFormulaParameter ( $p );
                        
                        break;
                        
                        
                    case 'labelparts':
                        
                        foreach ($xml->labelparts->item as $nodeValue)
                        {
                            $form->addLabelpart ((string) $nodeValue [0]);
                        }
                        if (isset($xml->labelparts->option[0]))
                            $form->setLabelpartsOption((string) $xml->labelparts->option[0]);
                            
                        break;
                        
                        
                    case 'sections':	
                    
                        $entryIndex = 0;
                        
                        foreach ($xml->sections->item as $nodeValue) 
                        {
                            $newSection = array ();
                            
                            $newSection ['title'] = (string) $nodeValue->title->{$this->_language};
                            
                            // Iterate over predicate entries.
                            foreach ($nodeValue->predicate as $predicate)
                            {	
                                // get complete URI of predicate
                                $p = $predicate->predicateuri;
                                
                                $type = $form->getFieldType ($p, $predicate->type);
                                $typeparameter = array ();

                                
                                // set typeparameters
                                switch ( $type )
                                {                    
                                    // a ALSFRS question with options
                                    case 'alsfrsquestion':
                                        
                                        $options = array ();
                                        
                                        foreach ($predicate->typeparameter->options->item as $i)
                                            $options [] = (string) $i;
                                    
                                        $typeparameter = array ( 
                                            'pertainsTo'    => (string) $predicate->typeparameter->pertainsTo,
                                            'options'       => $options
                                        );
                                        
                                        break;
                                           
                                    case 'date': 
                                        break;
                                    
                                    // a simple list of label/value pairs
                                    case 'list':
                                        foreach ($predicate->typeparameter->item as $parameter)
                                            $typeparameter [] = array (
                                                'label' => (string) $parameter->label->{$this->_language},
                                                'value' => (string) $parameter->value 
                                           );
                                        break;
                                        
                                    // a simple list of resources of a given class
                                    // TODO: extend it to use more than one class
                                    case 'resource':
                                        foreach (get_object_vars($predicate->typeparameter) as $name => $value)
                                            $typeparameter[$name] = $value;
                                        break;
                                        
                                    // a simple list of classes of a given overclass
                                    case 'class':
                                        foreach ($predicate->typeparameter as $item)
                                        {
                                            $typeparameter = array();
                                            foreach ($item as $parameter)
                                            {
                                                $newTypeParamter = array();
                                                
                                                foreach (get_object_vars($parameter) as $name => $value)
                                                    $newTypeParamter[$name] = (string) $value;
                                                    
                                                $typeparameter[] = $newTypeParamter;
                                            }
                                            break;
                                        }
                                        break;
                                    
                                    // a simple list of instances
                                    case 'multiple':
                                        foreach ($predicate->typeparameter as $item)
                                        {
                                            $typeparameter = array();
                                            foreach ($item as $parameter)
                                            {
                                                $newTypeParamter = array();
                                                
                                                foreach (get_object_vars($parameter) as $name => $value)
                                                    $newTypeParamter[$name] = (string) $value;
                                                
                                                $typeparameter[] = $newTypeParamter;
                                            }
                                            break;
                                        }
                                        break;
                                    
                                    // hidden input fields with a fix value
                                    case 'hidden':
                                        foreach ($predicate->typeparameter as $item)
                                        {
                                            $typeparameter = array();
                                            foreach ($item as $parameter)
                                                {
                                                    $typeparameter[] = array (
                                                        'value' => (string) $parameter->value
                                                    );
                                                }
                                            break;
                                        }
                                        break;
                                    default: 
                                        break;
                                }
                                
                                // if a title was explicit set in the XML config file
                                $title = (string) $predicate->title->{$this->_language};
                                
                                if ('' == $title)
                                {
                                    $title = $this->_dataHelper->getResourceTitle((string) $predicate->predicateuri);

                                    if (true == Erfurt_Uri::check($title) || '' == $title)
                                        $title = $this->_resourceHelper->extractClassNameFromUri ($p);
                                }                                
                                
                                // Build an entry instance.
                                $newSection [] = array (
                                    'index'         => $form->getIndex() . ',' . $entryIndex,
                                    'name'          => substr (md5 ($file . ',' . $form->getIndex() . ',' . $entryIndex), 0, 10),
                                    'predicateuri'  => (string) $predicate->predicateuri,
                                    'type'          => $type,
                                    'typeparameter' => $typeparameter,
                                    'title'	    => $title, 
                                    'mandatory'     => (int) $predicate->mandatory,
                                    'value'         => '',
                                    'sectiontype'   => 'predicate'
                               );
                                
                                ++$entryIndex;
                            }
                             
                            // Iterate over nestedconfig entries.                       
                            foreach ($nodeValue->nestedconfig as $nestedconfig)
                            {                                             
                                // Load XML Config
                                $xmlConfig = new XmlConfig(
                                    $this->_dataHelper,
                                    $this->_resourceHelper,
                                    $this->_titleHelper,
                                    $this->_dirXmlConfigurationFiles,
                                    $this->_language
                                );
                                
                                $f = $xmlConfig->loadFile(
                                    $nestedconfig->xmlfile,
                                    new Formula($form->getIndex() .','. $entryIndex)
                               );
                                
                                $tmpRel = $relations = array ();
                                
                                if (true === isset ($nestedconfig->relations))
                                    foreach ($nestedconfig->relations->item as $rel)
                                        $relations [] = (string) $rel;
                                 
                                // Add entry to nestedconfig array.
                                $newSection [] = array (
                                     'xmlfile'      => (string) $nestedconfig->xmlfile,
                                     'index'        => $form->getIndex() .','. $entryIndex,
                                     'relations'    => $relations,
                                     'typeclass'    => isset ($nestedconfig->typeclass) ? $nestedconfig->typeclass : "",
                                    //TODO: use forms instead of form
                                     'forms'        => array($f),
                                     'sectiontype'  => 'nestedconfig'
                               );
                                
                                ++$entryIndex;
                            }
                            
                            $form->addSection ($newSection);
                        }
                    
                        break;
                        
                    default: 				
                        break;
                }
            }
        
            return $form;
        }
    }
    
    /**
     * Generate a list of all form/xmlconfigs files.
     * @return Array of formnames
     */
    public function getFormList ()
    {
	$directory = opendir($this->_dirXmlConfigurationFiles);
	$formlist = array();
	while (false !== ($file = readdir($directory))) {
	    if ("." != $file && ".." != $file)
		$formlist[] = substr($file, 0, strpos ($file, '.'));
	}
	array_multisort($formlist);
	return $formlist;
    }
}
