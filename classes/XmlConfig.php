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
    public function __construct ()
    {
        
    }
    
	/**
	 * Loads a xml file.
     * @param $file name of XML configuration file.
	 */
	public function loadXmlConfigurationFile ( $file )
	{
        $xml = simpleXML_load_file($file); 
        
        if( false === $xml ) 
        { 
           //deal with error 
        } 
        else
        {
            $form = new Formula ();
            
            
            // ReadIn all readable data from XML-Config file.
            foreach ($xml as $nodeName => $nodeValue) 
            {	
                switch ( $nodeName )
                {
                    
                    case 'headline':
                        $this->headline = $nodeValue [0];
                        break;
                        
                        
                    case 'introduceText':
                        $this->introduceText = $nodeValue [0];
                        break;
                        
                        
                    case 'targetclass':
                        $this->targetclass = $nodeValue [0];
                        
                        $this->targetclasslabel = Tools::extractClassNameFromUri($this->targetclass);
                        
                        break;
                        
                        
                    case 'labelparts':
                        
                        foreach ( $xml->labelparts->item as $nodeValue )
                            $this->labelparts [] = (string) $nodeValue [0];
                            
                        break;
                        
                        
                    case 'sections':					
                    
                        foreach ( $xml->sections->item as $nodeValue ) 
                        {
                            $newSection = array ();
                            
                            $newSection ['caption'] = $nodeValue->caption;
                            
                            // Iterate over predicate entries.
                            foreach ( $nodeValue->predicate as $predicate )
                            {	
                                $p = Tools::replaceNamespaces ( $predicate->predicateuri );
                                
                                $titleHelper = new OntoWiki_Model_TitleHelper ( $this->model );
                                $titleHelper->addResource( $p );
                                
                                // Get type of this field.
                                $type = $this->getFieldType ( $p, $predicate->type );
                                $typeparameter = array ();
                                
                                // If set, get type parameters
                                if ( true == isset ( $predicate->typeparameter ) )
                                {
                                    foreach ( $predicate->typeparameter->item as $parameter )
                                    {
                                        $typeparameter [] = array ( 'label' => $parameter->label,
                                                                    'value' => $parameter->value );
                                    }
                                }                            
                                
                                // Build an entry instance.
                                $entry = array ( 'predicateuri' => Tools::replaceNamespaces ( (string) $predicate->predicateuri ),
                                                 'type' 		=> $type,
                                                 'typeparameter'=> $typeparameter,
                                                 'caption'	    => $titleHelper->getTitle( $p ), 
                                                 'mandatory'    => (int) $predicate->mandatory,
                                                 'resourcevalue'=> '' );
                                
                                // Add entry to predicate array.
                                $newSection ['predicate'] [] = $entry;
                            }
                             
                            // Iterate over nestedconfig entries.                       
                            foreach ( $nodeValue->nestedconfig as $nestedconfig )
                            {                                             
                                // Load XML Config
                                $form = new Form ( $this->model );
                                $form->loadConfig ( realpath(dirname(__FILE__)) . 
                                                    '/../formconfigs/' .
                                                    $nestedconfig->target  );
                                                    
                                // Build an entry instance.
                                $entry = array ( 'target'       => $nestedconfig->target,
                                                 'relations'    => $nestedconfig->relations,
                                                 'form'         => $form );
                                
                                // Add entry to nestedconfig array.
                                $newSection ['nestedconfig'] [] = $entry;
                            }
                            
                            $this->sections [] = $newSection;
                        }
                    
                        break;
                        
                    default: 				
                        break;
                }
            }
        }
	}
}
