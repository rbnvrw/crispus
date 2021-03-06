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
	private $sDefaultTemplate = 'index';
	private $aBlocks;
	private $aAssets;
	private $aPageList;
	private $aChildren;
	
	private $sUrl;
	
	private $aRenderedBlocks;
	private $aRenderedAssets;
	
	private $aDefaultTwigSettings;

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
		if(is_array($aConfig)){
			if(is_array($aConfig)){
		        $this->aPageConfig = array_change_key_case($aConfig, CASE_LOWER);
		    }else{
		        $this->aPageConfig = array();
		    }
		}
		
		if(isset($this->aPageConfig['template'])){
		    $this->setTemplate($this->aPageConfig['template']);
		}else{
		    $this->setTemplate($this->sDefaultTemplate);
		}
	}
	
	public function addBlocks($aBlocks) {
		$this->aBlocks = $aBlocks;
	}
	
	public function addAssets($aAssets) {
		$this->aAssets = $aAssets;
	}
	
	public function setSitemapInfo($sUrl, $aPageList, $aChildren) {
		$this->aPageList = $aPageList;
		$this->aChildren = $aChildren;
		$this->sUrl = $sUrl;
	}
	
	private function setTemplate($sTemplate) {
		$this->sTemplate = $sTemplate;
		
		if(empty($this->sTemplate)){
		    $this->sTemplate = $this->sDefaultTemplate;    
		}
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
		$this->aRenderedAssets = $oAssetManager->getAssetPaths();
	}

	private function runTwig(){
		// Pass it through Twig (load the theme)
		\Twig_Autoloader::register();
		
		// Add blocks
		if(empty($this->aRenderedBlocks)){
			$this->renderBlocks();
		}
		
		// Add assets
		if(empty($this->aRenderedAssets)){
			$this->renderAssets();
		}
		
		// Add twig variables
		$aVars = array(
		    'assets' => $this->aRenderedAssets,
		    'blocks' => $this->aRenderedBlocks,
		    'children' => $this->aChildren,
		    'page' => $this->aPageConfig,
		    'pages' => $this->aPageList,
		    'site' => $this->_oConfig->get('site'),
			'url' => $this->sUrl,
			'phpThumb' => $this->_oConfig->getPhpThumbUrl()
		);
		
		$sThemePath = $this->_oConfig->getPath('themes').'/' . $this->sTheme . '/';
		
		$oLoader = new \Twig_Loader_Filesystem($sThemePath);		
		
		$oTwig = new \Twig_Environment($oLoader, $this->getTwigConfig($this->_oConfig->get('twig')));
		
		if($this->_oConfig->get('twig', 'debug') === true){
		    $oTwig->addExtension(new \Twig_Extension_Debug());
		}
		
		return $oTwig->render($this->sTemplate.'.html', $aVars);
	}
	
	private function getTwigConfig($aSettings){
	    $this->aDefaultTwigSettings = (!is_array($this->aDefaultTwigSettings)) ? array() : $this->aDefaultTwigSettings;
        $aSettings = !(is_array($aSettings)) ? array() : $aSettings;
        
        $this->aDefaultTwigSettings['cache'] = $this->_oConfig->getPath('cache');

        return array_replace_recursive($this->aDefaultTwigSettings, $aSettings);    
	}

}
