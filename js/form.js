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
 * loads json-serialized formula instance by xml configuration file name
 * @param xml xml configuration file name
 * @param url url of extension
 * @param callback will be called on ajax success
 * @return void
 */ 
function loadFormulaArray ( xml, url, callback )
{
    $.ajax({
        type: "POST",
        
        // target
        url: url + 'echoformarray/',
        
        // payload
        data: "type=json&xml=" + xml,
        
        dataType: "json",
        
        async: true,
        
        cache: false,
        
        // on complete
        success: callback
    });
} 


/**
 * help function for formula interaction
 */
var currentFormula = null; var currentSectionIndex = -1;

function getNextSection ()
{
    function isString(s) {
        return typeof(s)=='string';
    }
    
    ++currentSectionIndex;
    var tmpCounter = 0;
    
    if ( null != currentFormula )
    {
        for ( var i=0; i < currentFormula.sections.length; ++i )
        {
            for ( entry in currentFormula.sections [i] )
            {
                if ( currentSectionIndex == tmpCounter && 
                     false == isString ( currentFormula.sections [i] [entry] ) )
                    return currentFormula.sections [i] [entry];
                
                ++tmpCounter;
            }
        }
    }
    
    return null;
}


/**
 * help function for formula interaction
 */
function resetNextSection ()
{
    currentSectionIndex = -1;
    currentFormula = null;
}


/**
 * extracts values of formula fields and save them in jsonForm
 * @param f json-serialized formula instance
 * @return array modified formula instance
 */ 
function setFormulaArrayFields ( f )
{    
    currentFormula = f;
    var section;
    
    while ( true )
    {
        section = getNextSection ();
        
        console.log ( section );
        
        // stop loop iff no new section exists
        if ( null == section ) 
            break;
    }
    
    return f;
}

 
/**
 * sends a complete json-serialzed formula instance and add/edit resources
 */
function submitFormula ( xml, url ) 
{
    // load current formula
    loadFormulaArray ( 
        xml, url, 
                
        // on complete (jsonForm = complete json-serialized formula instance )
        function ( jsonForm ) {
            
            jsonForm = setFormulaArrayFields ( jsonForm );
        }
    );
}
