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
function setFormulaArrayFields (f)
{        
    var len = f.sections.length;
    var i, j = 0;
    
    for (i = 0; i < len; ++i)
    {       
        for (j = 0;;++j)
        {
            if (undefined == f.sections [i][j]) 
                break;
         
            else if ("predicate" == f.sections [i][j].sectiontype)
            {
                f.sections [i][j].value = $("#" + f.sections [i][j].name).val();
            }
            
            // recursive call of this function 
            else if ("nestedconfig" == f.sections [i][j].sectiontype)
            {
                setFormulaArrayFields (f.sections [i][j].form);
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
function setFormulaModeTo (f, newMode)
{
    f.mode = newMode;
    var len = f.sections.length;
    var i, j = 0;
    
    for (i = 0; i < len; ++i)
    {       
        for (j = 0;;++j)
        {
            if (undefined == f.sections [i][j]) 
                break;
         
            // recursive call of this function 
            else if ("nestedconfig" == f.sections [i][j].sectiontype)
            {
                f.sections [i][j].form = setFormulaModeTo (
                    f.sections [i][j].form, 
                    newMode 
               );
            }
        }
    }
    
    return f;
} 

   
/**
 * sends a complete json-serialzed formula instance and add/edit resources
 * @param url target URL
 * @param formData a json-serialized formula instance
 */
function submitFormula (url, data, mode) 
{   
    // show please wait box
    $("#pleaseWaitBox").show ();
    
    var form = $.data(data, "form");
    
    if (undefined == $.data(data, "formOld"))
    {
        var formOld = $.extend(true, {}, $.data(data, "form"));
    }
    else
    {
        formOld = $.data(data, "formOld");
    }
        
    console.log ("form");
    console.log (form);
    console.log ("");
    
    console.log ("formOld");
    console.log (formOld);
    console.log ("");
    
    // set values from formula into the formula instance 
    // which was loaded at the beginning
    form = setFormulaArrayFields (form);
    
    // set mode from new to add
    form = setFormulaModeTo (form, mode);
    // formOld = setFormulaModeTo (formOld, mode);

    var _data = data;
    var jQ = jQuery;
        
    // send formulas to submit action on server
    $.ajax({
        async:true,
        data: "form=" + $.toJSON(form) + "&formOld=" + $.toJSON(formOld),
        dataType: "json",
        type: "POST",
        url: url + "submit/",
    
        // complete, no errors
        success: function ( res ) 
        {
            // res = jQ.parseJSON (res);
            
            console.log ("response");
            console.log ( res );
    
            // replace formOld with form
            jQ.data(_data, "formOld", $.extend(true, {}, form));
            
            // replace form with form instance from response
            jQ.data(_data, "form", res.form);
            
            $("#pleaseWaitBox").hide ();
            
            // show edit button
            $("#changeResource").show();
        },
        
        error: function (jqXHR, textStatus, errorThrown)
        {
            console.log (jqXHR);
            console.log (textStatus);
            console.log (errorThrown);
        },
        
        complete: function ()
        {
            console.log ( "complete" );
        }
    });
}
