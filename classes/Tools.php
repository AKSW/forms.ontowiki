<?php

/**
 * Represents a form container.
 */
class Tools
{
    private $_model;
    
	public function __construct ( &$m, &$c )
	{
		$this->_model = $m;
		$this->_config = $c;
	}
	
	/**
	 *
	 */
	public function getClassXmlConfig ( $classUri )
	{
		$className = substr ( $classUri, 1+strrpos ( $classUri, '/' ) );
                
        // If mapping for current class was found
        if ( '' != $this->_config->$className )
        {
            $xmlConfigName = $this->_config->$className .'.xml';
        }
        else
        {
            $i = 0;
            
            // Search class tree upper to find a class which have a xml config
            while ( true )
            {
                $newClassUri = $this->getSuperClass ( $classUri );
                
                $className = substr ( $newClassUri, 1+strrpos ( $newClassUri, '/' ) );
                
                // If mapping for current class was found
                if ( '' != $this->_config->$className )
                {
                    $xmlConfigName = $this->_config->$className .'.xml';
                }
                else
                {
                    // Set new class uri and go one step ahead in class tree.
                    $classUri = $newClassUri;
                }
                
                $i++;
                
                if ( $i == 2 ) break; 
            }
        }
        
        // Load XML and read formular headline tag.
        $xmlConfig = simplexml_load_file ( realpath(dirname(__FILE__)) .'/../formconfigs/'. $xmlConfigName );
	}
    
    /**
     * Get superclass of a class.
     * 
     * @param $classUri Uri of childclass.
     */
    public function getSuperClass ( $classUri )
    {
        // TODO: Handle case if more than one superclass.
        
        $a = $this->_model->sparqlQuery(
            'SELECT ?superclass 
              WHERE {
                  <' . $classUri . '> <http://www.w3.org/2000/01/rdf-schema#subClassOf> ?superclass.
              }'
        );
            
        return $a [0] ['superclass'];
    }
}

