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
 * extracts value of formula fields and saves it in form
 * @param f json-serialized formula instance
 * @return array modified formula instance
 */ 
function setFormulaArrayFields ( f )
{        
    for ( var i = 0; i < f.sections.length; ++i )
    {       
        for ( var j = 0;;++j )
        {
            if ( undefined == f.sections [i][j] ) 
                break;
         
            if ( "predicate" == f.sections [i][j].sectiontype )
            {
                f.sections [i][j].value = $( "#" + f.sections [i][j].name ).val();
            }
            
            // recursive call of this function 
            else if ( "nestedconfig" == f.sections [i][j].sectiontype )
            {
                setFormulaArrayFields ( f.sections [i][j].form );
            }
        }
    }
    
    return f;
}

 
/**
 * sends a complete json-serialzed formula instance and add/edit resources
 */
function submitFormula ( url, formData ) 
{
    // set values from formula into the formula instance 
    // which was loaded at the beginning
    formData = setFormulaArrayFields ( formData );
    
    // send formula to submit action on server
    response = jQuery.parseJSON($.ajax({
        type: "POST",
        url: url + "submit/",
        data: "form=" + $.toJSON( formData ),
        dataType: "json",
        async:false
    }).responseText);
    
    console.log ( response );
}
