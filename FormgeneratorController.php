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
        require realpath(dirname(__FILE__)) . '/../resourcecreationuri/classes/ResourceUriGenerator.php';
        
        $foo = new ResourceUriGenerator($this->_owApp->selectedModel, null, $this->_owApp);
        var_dump( $foo->generateUri('http://als.dispedia.info/pdExample/i/20110325/'));
        
        // Get model
        $m = $this->_owApp->selectedModel;
        $mUrl = (string) $this->_owApp->selectedModel;

		$exampleForm = new Form ( $m );

		// Load XML Config
		$exampleForm->loadConfig ( realpath(dirname(__FILE__)) . '/formconfigs/createPatient_own.xml' );

		echo "<h2>". $exampleForm -> headline ."</h2>";

		echo "<b>". $exampleForm -> introduceText .'</b><br><br><div align="center">';
        
               // TODO: $this->model->getStore()->addStatement ()    siehe WIKI > Erfurt > Working with Erfurt
        
        $name = 'Meyer';
        $firstName = 'Hans';
        $instanz = $exampleForm->sections[0] ['fields'][0]['target']
                 . '/' . $name . '/' . $firstName
                 . '/' . md5($name . $firstName);
        
        echo $instanz;
        $m->getStore()->addStatement( (string) $m, 
                                                $instanz,
                                                'a', 
                                                array ( 'value' => $exampleForm->sections[0] ['fields'][0]['target'], 'type' => 'uri' ));

		foreach ( $exampleForm -> sections as $section )
		{
			echo 
			'<table border="0" cellspacing="0" cellpadding="0" width="30%">
				<tr>
					<td bgcolor="#660000">
						<table border="0" cellspacing="1" cellpadding="4" width="100%">
							<tr>
								<td bgcolor="#FFFFCC" colspan="2">';
			
			echo '<h3>'. $section ['caption'] .'</h3>';
			
			echo 			   '</td>
							</tr>';
				
			foreach ( $section ['fields'] as $field )
			{
				echo '<tr>';
				
				switch ( $field ['type'] )
				{
					case 'date':
						
						echo '<script>
								$(function() {
									$( "#datepicker" ).datepicker();
								});
							  </script>';
						
						echo '<td bgcolor="#FFFFCC" width="45%">'. $field ['caption']. '(' . $field ['target'] . ')';
						
						if ( 1 == $field ['mandatory'] )
							echo ' *';
						
						echo '</td>';
						echo '<td bgcolor="#FFFFCC"><input type="text" id="datepicker"></td>';
					
						break;
						
					case 'gender':
									
						echo '<td bgcolor="#FFFFCC" width="45%">'. $field ['caption'] . '(' . $field ['target'] . ')';
						
						if ( 1 == $field ['mandatory'] )
							echo ' *';
						
						echo '</td>';
						echo '<td bgcolor="#FFFFCC"><select><option>- bitte w&auml;hlen -</option><option>m&auml;nnlich</option><option>weiblich</option></select></td>';
					
						break;
						
					default:
						echo '<td bgcolor="#FFFFCC" width="45%">'. $field ['caption'] . '(' . $field ['target'] . ')';
						
						if ( 1 == $field ['mandatory'] )
							echo ' *';
						
						echo '</td>';
						echo '<td bgcolor="#FFFFCC"><input type="text" name="foobar" /></td>';
						break;
				}
				
				echo '</tr>';
			}
			
			echo				'</td>
							</tr>
						</table>
					</td>
				</tr>
			</table><br>';
		}

		echo "</div></body></html>";
    }
}

