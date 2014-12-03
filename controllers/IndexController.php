<?php

class IndexController {

	public $sCurrentTheme;
	public $sCurrentTemplate;
	public $aCustomTwigConfig = array();
	public $aCustomTwigVars = array();
	
	private $sContent;
	private $oJs;
	private $oCss;

	public function __construct() {
		// Initialize assets
		$oJs = new \Assetic\Asset\AssetCollection();
		$oCss = new \Assetic\Asset\AssetCollection();
	}
	
	public function processPage($sUrl, $sContent){
		// Process Markdown
		$this->sContent = $this->processMarkdown($sContent);
	
		return $this->sContent;
	}
	
	public function getJavascriptAssets() {
		return $this->oJs;
	}
	
	public function getCSSAssets() {
		return $this->oCss;
	}
	
	private function processMarkdown($sContent){
		return \Michelf\MarkdownExtra::defaultTransform($sContent);
	}

}
