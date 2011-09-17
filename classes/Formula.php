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
    public function __construct()
    {
        $_data = array ();
    }
    
    
    /**
     * 
     */
    public function setDescription ( $v )
    {
        $this->_data ['description'] = $v;
    }
    
    
    /**
     * 
     */
    public function getDescription ()
    {
        return $this->_data ['description'];
    }
    
    
    /**
     * @param $label RDF-S label of target class
     * @param $uri URI of target class
     * @return void 
     */
    public function setTargetClass ( $label, $uri )
    {
        $this->_data ['targetclass'] = array ( 
            'label' => $label,
            'uri' => $uri
        );
    }
    
    
    /**
     * @return array 
     */
    public function getTargetClass ()
    {
        return $this->_data ['targetclass'];
    }
    
    
    /**
     * 
     */
    public function setTitle ( $v )
    {
        $this->_data ['title'] = $v;
    }
    
    
    /**
     * 
     */
    public function getTitle ()
    {
        return $this->_data ['title'];
    }
}
