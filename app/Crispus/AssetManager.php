<?php
namespace Crispus;

use \Assetic\Asset\AssetCollection;
use \Assetic\Asset\FileAsset;
use \Assetic\Asset\AssetCache;
use \Assetic\Cache\FilesystemCache;

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
		$this->addAssets($this->_oConfig->get('site', 'assets'));
	}
	
	public function addAssets($aAssets){	
		if(!is_array($aAssets)){
			return;
		}
		    
	    // Sort and add path, name collection
	    $aAssets = $this->prepareAssets($aAssets);
		
		// Add assets to the asset manager
		$this->addToAssetManager($aAssets);
		
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
	    $sName = '';	    
		foreach($aAssets as $sAsset){
			$sExt = pathinfo($sAsset, PATHINFO_EXTENSION);
			
			if(!isset($aAssetArray[$sExt])){
			    $aAssetArray[$sExt] = array();
			}			
			
			$sFile = $this->sThemePath.'/'.trim($sAsset, '/');
			
			if(file_exists($sFile)){
			    $aAssetArray[$sExt][] = $sFile;
			    $sName .= '_'.pathinfo($sAsset, PATHINFO_BASENAME);
			}
		}
		
		$sName = md5($sName);
		
		return array($sName => $aAssetArray);
	}
	
	private function addToAssetManager($aAssets){
	    // Build asset array
	    foreach($aAssets as $sName => $aAssetGroup){	    
	        foreach($aAssetGroup as $sType => $aFiles){
	        
	            $aCollection = array();
	        
	            foreach($aFiles as $sFile){
	                $aCollection[] = new AssetCache(
	                    new FileAsset($sFile, $this->getFilters($sType)),
	                    new FilesystemCache($this->sCachePath));
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
	    
	}
	
	private function setUrlsAndPaths(){
	    // Cache path where assets are stored
	    $this->sCachePath = $this->_oConfig->getPath('cache');
		$this->sCacheUrl = $this->_oConfig->getUrl('cache');
	
		// Theme path
		$this->sThemePath = $this->_oConfig->getPath('themes').'/'.$this->_oConfig->get('site','theme');
		$this->sThemeUrl = $this->_oConfig->getUrl('themes').'/'.$this->_oConfig->get('site','theme');
	}
	
	private function getFilters($sType){
	    if($sType == 'js'){
	        return array(new \Assetic\Filter\JSqueezeFilter());
	    }
	    
	    if($sType == 'css'){
	        return array(new \Assetic\Filter\CssRewriteFilter(), new \Assetic\Filter\CssMinFilter());
	    }
	    
	    return array();
	}
	
}
