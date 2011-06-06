<?php

/**
 * Represents a form container.
 */
class Form
{
	public $headline;
	public $introduceText;
	public $sections;
	public $model;
	
	public function __construct ( &$m )
	{   
		$this->headline = 'New form';
		$this->introduceText = '';
		$this->targetclass = '';
		$this->sections = array ();
		$this->labelparts = array ();
		$this->model = $m;
	}
	
	/**
	 * Loads a xml file and imports some data into class attributes.
	 */
	public function loadConfig ( $file )
	{
		$xml = simplexml_load_file ( $file );
				
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
					break;
					
                    
				case 'labelparts':
                    
                    foreach ( $xml->labelparts as $nodeValue )
                        $this->labelparts [] = $nodeValue [0];
                        
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
                                    $typeparameter [] = $parameter;
                                }
                            }                            
							
                            // Build an entry instance.
                            $entry = array ( 'predicateuri' => $predicate->predicateuri,
                                             'type' 		=> $type,
                                             'typeparameter'=> $typeparameter,
                                             'caption'	    => $titleHelper->getTitle( $p ), 
                                             'mandatory'    => (int) $predicate->mandatory );
                            
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
    
    /**
     * 
     */
    public function getFieldType ( $predicate, $t )
    {
        if (true == isset ( $t ) AND '' != $t )
        {
            return $t;
        }
        
        else
        {
            // Get range infos for predicate
            $range = $this->model->sparqlQuery('SELECT ?object 
                                                 WHERE {
                                                     <' . $predicate . '> <http://www.w3.org/2000/01/rdf-schema#range> ?object.
                                                 }');
            
            $type = 'xsd:string';
            
            // If a range was defined
            if (0 < count($range) AND true == isset ( $range[0]['object'] ) )
                $type = substr ( $range[0]['object'],
                                 1+strrpos ( $range[0]['object'], '/' ) );
                
            return $type;
        }
    }
}

