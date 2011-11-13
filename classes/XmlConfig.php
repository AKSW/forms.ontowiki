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
    private $_titleHelper;
    private $_dirXmlConfigurationFiles;
    private $_language;
    
    public function __construct ($titleHelper, $dirXmlConfigurationFiles, $language)
    {
        $this->_titleHelper = $titleHelper;
        $this->_dirXmlConfigurationFiles = $dirXmlConfigurationFiles;
        $this->_language = $language; // de, en
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
                        foreach ($nodeValue [0]->children() as $lang) {
                            $form->setTitle ((string) $nodeValue [0]->{$this->_language});
                            break;
                        }
                        break;
                        
                        
                    case 'description':
                        $form->setDescription ((string) $nodeValue [0]->{$this->_language});
                        break;
                        
                        
                    case 'selectResourceOfType':
                        $form->setSelectResourceOfType ((string) $nodeValue [0]);
                        break;
                        
                        
                    case 'targetclass':
                        $form->setTargetClass ((string) $nodeValue [0]);
                        
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
                        }
                        
                        $form->setFormulaParameter ( $p );
                        
                        break;
                        
                        
                    case 'labelparts':
                        
                        foreach ($xml->labelparts->item as $nodeValue)
                        {
                            $form->addLabelpart ((string) $nodeValue [0]);
                        }
                            
                        break;
                        
                        
                    case 'sections':	
                    
                        $entryIndex = 0;
                        
                        foreach ($xml->sections->item as $nodeValue) 
                        {
                            $newSection = array ();
                            
                            $newSection ['title'] = (string) $nodeValue->title;
                            
                            // Iterate over predicate entries.
                            foreach ($nodeValue->predicate as $predicate)
                            {	
                                // get complete URI of predicate
                                $p = $form->replaceNamespaces ($predicate->predicateuri);
                                
                                                                
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
                                        foreach ($predicate->typeparameter->resource as $resource)
                                        {
                                            $typeparameter = $form->replaceNamespaces ( (string) $resource );
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
                                    $this->_titleHelper->addResource ($p);
                                    $title = $this->_titleHelper->getTitle ($p);
                                    
                                    if (true == Erfurt_Uri::check($title))
                                        $title = Resource::extractClassNameFromUri ($title);
                                }
                                
                                
                                // Build an entry instance.
                                $newSection [] = array (
                                    'index'         => $form->getIndex() . ',' . $entryIndex,
                                    'name'          => substr (md5 ($form->getIndex() . ',' . $entryIndex), 0, 10),
                                    'predicateuri'  => $form->replaceNamespaces ((string) $predicate->predicateuri),
                                    'type' 		    => $type,
                                    'typeparameter' => $typeparameter,
                                    'title'	        => $title, 
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
                                $xmlConfig = new XmlConfig($this->_titleHelper, $this->_dirXmlConfigurationFiles);
                                
                                $f = $xmlConfig->loadFile(
                                    $nestedconfig->xmlfile,
                                    new Formula($form->getIndex() .','. $entryIndex, $form->getSelectedModel())
                               );
                                
                                $tmpRel = $relations = array ();
                                
                                if (true === isset ($nestedconfig->relations))
                                    foreach ($nestedconfig->relations->item as $rel)
                                        $relations [] = $form->replaceNamespaces((string) $rel);
                                 
                                // Add entry to nestedconfig array.
                                $newSection [] = array (
                                     'xmlfile'      => (string) $nestedconfig->xmlfile,
                                     'index'        => $form->getIndex() .','. $entryIndex,
                                     'relations'    => $relations,
                                     'form'         => $f, 
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
}
