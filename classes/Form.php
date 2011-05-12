<?php

/**
 * Represents a form container.
 */
class Form
{
	public $headline;
	public $introduceText;
	public $trigger;
	public $sections;
	public $model;
	
	public function __construct ( $m )
	{
		$this->headline = 'New form';
		$this->introduceText = '';
		$this->trigger = '';
		$this->sections = array ();
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
					
				case 'trigger':
					$this->trigger = $nodeValue [0];
					break;
					
				case 'sections':					
				
					foreach ( $xml->sections->item as $nodeValue ) 
					{
						$newSection = array ();
						
						$newSection ['caption'] = $nodeValue->caption;
						
						foreach ( $nodeValue->predicate as $predicate )
						{
							// Collect attribute stuff
							$a = array ();
							
							foreach ( $predicate->attributes() as $attr => $val )
							{ 
								$a [ $attr ] = $val;
                                
                              //  echo $attr .' ' . $val .'<br>';
							}
                            //echo '<br>';
							
							$p = $this->replaceNamespaces ( (string) $predicate [0] );
							
							$titleHelper = new OntoWiki_Model_TitleHelper ( $this->model );
							$titleHelper->addResource( $p );
                            
                            //echo 'SELECT ?subject WHERE {?subject <' . $p . '> ?o.}<pre>';
                            
                            //var_dump($this->model->sparqlQuery('SELECT ?subject WHERE {?subject <' . $p . '> ?o.}'));
							//echo '</pre>';
							// echo '<br> > '. (string) $predicate [0] .' => ' . $p . ' ( '. $titleHelper->getTitle( $p ) .' )';
                            
                            
                            
							
							$newSection ['fields'] [] = array ( 'type' 		=> (string) $a ['type'],
															    'caption'	=> $titleHelper->getTitle( $p ), 
															    'mandatory' => (int) 	$a ['mandatory'],
																'name' 		=> (string) $predicate [0],
                                                                'target'    => $this->replaceNamespaces ((string) $a ['target'] ));
                                                                

						}
						
						$this->sections [] = $newSection;
					}
				
					break;
					
				default: 				
					break;
			}
		}
	}
	
	public function replaceNamespaces ( $s )
	{                                        
		$s = str_replace ( 'architecture:', 'http://als.dispedia.info/architecture/c/20110504/', $s );
		
		return $s;
	}
}

