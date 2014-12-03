<?php

class IndexController {

	public $sCurrentTheme;
	public $sCurrentTemplate;
	public $aCustomTwigConfig = array();
	public $aCustomTwigVars = array();
	public $aJs = array();
	public $aCss = array();
	
	private $sContent;

	public function __construct(){	    
		// Initialize assets
		$this->aCss = array(
		    \RubenVerweij\Config::$crispus['urls']['themes'] . 
		    '/' . \RubenVerweij\Config::$site['theme'] .
		    '/css/base.css'
		);
	}
	
	public function processPage($sUrl, $sContent){
		// Process Markdown
		$this->sContent = $this->processMarkdown($sContent);
	
		return $this->sContent;
	}
	
	private function processMarkdown($sContent){
		return \Michelf\MarkdownExtra::defaultTransform($sContent);
	}

}
