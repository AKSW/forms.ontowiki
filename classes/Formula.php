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
     * 
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
    public function addLabelpart ( $value )
    {
        $this->_data ['labelparts'] [$value] = $value;
    }
    
    
    /**
     * 
     */
    public function getLabelparts ( $value )
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
    public function addSection ()
    {
        
    }
    
    
    /**
     * @return void
     */
    public function removeSection ()
    {
        
    }
    
    /**
     * @return string
     */
    public function toString ()
    {
        return 'Form '. $this->getTitle () .' with index '. $this->getIndex () .'<br/>'.
                $this->getDescription () .
                '<br/>- label parts: '. implode ( ', ', $this->getLabelparts () ) .
                '<br/>- mode: '. $this->getMode () .
                '<br/>- resources: '. implode ( ', ', $this->getResources () ) .
                '<br/>- target class: '. $this->getTargetClass () .
                '<br/>- XML config: '. $this->getXmlConfig () ;
    }
}
