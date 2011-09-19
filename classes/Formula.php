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
    private $_data;
    
    
    /**
     * 
     */
    public function __construct( $index )
    {
        $this->_data = array ();
        
        $this->_data ['index'] = $index;        
        $this->_data ['mode'] = 'add';
        $this->_data ['resources'] = array ();
        $this->_data ['sections'] = array ();
    }
    
    
    // -----------------------------------------------------------------
    
    
    /**
     * @return void 
     */
    public function setDescription ( $value)
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
        return $this->_data ['index'];
    }
    
    
    /**
     * @return void 
     */
    public function setMode ( $value)
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
     * @param $value Array of URIs
     * @return void 
     */
    public function setResources ( $value )
    {
        $this->_data ['resources'] = $value;
    }
    
    
    /**
     * @return array 
     */
    public function getResources ()
    {
        return $this->_data ['resources'];
    }
    
    
    /**
     * @param $value URI of target class
     * @return void 
     */
    public function setTargetClass ( $value )
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
     * @return void 
     */
    public function setTitle ( $value)
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
    public function setXmlConfig ( $value)
    {
        $this->_data ['xmlconfig'] = $value;
    }
    
    
    /**
     * @return string 
     */
    public function getXmlConfig ()
    {
        return $this->_data ['xmlconfig'];
    }
    
    
    // -----------------------------------------------------------------
    
    
    /**
     * 
     */
    public function getFieldType ( $predicate, $t )
    {
        $t = (string) $t;
        
        if (true == isset ( $t ) AND '' != $t )
        {
            return $t;
        }
        
        else
        {
            /*
            // Get range infos for predicate
            $range = config::get ( 'selectedModel' )->sparqlQuery(
                'SELECT ?object 
                  WHERE {
                     <' . $predicate . '> <http://www.w3.org/2000/01/rdf-schema#range> ?object.
                  }'
            );*/
            
            $type = 'xsd:string';
            
            /*
            // If a range was defined
            if ( 0 < count($range) AND true === isset ( $range[0]['object'] ) )
                $type = substr ( 
                    $range[0]['object'],
                    1+strrpos ( $range[0]['object'], '/' ) 
                );
            */
                
            return $type;
        }
    }
    
    
    /**
     * 
     */
    public function addLabelpart ( $value )
    {
        $this->_data ['labelparts'] [$value] = $value;
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
    public function removeLabelpart ( $value )
    {
        unset ( $this->_data ['labelparts'] [$value] );
    }
    
    
    /**
     * @return void
     */
    public function addSection ( $value )
    {
        $this->_data ['sections'] [] = $value;
    }
    
    
    /**
     * @return void
     */
    public function removeSection ( $value )
    {
        unset ( $this->_data ['sections'] [$value] );
    }
    
    
    /**
     * @return array
     */
    public function getSections ()
    {
        return $this->_data ['sections'];
    }
    
    
    /**
     * @return string
     */
    public function toString ()
    {        
        $return = '<br/>- title: '. $this->getTitle () .
                '<br/>- index: '. $this->getIndex () .
                '<br/>- description: '. $this->getDescription () .
                '<br/>- label parts: '. implode ( ', ', $this->getLabelparts () ) .
                '<br/>- mode: '. $this->getMode () .
                '<br/>- resources: '. implode ( ', ', $this->getResources () ) .
                '<br/>- target class: '. $this->getTargetClass () .
                '<br/>- XML config: '. $this->getXmlConfig () .
                '<br/>- sections: ';
          
        foreach ( $this->getSections () as $section )
            foreach ( $section as $s )
                if ( 'predicate' == $s ['sectiontype'] )
                {
                    $return .= '<br/>&nbsp;&nbsp;+ predicate ';
                    $return .= '<br/>&nbsp;&nbsp;&nbsp; - index: '. $s ['index'];
                    $return .= '<br/>&nbsp;&nbsp;&nbsp; - title: '. $s ['title'];
                    $return .= '<br/>&nbsp;&nbsp;&nbsp; - name: '. $s ['name'];
                    $return .= '<br/>&nbsp;&nbsp;&nbsp; - predicateuri: '. $s ['predicateuri'];
                }
                elseif ( 'nestedconfig' == $s ['sectiontype'] )
                {
                    $return .= '<br/>&nbsp;&nbsp;+ nestedconfig ';
                    $return .= '<br/>';
                    $return .= $s ['form']->toString ();
                }
        /*
        echo '<pre>';
        var_dump ( $this->getSections () );
        echo '</pre>';
        */
        return $return;
    }
}
