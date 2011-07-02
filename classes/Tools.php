<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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
	public static function getClassRelevantConfigFile ( $classUri, &$m, &$c )
	{
        // TODO Handle classes which have a # at the end!
		$className = substr ( $classUri, 1+strrpos ( $classUri, '/' ) );
        
        
        // Set standard xml file, which will be loaded if no mapping is available.
        $xmlConfigName = $c->mapping->standard .'.xml';
                
        
        // If mapping for current class was found
        if ( '' != $c->mapping->$className )
        {
            $xmlConfigName = $c->mapping->$className .'.xml';
        }
        else
        {
            $i = 0;
            
            // Search class tree upper to find a class which have a xml config
            while ( true )
            {
                $newClassUri = Tools::getSuperClass ( $classUri, $m );
                
                $className = substr ( $newClassUri, 1+strrpos ( $newClassUri, '/' ) );
                
                // If mapping for current class was found
                if ( '' != $c->mapping->$className )
                {
                    $xmlConfigName = $c->mapping->$className .'.xml';
                }
                else
                {
                    // Set new class uri and go one step ahead in class tree.
                    $classUri = $newClassUri;
                }
                
                ++$i;
                
                if ( $i == 2 ) break; 
            }
        }
        
        // Returning name of xml config file.
        return $xmlConfigName;
	}
    
    /**
     * Get superclass of a class.
     * 
     * @param $classUri Uri of childclass.
     */
    public static function getSuperClass ( $classUri, &$m )
    {
        // TODO: Handle case if more than one superclass.
        
        $a = $m->sparqlQuery(
            'SELECT ?superclass 
              WHERE {
                  <' . $classUri . '> <http://www.w3.org/2000/01/rdf-schema#subClassOf> ?superclass.
              }'
        );
            
        return isset ( $a [0] ['superclass'] )
               ? $a [0] ['superclass'] 
               : '';
    }
    
    /**
     *
     */
	public static function replaceNamespaces ( $s )
	{
        //TODO: no use of fix Uri                                   
		return str_replace ( 'architecture:', 'http://als.dispedia.info/architecture/c/20110504/', $s );
	}
    
    /**
     * 
     */
    public static function generateUniqueUri ( $modelUri, $className, $label )
    {
		$time = time ();
        
        return $modelUri . 'i/' . 
			   date ( 'Ymd', $time ) . '/' . 
			   $className . '/' .
			   substr ( md5 ($time . $className), 0, 6 ) . '/' . 
			   str_replace ( ' ', '', $label ); 
    }
    
    /**
     * Make mapping between md5-fields and XML config
     * @param Form The form which should be interpreted.
     * @return array Array with mappings.
     */
    public static function getFieldMappings ( &$form )
    {
        $mappingArray = array ();
        
        foreach ( $form->sections as $section )
        {            
            ## Iterate about predicates, only if predicate was set ##
            if ( true == isset ( $section ['predicate'] ) )
            {
    
                foreach ( $section ['predicate'] as $predicate )
                {
                    $mappingArray [] = array ( 
                        'targetclass'   => substr ( (string) $form->targetclass,
                                                    strpos ( (string) $form->targetclass, ':' ) + 1 ),
                        'predicateuri'  => (string) $predicate ['predicateuri'],
                        'md5'           => md5 ( $form->targetclass . $predicate ['predicateuri'] ),
                        'mandatory'     => $predicate ['mandatory']
                    );
                }
                
            }
            
            ## Iterate about nestedconfigs, only if nestedconfig was set ##
            if ( true == isset ( $section ['nestedconfig'] ) )
            {
                
                // Include formulas from nested configs
                foreach ( $section ['nestedconfig'] as $nestedconfig )
                {                    
                    foreach ( $nestedconfig ['form']->sections as $section )
                    {                                            
                        // Iterate about predicates, only if predicate was set
                        if ( true == isset ( $section ['predicate'] ) )
                            foreach ( $section ['predicate'] as $predicate )
                            {
                                $mappingArray [] = array ( 
                                    'targetclass'   => substr ( (string) $nestedconfig ['form']->targetclass,
                                                                strpos ( (string) $nestedconfig ['form']->targetclass, ':' ) + 1 ),
                                    'predicateuri'  => (string) $predicate ['predicateuri'],
                                    'md5'           => md5 ( $nestedconfig ['form']->targetclass . $predicate ['predicateuri'] ),
                                    'mandatory'     => $predicate ['mandatory']
                                );
                            }
                    }
                }
                
            }
        }
        
        return $mappingArray;
    }
    
    /**
     * Extract target classes from form.
     * @param $form Reference of form.
     * @return array Target classes.
     */
    public static function getTargetClasses ( &$form )
    {
        $targetClasses = array ();
        
        // Level 0 target class
        $targetClasses [] = substr ( (string) $form->targetclass,
                                     strpos ( (string) $form->targetclass, ':' ) + 1 );
        
        foreach ( $form->sections as $section )
        {            
            ## Iterate about nestedconfigs, only if nestedconfig was set ##
            if ( true == isset ( $section ['nestedconfig'] ) )
            {
                
                // Include formulas from nested configs
                foreach ( $section ['nestedconfig'] as $nestedconfig )
                {                    
                    $targetClasses [] = substr ( (string) $nestedconfig ['form']->targetclass,
                                                 strpos ( (string) $nestedconfig ['form']->targetclass, ':' ) + 1 );
                }
                
            }
        }
        
        return $targetClasses;
    }
    
    /**
     * Extract target classes from form.
     * @param $form Reference of form.
     * @return array Target classes.
     */
    public static function getLabelParts ( &$form )
    {
        $labelParts = array ();
        
        // Level 0 target class
        $level0TargetClass = substr ( (string) $form->targetclass,
                                       strpos ( (string) $form->targetclass, ':' ) + 1 );
        
        
        // Saves level 0 target class as key and labelparts array as value.
        // for example:
        //      arr [ class0 ] = array ( 'http://.../firstname', 'http://.../lastname' );
        foreach ( $form->labelparts as $part )
        {
            $labelParts [ $level0TargetClass ] [] = Tools::replaceNamespaces ( $part );
        }
        
        
        foreach ( $form->sections as $section )
        {            
            ## Iterate about nestedconfigs, only if nestedconfig was set ##
            if ( true == isset ( $section ['nestedconfig'] ) )
            {
                
                // Include formulas from nested configs
                foreach ( $section ['nestedconfig'] as $nestedconfig )
                {                    
                    $class = substr ( (string) $nestedconfig ['form']->targetclass,
                                      strpos ( (string) $nestedconfig ['form']->targetclass, ':' ) + 1 );
                    
                    foreach ( $nestedconfig ['form']->labelparts as $part )
                    {
                        $labelParts [ $class ] [] = Tools::replaceNamespaces ( $part );
                    }
                                                 
                    // TODO Extends this to a dynamic depth!
                }
                
            }
        }
        
        return $labelParts;
    }
    
    /**
     * Get all relations between a XML config and their nestedconfig's.
     * 
     * @return array Array with relations.
     */
    public static function getNestedConfigRelations ( &$form )
    {
        $relations = array ();
        $entry = array ();
        
        foreach ( $form->sections as $section )
        {              
            ## Iterate about nestedconfigs, only if nestedconfig was set ##
            if ( true == isset ( $section ['nestedconfig'] ) )
            {                
                // Include formulas from nested configs
                foreach ( $section ['nestedconfig'] as $nestedconfig )
                {                  
                    $entry = array ();
                    
                    
                    // Get targetclass from nestedconfig item.
                    $entry ['targetclass'] = substr ( (string) $nestedconfig ['form']->targetclass,
                                                      strpos ( (string) $nestedconfig ['form']->targetclass, ':' ) + 1 );
                     
                                         
                    // Get all relations between nestedconfig item.
                    foreach ( $nestedconfig ['relations']->item as $relation )
                    {
                        $entry ['relations'] [] = Tools::replaceNamespaces ( (string) $relation );
                    }
                    
                    
                    // Add entry to relations list.
                    $relations [] = $entry;
                }
                
            }
        }
        
        return $relations;
    }
    
    /**
     * Loads a specific XML config file and returns a Form instance
     * which include all relevant stuff.
     * @param $file Name of XML file.
     * @return Form Instance of Form class.
     */
    public static function loadFormByXmlConfig ( $file, $m )
    {        
        // Load XML config.
		$form = new Form ( $m );
        $form->loadConfig ( realpath(dirname(__FILE__)) . '/../formconfigs/'. $file );
        
        return $form;
    }
    
    /**
     * 
     */
    public static function dumpIt ( $var )
    {
        echo '<pre>';
        var_dump ( $var );
        echo '</pre>';
    }
    
    /**
     * Read all XML files in formconfig folder and generate a list.
     * @return array List of all XML config files.
     */
    public static function getConfigFileList ()
    {
        $dir = realpath(dirname(__FILE__)) .'/../formconfigs';
        $handle = opendir($dir);
        $fileList = array ();
        
        // Iterate over files in formconfig's folder.
        while ($file = readdir ($handle)) {
            
            if( $file != '.' && $file != '..' 
                && is_file ($dir.'/'.$file)
                && false !== strpos ( $file, '.xml' ) ) {
                    
                    // Only add XML files.
                    $fileList [] = $file;
            }
        }
            
        return $fileList;
    }
}
