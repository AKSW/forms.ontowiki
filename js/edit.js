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
* 
*/
function addEntity (entity, entityOverClass, context, resource, name)
{
    var returnValue = 0;
    var action = 'newform';
    
    if ('' != resource)
    {
        action = 'form';
    };   
    $.ajax({
        async:false,
        dataType: "html",
        type: "GET",
        data: {
            layout : 'box',
            r : resource,
            file : entity
        },
        context: $(context),
        url: url + "formgenerator/" + action,
            // complete, no errors
        success: function ( res ) 
        {
            if ('noformularfound' == res)
            {
                alert ('No Formular found');
                returnValue = -1;
            }
            else
            {
                $(this).append(res);
                updateElements = new Array();
                if ('' != name)
                {
                    updateElements[0]  = '<div class=\"divClassPredicateValue\">\n';
                    updateElements[0] += '<div class=\"divClassPredicateValueInput\">';
                    updateElements[0] += '<input type=\"checkbox\" class=\"predicateValue_Class\" name=\"' + name + '\" value=\"';
                    updateElements[1]  = '\" checked="checked">';
                    updateElements[1] += '</div>';
                    updateElements[2]  = "<a href=\"javascript:openBoxForm('#boxes', 'service', ";
                    updateElements[3]  = "')\"><img src=\"http://localhost/ow_als/extensions/themes/dispedia/images/icon-edit.png\"></a>";
                    updateElements[3] += '<div class=\"clear\"></div>'
                    updateElements[3] += '</div>';
                }
                returnValue = 1;
            }
        },
        
        error: function (jqXHR, textStatus, errorThrown)
        {
            console.log (jqXHR);
            console.log (textStatus);
            console.log (errorThrown);
            returnValue = -1;
        }
    });
    return returnValue;
}

/**
* 
*/
function removeEntity (entityHash)
{
    console.log ("remove " + entityHash);
    $("." + entityHash).remove();
}