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

var boxopen = false;
var boxnumber = 0;
var boxdata = new Array();
var tempboxdata =  {};

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
                else if ( "class" == f.sections[i][j].type || "multiple" == f.sections[i][j].type )
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
                else if (1 < $("input[name=" + f.sections [i][j].name + "]").length)
                {
                    values = new Array();
                    $("input[name=" + f.sections [i][j].name + "]").each(function(index) {
                        values[index] = $(this).val();
                    });

                    f.sections [i][j].value = values;
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
    if ((typeof data === 'object' && data && data instanceof Array))
    {
        data = boxdata[boxnumber-1];
    }

    var reload = true;
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
            
            if ('undefined' != typeof res.newresources && boxopen)
            {
                var tpl = jsontemplate.Template($('#' + res.newresources['className'] + '-template').html());
                $('#' + res.newresources['className']).append(tpl.expand(res.newresources));
            }
            
            if (boxopen)
            {
                reload = false;
                // close box view if submit complete
                closeBoxForm();
            }
            
            
            $("#pleaseWaitBox").hide ();
            
            // show edit button
            $("#changeResource").show();
            
            if (reload)
            {
                if ('edit' == form.mode)
                    location.reload();
                else
                    location = url + 'form/?r=' + encodeURI(res.newresources['resourceUri']);
            }
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

function openBoxForm(form, resource) {
    
    boxopen = true;
    boxnumber++;
    
    $('#boxes').append('<div id="box' + boxnumber + '"></div>');
    // load the form from server
    if (1 == addEntity (form, '#box' + boxnumber, resource))
    {
        
        if (1 == boxnumber){
            // because firefox is slow
            $('div.section-mainwindows').css('opacity', 'inherit');
        }
        else
        {
            $.modal.close();
            $('#box' + (boxnumber - 1)).hide();
        }
        showModal();
    }
    boxdata[boxnumber - 1] = tempboxdata;
}
function showModal()
{
    $('#boxes').modal({
        minWidth:750,
        persist : true,
        appendTo : '.active-tab-content',
        animation : 'fade'
    });
}

/**
 * close boxform
 */
function closeBoxForm() 
{
    $('#box' + boxnumber).empty();
    boxnumber--;
    $.modal.close();
    if (0 != boxnumber)
    {
        $('#box' + boxnumber).show();
        showModal();
    }
    
    
    if (0 == boxnumber)
        boxopen = false;
}

/**
 * put an element up
 */
function elementUp(element)
{
    $('#' + element).insertBefore($('#' + element).prev());
}

/**
 * put an element down
 */
function elementDown(element)
{
    $('#' + element).insertAfter($('#' + element).next());
}

/**
 * element add a element
 */
function addElement(selectBoxId, elementContainer)
{
    var option = $('#' + selectBoxId + ' option:selected');
    if ("" == option.val())
        openBoxForm('#boxes', elementContainer, '');
    else{
        var elementData = new Array();
        elementData['resourceUri'] = option.val();
        elementData['label'] = option.text();
        elementData['checked'] = 'checked';
        elementData['md5'] = option.attr('id');
        
        var tpl = jsontemplate.Template($('#' + elementContainer + '-template').html());
        $('#' + elementContainer).append(tpl.expand(elementData));
        
        option.remove();
    }
}

/**
 * remove an element
 */
function removeElement(element)
{
    $('#' + element).remove();
}