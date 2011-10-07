/**
 * Javascript stuff for form action
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright (c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */


/**
 * extracts values of formula fields and save them in jsonForm
 * @param f json-serialized formula instance
 * @return array modified formula instance
 */ 
function setFormulaArrayFields ( f )
{    
    var sectionElement = null;
    var j = 0;
    
    // for ( var i=0; i < f.sections.length; ++i )
    for ( i in f.sections )
    {
        sectionElement = f.sections [i];
        
        console.log ( sectionElement );
        
        while ( true ) 
        {
            // if ( null == sectionElement [j] )
                break;
                
            console.log ( sectionElement [j] );
                
            ++j;
        }
        
        j = 0;
        
        /*for ( entry in f.sections [i] )
        {
            // if ( false == isString ( f.sections [i] [entry] ) )
            // if ( "NaN" != parseInt ( entry, "10" ) )
            // console.log ( f.sections [i] [entry] );
        }*/
    }
    
    return f;
}

 
/**
 * sends a complete json-serialzed formula instance and add/edit resources
 */
function submitFormula ( xml, url, formData ) 
{
    formData = setFormulaArrayFields ( formData );
}
