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
	private $sAssetPath;
	private $sThemeUrl;
	private $sAssetUrl;

	public function __construct($sConfigFile = 'config.json')
    {
		$this->aAssets = array();
		$this->_oConfig = new SiteConfig($sConfigFile);		
		
		$this->sAssetPath = $this->_oConfig->getPath('assets');
		$this->sThemePath = $this->_oConfig->getPath('themes').'/'.$this->_oConfig->get('site','theme');
		
		$this->sAssetUrl = $this->_oConfig->getUrl('assets');
		$this->sThemeUrl = $this->_oConfig->getUrl('themes').'/'.$this->_oConfig->get('site','theme');
		
		$this->addAssets($this->_oConfig->get('site', 'assets'));
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

		return $this->getAssets();		
	}
	
	private function getAssets(){
		$aAssets = array();
		
		foreach($this->aAssets as $aAsset){			
			if(!empty($aAsset['url'])){
			    if(!isset($aAssets[$aAsset['type']]) || !is_array($aAssets[$aAsset['type']])){
			        $aAssets[$aAsset['type']] = array();
			    }
			
				$aAssets[$aAsset['type']][] = $aAsset['url'];
			}
		}
		
		return $aAssets;		
	}
	
	private function addAsset($sPath, $sExt){	
		if(substr($sPath, 0, 1) == '/'){		
			$sNewPath = $this->_oConfig->getPath('root') . $sPath;		
			$sUrl = $this->_oConfig->getBaseUrl() . $sPath;
		}else{
			$sNewPath = $this->sThemePath . '/' . $sPath;
			$sUrl = $this->sThemeUrl . '/' . $sPath;
            if(!file_exists($sNewPath)){
                $sNewPath = $this->sAssetPath . '/' . $sPath;
                $sUrl = $this->sAssetUrl . '/' . $sPath;
                if(!file_exists($sNewPath)){
                    return;
                }
            }
			
		}
		
		$this->aAssets[] = array('path' => $sNewPath, 'url' => $sUrl, 'type' => $sExt);
	}
	
}
