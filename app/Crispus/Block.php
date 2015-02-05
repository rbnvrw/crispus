<?php
namespace Crispus;

/**
 * Crispus CMS
 *
 * @author Ruben Verweij
 * @link http://rubenverweij.nl
 * @license http://opensource.org/licenses/MIT
 * @version 0.1
 */
class Block {

	private $_oConfig;
	
	private $sPath;
	private $aVars;

	public function __construct($sPath, $aVars = array(), $sConfigFile = 'config.json')
    {
		$this->_oConfig = new SiteConfig($sConfigFile);
		$this->sPath = $sPath;
		$this->aVars = $aVars;
	}
	
	public function render(){
		if(!empty($this->sPath)){
			$oFilesystem = new Filesystem();
			
			$sFileContents = $oFilesystem->getFileContents($this->sPath);
			
			return $this->processMarkdown($sFileContents);
		}
		
		return null;
	}
	
	public function getName(){
		$sBlockExt = $this->_oConfig->get('crispus', 'block_extension');
		
		return basename($this->sPath, '.'.$sBlockExt);
	}
	
	private function setMarkdownVars(){
	    // Base URL
		$this->aVars['base_url'] = rtrim($this->_oConfig->getBaseUrl(),'/');
		
		// phpThumb URL
		$this->aVars['phpThumb'] = $this->_oConfig->getPhpThumbUrl();
	}
	
	private function processMarkdown($sContent){
	    $this->setMarkdownVars();
		
		// Simple variable replacement
		foreach($this->aVars as $sName => $sVar){
			if(is_numeric($sVar) || is_string($sVar)){
				$sContent = str_replace('%'.$sName.'%', $sVar, $sContent);
			}
		}
	    
	    // Markdown
	    $sContent = \Michelf\MarkdownExtra::defaultTransform($sContent);
	    
		return trim($sContent);
	}

}
