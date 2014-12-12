<?php

class IndexController {

	public $sCurrentTheme;
	public $sCurrentTemplate;
	public $aCustomTwigConfig = array();
	public $aCustomTwigVars = array();
	public $aJs = array();
	public $aCss = array();
	public $aHeaders = array();
	
	private $sContent;

	public function __construct(){	    
	}
	
	public function processPage($sUrl, $sContent){
		// Get custom headers from top comment
		$this->aHeaders = $this->processHeaders($sContent);
		
		// Process Markdown
		$this->sContent = $this->processMarkdown($sContent);
	
		return $this->sContent;
	}
	
	private function processMarkdown($sContent){
	    // Remove comments/meta
	    $sContent = preg_replace('#/\*.+?\*/#s', '', $sContent);
	    
		return \Michelf\MarkdownExtra::defaultTransform($sContent);
	}
	
	private function processHeaders($sContent){
	    // Match the first block comment
	    $aMatches = array();
	    preg_match_all("#\/\*" . "((?:(?!\*\/).)*)" . "\*\/#s", $sContent, $aMatches);
	    
	    if(isset($aMatches[1][0])){
	    
	        $sMatch = $aMatches[1][0];
	        
	        // Match "Key" : "Value of key"	        
	        preg_match_all('#["\']((?:(?!"\').)*)[\'"]\s*\:\s*["\']((?:(?!"\').)*)[\'"]#', $sMatch, $aMatches);
	        
	        if(isset($aMatches[1])){
	            // These are the keys
	            foreach($aMatches[1] as $iIndex => $sKey){
	                if(isset($aMatches[2][$iIndex])){
	                    // Set the values
	                    $this->aCustomTwigVars[strtolower($sKey)] = $aMatches[2][$iIndex];
	                }
	            }
	        }
	    
	    }
	}

}
