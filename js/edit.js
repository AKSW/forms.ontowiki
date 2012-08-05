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
function addEntity (entity, entityOverClass, context, resource)
{
    var return_value = 0;
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
                return_value = -1;
            }
            else
            {
                $(this).append(res);
                return_value = 1;
            }
        },
        
        error: function (jqXHR, textStatus, errorThrown)
        {
            console.log (jqXHR);
            console.log (textStatus);
            console.log (errorThrown);
            return_value = -1;
        }
    });
    return return_value;
}

/**
* 
*/
function removeEntity (entityHash)
{
    console.log ("remove " + entityHash);
    $("." + entityHash).remove();
}