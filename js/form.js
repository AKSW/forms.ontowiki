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
    for ( i in f.sections )
    {
        if ( "predicate" == f.sections [i].sectiontype )
        {
            f.sections [i].value = $( "#" + f.sections [i].name ).val();
        }
        
        // recursive call of this function 
        else if ( "nestedconfig" == f.sections [i].sectiontype )
        {
            setFormulaArrayFields ( f.sections [i].form );
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
