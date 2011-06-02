<?php

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_files
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: FilesController.php 4090 2009-08-19 22:10:54Z christian.wuerker $
 */
class FormgeneratorController extends OntoWiki_Controller_Component
{
    protected $_configModel;
    
    /**
     * Default action. Forwards to get action.
     */
    public function __call($action, $params)
    {
        $this->_forward('get', 'files');
    }
    
    public function overviewAction()
    {
	}
	    
    
    public function formAction()
    {
        require 'classes/Form.php';		
        require 'classes/Tools.php';

        
        // Get model
        $m = $this->_owApp->selectedModel;
        $mUrl = (string) $this->_owApp->selectedModel;


        // Load XML Config
		$exampleForm = new Form ( $m );
        $exampleForm->loadConfig ( realpath(dirname(__FILE__)) . '/formconfigs/patient.xml' );

        
        echo '<pre>';
        var_dump ( $exampleForm );
        echo '</pre>';
        
        
		echo "</div></body></html>";
    }
}

