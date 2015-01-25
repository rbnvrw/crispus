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
class AssetManager {

	public $_oConfig;
	
	private $aAssets;
	private $aFilters;
	
	private $sThemePath;

	public function __construct($sConfigFile = 'config.json')
    {
		$this->_oConfig = new SiteConfig($sConfigFile);		
		$this->aAssets = array();
		$this->sThemePath = $this->_oConfig->getPath('themes').'/'.$this->_oConfig->get('site','theme');
	}
	
	public function addAssets($aAssets){	
		if(!is_array($aAssets)){
			return;
		}
	
		foreach($aAssets as $sAsset){
			$sExt = pathinfo($sAsset, PATHINFO_EXTENSION);
			
			$this->addAsset($sAsset, $sExt);
		}
	}
	
	public function render(){
		if(empty($this->aAssets)){
			return null;
		}
		
		// Create the asset manager
		$oManager = new \Assetic\AssetManager();
		
		// Add all the assets
		foreach($this->aAssets as $sType => $aAssets){
			$oManager->set($sType, new \Assetic\Asset\AssetCollection($aAssets));
		}
		
		// Create the asset factory
		$oFactory = new \Assetic\Factory\AssetFactory($this->sThemePath);
		$oFactory->setAssetManager($oManager);
		$oFactory->addWorker(new \Assetic\Factory\Worker\CacheBustingWorker());
		
		// Write assets to files
		$sCachePath = $this->_oConfig->getPath('cache');
		$oWriter = new \Assetic\AssetWriter($sCachePath);
		$oWriter->writeManagerAssets($oManager);

		return true;		
	}
	
	private function addAsset($sPath, $sExt){
		if(!isset($this->aAssets[$sExt]) || !is_array($this->aAssets[$sExt])){
			$this->aAssets[$sExt] = array();
		}
		
		$sPath = $this->sThemePath . '/' . $sPath;
		
		$this->aAssets[$sExt][] = new \Assetic\Asset\FileAsset($sPath, $this->getFilters($sExt));
	}
	
	private function initFilters() {
		$this->aFilters['css'] = array('Yui\\CssCompressorFilter');

		$this->aFilters['js'] = array('Yui\\JsCompressorFilter');
	}
	
	private function getFilters($sExt){
		$aFilters = array();
		
		if(empty($this->aFilters)){
			$this->initFilters();
		}
		
		if(isset($this->aFilters[$sExt])){
					
			foreach($this->aFilters[$sExt] as $sFilter){
				$sClassName = '\\Assetic\\Filter\\' . $sFilter;
				$aFilters[] = new $sClassName();
			}
		}
		
		return $aFilters;
	}
	
}