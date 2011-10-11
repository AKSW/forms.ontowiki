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
    var len = f.sections.length;
    var i, j = 0;
    
    for ( i = 0; i < len; ++i )
    {       
        for ( j = 0;;++j )
        {
            if ( undefined == f.sections [i][j] ) 
                break;
         
            else if ( "predicate" == f.sections [i][j].sectiontype )
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
 * sets formula mode for f and all sub formulas
 * @param f formula instance
 * @param newMode new formula mode
 * @return void
 */
function setFormulaModeTo ( f, newMode )
{
    f.mode = newMode;
    var len = f.sections.length;
    var i, j = 0;
    
    for ( i = 0; i < len; ++i )
    {       
        for ( j = 0;;++j )
        {
            if ( undefined == f.sections [i][j] ) 
                break;
         
            // recursive call of this function 
            else if ( "nestedconfig" == f.sections [i][j].sectiontype )
            {
                setFormulaModeTo ( f.sections [i][j].form, newMode );
            }
        }
    }
} 

 
/**
 * sends a complete json-serialzed formula instance and add/edit resources
 * @param url target URL
 * @param formData a json-serialized formula instance
 */
function submitFormula ( url, formData ) 
{
    // set values from formula into the formula instance 
    // which was loaded at the beginning
    formData = setFormulaArrayFields ( formData );
    
    // set mode from new to add
    formData = setFormulaModeTo ( formData, "add" );
    
    // send formula to submit action on server
    response =  $.ajax({
        type: "POST",
        url: url + "submit/",
        data: "form=" + $.toJSON( formData ),
        dataType: "json",
        async:false
    }).responseText;
    
    console.log ( "formData" );
    console.log ( $.toJSON( formData ) );
    console.log ( "-------------------" );
    
    response = jQuery.parseJSON ( response );
    console.log ( response );
}
