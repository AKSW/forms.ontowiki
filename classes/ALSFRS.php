<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_formgenerator
 * @author     Lars Eidam <larseidam@googlemail.com>
 * @author     Konrad Abicht <konrad@inspirito.de>
 * @copyright  Copyright(c) 2011
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License(GPL)
 */
class ALSFRS
{
    protected $_selectedModel;
    
    public function __construct ( $selectedModel )
    {
        $this->_selectedModel = $selectedModel;
    }
    
    /**
     * 
     */
    public function getSimpleTopicList ( $currentLanguage = 'de' )
    {
        // TODO add language selection
        $topics = $this->_selectedModel->sparqlQuery(
            'SELECT ?topicUri ?label ?suggestedQuestion
             WHERE {
                 ?topicUri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://als.dispedia.info/frs/o/Topic> .
                 ?topicUri <http://www.w3.org/2000/01/rdf-schema#label> ?label .
                 ?topicUri <http://als.dispedia.info/frs/o/suggestedQuestion> ?suggestedQuestion .
             };'
        );
        
        $topicList = array ();
        $currentTopicUri = '';
        
        foreach ( $topics as $topic ) 
        {
            if ( $topic['topicUri'] == $currentTopicUri ) 
                continue;
            
            $currentTopicUri = $topic['topicUri'];
            $currentLabel = $topic['label'];
            $currentSuggestedQuestion = $topic['suggestedQuestion'];
             
            $topicList[] = array ( 
                'topicUri' => $currentTopicUri,
                'label' => $currentLabel,
                'suggestedQuestion' => $currentSuggestedQuestion
            );
        }         
        return $topicList;
    }
    
    
    /**
     * 
     */
    public function getTopicsWithOptions ( $topics, $currentLanguage = 'de' )
    {
        // TODO add language selection
        
        $result = array ();
        
        foreach ( $topics as $topic ) 
        {
            $options = $this->_selectedModel->sparqlQuery(
                'SELECT ?optionUri
                 WHERE {
                     <'. $topic ['topicUri'] .'> <http://als.dispedia.info/frs/o/hasOption> ?optionUri .
                 }
                 ORDER BY DESC(?optionUri);'
            );
            
            foreach ( $options as $o ) 
            {                
                $option = $this->_selectedModel->sparqlQuery(
                    'SELECT ?optionUri ?label ?score
                     WHERE {
                         ?optionUri <http://www.w3.org/2000/01/rdf-schema#label> ?label .
                         <'. $o ['optionUri'] .'> <http://www.w3.org/2000/01/rdf-schema#label> ?label .
                         <'. $o ['optionUri'] .'> <http://als.dispedia.info/frs/o/hasScore> ?score .
                     };'
                );
                            
                $topic ['options'] [] = $option [1];
            }
            
            $result [] = $topic;
        } 
        
        return $result;
    }
}
