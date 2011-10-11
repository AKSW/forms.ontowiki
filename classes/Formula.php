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
        $this->_data ['mode'] = 'new';
        $this->_data ['resources'] = array ();
        $this->_data ['sections'] = array ();
    }
    
    
    // -----------------------------------------------------------------
    
    
    /**
     * @return void 
     */
    public function setDescription ( $value )
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
    public function setMode ( $value )
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
     * @param $value resource
     * @return void 
     */
    public function addResource ( $value )
    {
        $this->_data ['resources'] [] = $value;
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
    public function setTitle ( $value )
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
    public function setxmlfile ( $value )
    {
        $this->_data ['xmlfile'] = $value;
    }
    
    
    /**
     * @return string 
     */
    public function getxmlfile ()
    {
        return $this->_data ['xmlfile'];
    }
    
    
    // -----------------------------------------------------------------
    
    
    /**
     * Extract field type.
     * @return string
     */
    public static function getFieldType ( $predicate, $t )
    {
        $t = (string) $t;
        
        if (true == isset ( $t ) AND '' != $t )
        {
            return $t;
        }
        
        else
        {
            // TODO determine field type by predicate url
            
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
    public function removeLabelpart ( $value )
    {
        unset ( $this->_data ['labelparts'] [$value] );
    }
    
    
    /**
     * @param $value Array of URIs
     * @return void 
     */
    public function setLabelparts ( $value )
    {
        $this->_data ['labelparts'] = $value;
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
     * 
     */
    public function getData ()
    {
        return $this->_data;
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
                '<br/>- XML config: '. $this->getxmlfile () .
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
                
        return $return;
    }
    
    
    /**
     * @return array
     */
    public function getDataAsArrays ( )
    {
        $arr = array (
            'title'         => $this->getTitle (),
            'index'         => $this->getIndex (),
            'description'   => $this->getDescription (),
            'labelparts'    => $this->getLabelparts (),
            'mode'          => $this->getMode (),
            'resources'     => $this->getResources (),
            'targetclass'   => $this->getTargetClass (),
            'xmlfile'       => $this->getxmlfile (),
            'sections'      => array ()
        );
                  
        
        foreach ( $this->getSections () as $entry )
        {
            $newSection = array ();
            $newSection ['title'] = $entry ['title'];
            
            for ( $i = 0; $i < (count ( $entry )-1); ++$i )
            {
                $s = $entry [$i];
                
                if ( 'predicate' == $s ['sectiontype'] )
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
                elseif ( 'nestedconfig' == $s ['sectiontype'] )
                {
                    $newSection [] = array (
                        'sectiontype'   => $s ['sectiontype'],
                        'relations'     => $s ['relations'],
                        'form'          => $s ['form']->getDataAsArrays ()
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
    public static function initByArray ( $formArray )
    {
        // init a new Formula instance
        $form = new Formula ( 0 );

        $form->setTitle ( $formArray ['title'] );
        
        $form->setDescription ( $formArray ['description'] );
        
        $form->setLabelparts ( $formArray ['labelparts'] );
        
        $form->setMode ( $formArray ['mode'] );
        
        $form->setResources ( $formArray ['resources'] );
        
        $form->setTargetClass ( $formArray ['targetclass'] );
        
        $form->setxmlfile ( $formArray ['xmlfile'] );
        
        foreach ( $formArray ['sections'] as $entry )
        {
            $newSection = array ( 'title' => $entry ['title'] );
            
            foreach ( $entry as $section )
            {
                if ( 'predicate' == $section ['sectiontype'] )
                {
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
                elseif ( 'nestedconfig' == $section ['sectiontype'] )
                {
                    $newSection [] = array ( 
                        'xmlfile'      => $section ['form']['xmlfile'],
                        'index'        => $section ['form']['index'],
                        'relations'    => $section ['relations'],
                        'form'         => Formula::initByArray ( $section ['form'] ), 
                        'sectiontype'  => 'nestedconfig'
                    );
                }    
            }
            
            $form->addSection ( $newSection );
        }
        
        return $form;
    }
    
    
    /** 
     * Check a formula
     * @return boolean 
     */
    public static function isValid ( $f )
    {
        if ( 'new' == $f->getMode () )
        {
            
        }
        
        elseif ( 'add' == $f->getMode () )
        {
            foreach ( $f->getSections () as $sectionEntries )
            {
                // extract title from array and delete it
                // so there only predicate and nestedconfig elements in it
                $title = array_shift( $sectionEntries );
                
                foreach ( $sectionEntries as $entry )
                {
                    if ( 'predicate' == $entry ['sectiontype'] )
                    {
                        // check mandatory field value
                        if ( 'mandatory' == $entry ['mandatory'] )
                        {
                            $entry ['value'] = trim ( $entry ['value'] );
                        }
                        
                    }
                } 
            }
        }
        
        // TODO implement Formula::isValid
        return true;
    }
}
