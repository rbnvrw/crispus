<?php
namespace Crispus;

use \Assetic\Asset\AssetCollection;
use \Assetic\Asset\FileAsset;

/**
 * Crispus CMS
 *
 * @author Ruben Verweij
 * @link http://rubenverweij.nl
 * @license http://opensource.org/licenses/MIT
 * @version 0.1
 */
class AssetManager {

	private $_oConfig;	
	
	private $oAssetManager;	
	private $aAssetFiles;
	
	private $sThemePath;	
	private $sThemeUrl;
	
	private $sCachePath;
	private $sCacheUrl;

	public function __construct($sConfigFile = 'config.json')
    {
		$this->aAssets = array();
		$this->_oConfig = new SiteConfig($sConfigFile);		
		
		$this->setUrlsAndPaths();
		
		$this->oAssetManager = new \Assetic\AssetManager();
		
		// Add global assets
		$this->addAssets($this->_oConfig->get('site', 'assets'), 'global_assets');
	}
	
	public function addAssets($aAssets, $sName){	
		if(!is_array($aAssets)){
			return;
		}
		
		// Get collection name for writing the file
		$sCollectionName = trim(preg_replace("/[^a-z0-9]/i", "_", $sName), '_');
		
		if(empty($sCollectionName)){
		    $sCollectionName = 'index';
		}
	    
	    // Sort and add path
	    $aAssets = $this->prepareAssets($aAssets);
		
		// Add assets to the asset manager
		$this->addToAssetManager($aAssets, $sCollectionName);
		
	}
	
	public function getAssetPaths(){
	
	    $this->createFiles();
	    return $this->aAssetFiles;
	        
	}
	
	private function createFiles(){

	    $oWriter = new AssetWriter($this->sCachePath);
        $oWriter->writeManagerAssets($this->oAssetManager);  
          
	}
	
	private function prepareAssets($aAssets){
	    // Sort assets on type
	    $aAssetArray = array();	    
		foreach($aAssets as $sAsset){
			$sExt = pathinfo($sAsset, PATHINFO_EXTENSION);
			
			if(!isset($aAssetArray[$sExt])){
			    $aAssetArray[$sExt] = array();
			}			
			
			$sFile = $this->sThemePath.'/'.trim($sAsset, '/');
			
			if(file_exists($sFile)){
			    $aAssetArray[$sExt][] = $sFile;
			}
		}
		
		return $aAssetArray;
	}
	
	private function addToAssetManager($aAssets, $sName){
	    // Build asset array	    
	    foreach($aAssets as $sType => $aFiles){
	    
	        $aCollection = array();
	    
	        foreach($aFiles as $sFile){
	            $aCollection[] = new FileAsset($sFile);
	        }
	        
	        $oCollection = new AssetCollection($aCollection);	        
	        $oCollection->setTargetPath($sName.'.'.$sType);
	        
	        $this->oAssetManager->set($sName.'_'.$sType, $oCollection);
	        
	        // Add to list of assets
	        if(!isset($this->aAssetFiles[$sType])){
			    $this->aAssetFiles[$sType] = array();
			}
			
			$this->aAssetFiles[$sType][] = $this->sCacheUrl.'/'.trim($sName.'.'.$sType, '/');
	    }
	    
	}
	
	private function setUrlsAndPaths(){
	    // Cache path where assets are stored
	    $this->sCachePath = $this->_oConfig->getPath('cache');
		$this->sCacheUrl = $this->_oConfig->getUrl('cache');
	
		// Theme path
		$this->sThemePath = $this->_oConfig->getPath('themes').'/'.$this->_oConfig->get('site','theme');
		$this->sThemeUrl = $this->_oConfig->getUrl('themes').'/'.$this->_oConfig->get('site','theme');
	}
	
}
