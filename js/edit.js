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
function addEntity (entity, context, resource)
{
    var returnValue = 0;
    var action = 'newform';
    var data = '';
    
    if ('' != resource)
    {
        action = 'form';
        data = {
            layout : 'box',
            r : resource,
            file : entity
        };
    }
    else
    {
        data = {
            layout : 'box',
            file : entity
        };
    };
    $.ajax({
        async:false,
        dataType: "html",
        type: "GET",
        data: data,
        context: $(context),
        url: urlBase + "formgenerator/" + action,
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