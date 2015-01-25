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
		
		$this->addAssets($this->_oConfig->get('site', 'assets'), 'global');
	}
	
	public function addAssets($aAssets, $sType = 'file'){	
		if(!is_array($aAssets)){
			return;
		}
	
		foreach($aAssets as $sAsset){
			$sExt = pathinfo($sAsset, PATHINFO_EXTENSION);
			
			$this->addAsset($sAsset, $sExt, $sType);
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
			$oCollection = new \Assetic\Asset\AssetCollection($aAssets);
			$oCollection->setTargetPath('assets.'.$sType);
			$oManager->set($sType, $oCollection);
		}
		
		// Create the asset factory
		$oFactory = new \Assetic\Factory\AssetFactory($this->sThemePath);
		$oFactory->setAssetManager($oManager);
		$oFactory->addWorker(new \Assetic\Factory\Worker\CacheBustingWorker());
		
		// Write assets to files
		$sCachePath = $this->_oConfig->getPath('cache');
		$oWriter = new \Assetic\AssetWriter($sCachePath);
		$oWriter->writeManagerAssets($oManager);

		return $this->getAssets();		
	}
	
	private function getAssets(){
		$aAssets = array();
		
		foreach($this->aAssets as $sType => $aAssetObjects){
			$sUrl = $this->getAssetUrl($sType);
			
			if(!empty($sUrl)){
				$aAssets[$sType] = $sUrl;
			}
		}
		
		return $aAssets;		
	}
	
	private function getAssetUrl($sType){
		if(empty($sType)){
			return null;
		}
		
		$sPath = $this->_oConfig->getPath('cache') . '/' . 'assets.' . $sType;
		
		if(file_exists($sPath)){
			return $this->_oConfig->getUrl('cache') . '/' . 'assets.' . $sType;
		}
		
		return null;
	}
	
	private function addAsset($sPath, $sExt, $sType = 'file'){
		if(!isset($this->aAssets[$sExt]) || !is_array($this->aAssets[$sExt])){
			$this->aAssets[$sExt] = array();
		}
		
		if(substr($sPath, 0, 1) == '/'){		
			$sPath = $this->_oConfig->getPath('root') . $sPath;		
		}else{
			$sPath = $this->sThemePath . '/' . $sPath;
		}
		
		if($sType == 'global'){
			$this->aAssets[$sExt][] = new \Assetic\Asset\GlobAsset($sPath, $this->getFilters($sExt));
		}else{
			$this->aAssets[$sExt][] = new \Assetic\Asset\FileAsset($sPath, $this->getFilters($sExt));
		}
	}
	
	private function initFilters() {
		$this->aFilters = array();
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