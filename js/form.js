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
                if ( "alsfrsquestion" == f.sections[i][j].type )
                    f.sections [i][j].value = $("input[name=" + f.sections [i][j].name + "]:checked").val();
                else if ( "class" == f.sections[i][j].type )
                {
                    if (0 < $("input[name=" + f.sections [i][j].name + "]:checked").length)
                    {
                        values = new Array();
                        $("input[name=" + f.sections [i][j].name + "]:checked").each(function(index) {
                            values[index] = $(this).val();
                        });

                        f.sections [i][j].value = values;
                    }
                    else
                        f.sections [i][j].value = "";
                }
                else
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
    
    if (false == checkMandatoryFields (form))
    {
        $("#pleaseWaitBox").hide ();
        return;
    }
    
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
    console.log ("formnew");
    console.log (form);
    console.log ("");
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
            
            if (0 < updateElements.length)
            {
                newElement  = updateElements[0];
                newElement += res.newresources['http://www.serviceOntology.org/Service'];
                newElement += updateElements[1];
                newElement += res.newresources[res.newresources['http://www.serviceOntology.org/Service']];
                newElement += updateElements[2];
                newElement += res.newresources['http://www.serviceOntology.org/Service'];
                newElement += updateElements[3];
                $('#service').append(newElement);
                updateElements = new Array();
            }
            // close box view if submit complete
            closeBoxForm();
            
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

/**
 * checks if mandatory fields are empty
 * @param url target URL
 * @param formData a json-serialized formula instance
 */
function checkMandatoryFields (f) 
{
    var len = f.sections.length;
    var i, j = 0;
    var returnValue = true;
    
    for (i = 0; i < len; ++i)
    {       
        for (j = 0;;++j)
        {
            if (undefined == f.sections [i][j]) 
                break;
            
            
            else if ("predicate" == f.sections [i][j].sectiontype)
            {
                if (1 == f.sections [i][j].mandatory)
                {
                    if ("" == $("#" + f.sections [i][j].name).val())
                    {
                        $("#" + f.sections [i][j].name).addClass("mandatoryFieldEmpty");
                        returnValue &= false;
                    }
                    else {
                        $("#" + f.sections [i][j].name).removeClass("mandatoryFieldEmpty");
                    }
                }
            }
            // recursive call of this function 
            else if ("nestedconfig" == f.sections [i][j].sectiontype)
            {
                returnValue &= checkMandatoryFields (
                    f.sections [i][j].form
               );
            }
        }
    }
    return returnValue;
}

function openBoxForm(id, form, resource, name) {
    
    //Cancel the link behavior
    //e.preventDefault();
 
    // load the form from server
    if (1 == addEntity (form, 'action', id, resource, name))
    {
        //Get the screen height and width
        var maskHeight = $(window).height();
        var maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#mask').fadeIn(1000);   
        $('#mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        var winH = $(window).height();
        var winW = $(window).width();
               
        //Set the popup window to center
        //$(id).css('top',  winH/2-$(id).height()/2);
        
        $(id).css('width', winW * 0.66);
        $('#boxdialog').css('width', winW * 0.66 - 60);
        
        $(id).css('top', '50px');
        $(id).css('left', winW/2-$(id).width()/2);
        $(id).css('height', winH-150);
        $('#boxdialog').css('height', $(id).height() - $('.toolbar').height() - 40);
        
        $('div.section-sidewindows').css('z-index', '-1');
        
        //transition effect
        $(id).fadeIn(2000);
    }
}

/**
 * close boxform
 */
function closeBoxForm() 
{
    $('div.section-sidewindows').css('z-index', 'auto');
    $('#boxes').empty();
    $('#mask, #boxes').hide();
}