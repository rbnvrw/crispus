<?php

class IndexController {

	public $sCurrentTheme;
	public $sCurrentTemplate;
	public $aCustomTwigConfig = array();
	public $aCustomTwigVars = array();
	
	private $sContent;

	public function __construct() {
	
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
