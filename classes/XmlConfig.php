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
     * @return Formula Filled formula instance
	 */
	public static function loadFile ( $file, $index = 0 )
	{
        $xml = simpleXML_load_file ( $file ); 
        
        if( false === $xml ) 
        { 
           //deal with error 
        } 
        else
        {
            $form = new Formula ( $index );
            
            $form->setXmlConfig ( $file );
            
            // ReadIn all readable data from XML-Config file.
            foreach ($xml as $nodeName => $nodeValue) 
            {	
                switch ( $nodeName )
                {
                    case 'title':
                        $form->setTitle ( (string) $nodeValue [0] );
                        break;
                        
                        
                    case 'description':
                        $form->setDescription ( (string) $nodeValue [0] );
                        break;
                        
                        
                    case 'targetclass':
                        $form->setTargetClass ( (string) $nodeValue [0] );
                        
                        break;
                        
                        
                    case 'labelparts':
                        
                        foreach ( $xml->labelparts->item as $nodeValue )
                        {
                            $form->addLabelpart ( (string) $nodeValue [0] );
                        }
                            
                        break;
                        
                        
                    case 'sections':					
                        
                        foreach ( $xml->sections->item as $nodeValue ) 
                        {
                            $newSection = array ();
                            
                            $newSection ['title'] = (string) $nodeValue->title;
                            
                            // Iterate over predicate entries.
                            foreach ( $nodeValue->predicate as $predicate )
                            {	
                                // get complete URI of predicate
                                $p = XmlConfig::replaceNamespace ( $predicate->predicateuri );
                                
                                $titleHelper = new OntoWiki_Model_TitleHelper ( 
                                    config::get ( 'selectedModel' )
                                );                                
                                $titleHelper->addResource( $p );
                                
                                $type = Formula::getFieldType ( $p, $predicate->type );
                                $typeparameter = array ();

                                
                                // If set, get type parameters
                                if ( true == isset ( $predicate->typeparameter ) )
                                    foreach ( $predicate->typeparameter->item as $parameter )
                                        $typeparameter [] = array ( 
                                            'label' => $parameter->label,
                                            'value' => $parameter->value 
                                        );
                                    
                                
                                // Build an entry instance.
                                $newSection [] = array ( 
                                    'predicateuri'  => XmlConfig::replaceNamespace ( (string) $predicate->predicateuri ),
                                    'type' 		    => $type,
                                    'typeparameter' => $typeparameter,
                                    'title'	        => $titleHelper->getTitle( $p ), 
                                    'mandatory'     => (int) $predicate->mandatory,
                                    'sectiontype'   => 'predicate'
                                );
                            }
                             
                            // Iterate over nestedconfig entries.                       
                            foreach ( $nodeValue->nestedconfig as $nestedconfig )
                            {                                             
                                // Load XML Config
                                $f = XmlConfig::loadFile ( 
                                    config::get ( 'dirXmlConfigurationFiles' ) . $nestedconfig->target,
                                    $index+1 
                                );
                                
                                $relations = array ();
                                
                                if ( true === isset ( $nestedconfig->relations ) )
                                    foreach ( $nestedconfig->relations->item as $rel )
                                        $relations [] = (string) $rel;
                                                                                    
                                // Add entry to nestedconfig array.
                                $newSection [] = array ( 
                                     'target'       => (string) $nestedconfig->target,
                                     'relations'    => $relations,
                                     'form'         => $f, 
                                     'sectiontype'  => 'nestedconfig'
                                );
                            }
                            
                            $form->addSection ( $newSection );
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
     * 
     */
    public static function replaceNamespace ( $s )
    {
        // TODO: no use of fix Uri                                   
		return str_replace ( 'architecture:', 'http://als.dispedia.info/architecture/c/20110504/', $s );
    }
}
