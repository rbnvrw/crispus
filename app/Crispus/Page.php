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
class Page {

	private $_oGlobalConfig;
	private $sPath;
	
	private $sConfigFile = "config.json";

	private $sUrl;
	private $sType = "default";
	private $sTemplate = "base";
	
	private $aConfig;
	private $aBlocks;
	private $aAssets;	

	public function __construct($sUrl, $sGlobalConfigFile = 'config.json')
    {
		// Init global config
		$this->_oGlobalConfig = new SiteConfig($sGlobalConfigFile);
		
		// Set URL
		$this->sUrl = $sUrl;
		$this->setPath();
	}
	
	public function build()
	{
		// Get page config
		$this->setConfig();
		
		// Get all page blocks
		$this->setBlocks();
		
		// Get all the assets
		$this->setAssets();
	}
	
	public function getConfig(){
		return $this->aConfig;
	}
	
	public function getBlocks(){
		return $this->aBlocks;
	}
	
	public function getAssets(){
		return $this->aAssets;
	}
	
	public function getType(){
		return $this->sType;
	}
	
	public function getTemplate(){
		return $this->sTemplate;
	}
	
	private function setPath(){		
		// Get the path to this page's file
		$sPageDir = $this->_oGlobalConfig->getPath('pages') . '/';
        $this->sPath =  $sPageDir . $this->sUrl;
	
        // Check if it is a directory
		if(!is_dir($this->sPath)) {
			// Serve 404 page
			$this->set404Page();
		}
	}
	
	private function setConfig(){
		if(empty($this->sPath)){
			$this->setPath();
		}
	
		$oPageConfig = new Config($this->sPath.'/'.$this->sConfigFile);
		$this->aConfig = $oPageConfig->getConfig();
		
		// Set page template
		if(isset($this->aConfig['template']) && !empty($this->aConfig['template'])){
			$this->sTemplate = $this->aConfig['template'];
		}
	}
	
	private function setBlocks(){
		$sBlockExt = $this->_oGlobalConfig->get('crispus', 'block_extension');
		
		// Get all block files
		$oFilesystem = new Filesystem();
		$this->aBlocks = $oFilesystem->getFiles($this->sPath, $sBlockExt);
	}
	
	private function setAssets(){
		if(empty($this->aConfig)){
			$this->setConfig();
		}
		
		if(isset($this->aConfig['assets']) && !empty($this->aConfig['assets'])){
			$this->aAssets = $this->aConfig['assets'];
		}
	}
	
	private function set404Page(){
		$this->sType = "404";
	}

}