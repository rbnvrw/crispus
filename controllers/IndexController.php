<?php

use RubenVerweij\Config;

class IndexController {

	public $sCurrentTheme;
	public $sCurrentTemplate;
	public $aCustomTwigConfig = array();
	public $aCustomTwigVars = array();
	public $sJs;
	public $sCss;
	public $aHeaders = array();
	
	private $sContent;

	public function __construct(){	    
		Config::getInstance();
	}
	
	public function processPage($sUrl, $sContent){
		// Process Markdown
		$this->sContent = $this->processMarkdown($sContent);
		
		// Add excerpt
		$iLength = (isset(Config::$site['excerpt_length'])) ? Config::$site['excerpt_length'] : 150;
		$this->aCustomTwigVars['excerpt'] = $this->excerpt(strip_tags($this->sContent), $iLength);
		
		// Get custom headers from top comment, from unprocessed content
		$this->aHeaders = $this->processHeaders($sContent);
		
		$this->setCssJsFromHeaders();
		
		// Set template
		if(isset($this->aCustomTwigVars['template'])){
			$this->sTemplate = $this->aCustomTwigVars['template'];
		}
		
		// Process Markdown
		$this->sContent = $this->processMarkdown($sContent);
	
		return $this->sContent;
	}
	
	public function getHeaders($sUrl, $sContent){
		if(empty($this->aCustomTwigVars)){
			$this->processPage($sUrl, $sContent);
			return $this->aCustomTwigVars;
		}else{
			return $this->aCustomTwigVars;
		}
	}
	
	private function processMarkdown($sContent){
	    // Remove comments/meta
	    $sContent = preg_replace('#/\*.+?\*/#s', '', $sContent);
	    // Base URL
	    Config::getInstance();
	    $sContent = str_replace('%base_url%', Config::$root_url, $sContent);
	    
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
	                    $this->aCustomTwigVars[strtolower($sKey)] = $this->convertHeaderValue($aMatches[2][$iIndex]);
	                }
	            }
	        }
	    
	    }
	}
	
	private function setCssJsFromHeaders(){
	    Config::getInstance();
	    
	    $sCssUrl = Config::$crispus['urls']['themes'].'/'.Config::$site['theme'].'/'
	                        .Config::$site['css_theme_folder'].'/';
	    $sJsUrl = Config::$crispus['urls']['themes'].'/'.Config::$site['theme'].'/'
	                        .Config::$site['js_theme_folder'].'/';
	
	    if(isset($this->aCustomTwigVars['css'])){
	        $aCss = explode(',', $this->aCustomTwigVars['css']);
	        $sCss = '';
	        foreach($aCss as $sSheet){
	            $sCss .= $sCssUrl . trim($sSheet) . ',';
	        }
	        $this->sCss = rtrim($sCss, ', ');
	        unset($this->aCustomTwigVars['css']);
	    }
	    
	    if(isset($this->aCustomTwigVars['js'])){
	        $aJs = explode(',', $this->aCustomTwigVars['js']);
	        $sJs = '';
	        foreach($aJs as $sSheet){
	            $sJs .= $sJsUrl . trim($sSheet) . ',';
	        }
	        $this->sJs = rtrim($sJs, ', ');
	        unset($this->aCustomTwigVars['js']);
	    }
	}
	
	private function convertHeaderValue($sValue){
		if(strtolower($sValue) == "true"){
			// Boolean true
			$sValue = true;
		}elseif(strtolower($sValue) == "false"){
			// Boolean false
			$sValue = false;
		}elseif(is_numeric($sValue)){
			// Numeric
			$sValue = (float) $sValue;
		}
		
		return $sValue;
	}
	
	/**
	 * Helper function to limit the words in a string
	 *
	 * @param string $string the given string
	 * @param int $word_limit the number of words to limit to
	 * @return string the limited string
	 */ 
	private function excerpt($sString, $iLimit)
	{
		$aWords = explode(' ', $sString);
		$sExcerpt = trim(implode(' ', array_splice($aWords, 0, $iLimit)));
		if(count($aWords) > $iLimit) $sExcerpt .= '&hellip;';
		return $sExcerpt;
	}
}
