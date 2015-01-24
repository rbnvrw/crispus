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
			
			return processMarkdown($sFileContents);
		}
		
		return null;
	}
	
	public function getName(){
		$sBlockExt = $this->_oGlobalConfig->get('crispus', 'block_extension');
		
		return basename($this->sPath, $sBlockExt);
	}
	
	private function processMarkdown($sContent){
	    // Base URL
		$this->aVars['base_url'] = $this->_oConfig->getBaseUrl();
		
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