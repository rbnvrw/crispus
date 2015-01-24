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
class Theme {

	public $_oConfig;
	private $sConfigFile;
	private $sTheme;
	
	private $aPageConfig;
	private $sTemplate;
	private $aBlocks;
	private $aAssets;
	
	private $aRenderedBlocks;
	private $aRenderedAssets;

	public function __construct($sTheme, $sConfigFile = 'config.json')
    {
		$this->sTheme = $sTheme;
		
		$this->sConfigFile = $sConfigFile;
		$this->_oConfig = new SiteConfig($this->sConfigFile);
	}
	
	public function renderPage() {
		// Render all blocks
		$this->renderBlocks();
		
		// Prepare the assets
		$this->renderAssets();
	
		// Render page using Twig
		return $this->runTwig();
	}
	
	public function setPageConfig($aConfig) {
		$this->aPageConfig = array_change_key_case($aConfig, CASE_LOWER);
	}
	
	public function setTemplate($sTemplate) {
		$this->sTemplate = $sTemplate;
	}
	
	public function addBlocks($aBlocks) {
		$this->aBlocks = $aBlocks;
	}
	
	public function addAssets($aAssets) {
		$this->aAssets = $aAssets;
	}
	
	private function renderBlocks(){
		$this->aRenderedBlocks = array();
		
		foreach($this->aBlocks as $sBlock){
			// New block object
			$oBlock = new Block($sBlock, $this->aPageConfig, $this->sConfigFile);
			$this->aRenderedBlocks[$oBlock->getName()] = $oBlock->render();
		}
	}
	
	private function renderAssets(){
		$oAssetManager = new AssetManager($this->sConfigFile);
		$oAssetManager->addAssets($this->aAssets);
		$this->aRenderedAssets = $oAssetManager->render();
	}

	private function runTwig(){
		// Pass it through Twig (load the theme)
		\Twig_Autoloader::register();
		
		// Add blocks
		if(empty($this->aRenderedBlocks)){
			$this->renderBlocks();
		}
		$aVars['blocks'] = $this->aRenderedBlocks;
		
		// Add assets
		if(empty($this->aRenderedAssets)){
			$this->renderAssets();
		}
		$aVars['assets'] = $this->aRenderedAssets;
		
		// Page config
		$aVars['page'] = $this->aPageConfig;
		
		// Global site config
		$aVars['site'] = $this->_oConfig->get('site');
		
		$sThemePath = $this->_oConfig->getPath('themes').'/' . $this->sTheme . '/';
		
		$oLoader = new \Twig_Loader_Filesystem($sThemePath);		
		
		$oTwig = new \Twig_Environment($oLoader, $this->_oConfig->get('twig'));
		
		return $oTwig->render($this->sTemplate.'.html', $aVars);
	}

}