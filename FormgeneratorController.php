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
        require realpath(dirname(__FILE__)) . '/../resourcecreationuri/ResourcecreationuriPlugin.php';

        
        // Get model
        $m = $this->_owApp->selectedModel;
        $mUrl = (string) $this->_owApp->selectedModel;


		$exampleForm = new Form ( $m );
        
        
// ####### Test URI generation

        echo '<br />---------------------------------------->';
        echo $exampleForm->generateUniqueUri ( "http://als.dispedia.info/architecture/c/20110504/Patient", "KarlHeinz" );



		// Load XML Config
		$exampleForm->loadConfig ( realpath(dirname(__FILE__)) . '/formconfigs/createPatient_own.xml' );

		echo "<h2>". $exampleForm -> headline ."</h2>";

		echo "<b>". $exampleForm -> introduceText .'</b><br><br><div align="center">';
        
               // TODO: $this->model->getStore()->addStatement ()    siehe WIKI > Erfurt > Working with Erfurt
        
        // addstatement
        echo 'example addstatement:<br />';
        echo $exampleForm->generateUniqueUri ( "http://als.dispedia.info/architecture/c/20110504/Patient", "KarlHeinz" ) . ' a ' . $exampleForm->sections[0] ['fields'][0]['target'];
        //$m->getStore()->addStatement( (string) $m, 
        //                                        $instance,
        //                                       'a', 
        //                                        array ( 'value' => $exampleForm->sections[0] ['fields'][0]['target'], 'type' => 'uri' ));

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
						
						echo '<td bgcolor="#FFFFCC" width="45%">'. $field ['caption'];
						
						if ( 1 == $field ['mandatory'] )
							echo ' *';
						
						echo '<br />';
						echo '(target: ' . $field ['target'] . ')';
						echo '<br />';
                        echo '(xml-type: ' . $field ['type'] . ')';
						echo '<br />';
                        echo '(range-type: ' . $field ['range'] . ')';
						echo '<br />';
                        
						echo '<br />';
						echo '</td>';
						echo '<td bgcolor="#FFFFCC"><input type="text" id="datepicker"></td>';
					
						break;
						
					case 'gender':
									
						echo '<td bgcolor="#FFFFCC" width="45%">'. $field ['caption'];
						
						if ( 1 == $field ['mandatory'] )
							echo ' *';
						
						echo '<br />';
						echo '(target: ' . $field ['target'] . ')';
						echo '<br />';
                        echo '(xml-type: ' . $field ['type'] . ')';
						echo '<br />';
                        echo '(range-type: ' . $field ['range'] . ')';
						echo '<br />';
                        
						echo '<br />';
						echo '</td>';
						echo '<td bgcolor="#FFFFCC"><select><option>- bitte w&auml;hlen -</option><option>m&auml;nnlich</option><option>weiblich</option></select></td>';
					
						break;
						
					default:
						echo '<td bgcolor="#FFFFCC" width="45%">'. $field ['caption'];
                        
						if ( 1 == $field ['mandatory'] )
							echo ' *';
                        
						echo '<br />';
						echo '(target: ' . $field ['target'] . ')';
						echo '<br />';
                        echo '(xml-type: ' . $field ['type'] . ')';
						echo '<br />';
                        echo '(range-type: ' . $field ['range'] . ')';
						echo '<br />';
                        
						echo '<br />';
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

