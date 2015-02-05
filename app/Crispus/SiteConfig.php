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
class SiteConfig extends Config {
	    
    public function __construct($sConfigFile = 'config.json'){
               
		parent::__construct($sConfigFile);
		
		$this->setup();
        
    }
	
	private function setup() {
		$this->setRootPath();
        
        $this->_aConfig['request_uri'] = (isset($this->_aConfig['request_uri'])) ? $this->_aConfig['request_uri'] : $this->getServerVar('REQUEST_URI');
        
        $this->_aConfig['protocol'] = $this->getProtocol();
        
        $this->_aConfig['site']['base_url'] = $this->getBaseUrl();                
	}
	
	public function getPath($sKey, $sGroup = 'crispus'){
		$sRoot = '';
		if(isset($this->_aConfig['crispus']['paths']['root'])){
			$sRoot = $this->_aConfig['crispus']['paths']['root'];
		}
		
		if($sKey == 'root' && $sGroup == 'crispus'){
			return $sRoot;
		}
	
		if(isset($this->_aConfig[$sGroup]['paths'][$sKey])){
			return $sRoot.$this->_aConfig[$sGroup]['paths'][$sKey];
		}
		
		return $sRoot;
	}
	
	public function getUrl($sKey, $sGroup = 'crispus'){
		$sBaseUrl = $this->getBaseUrl();
	
		if(isset($this->_aConfig[$sGroup]['paths'][$sKey])){
			return $sBaseUrl . ltrim($this->_aConfig[$sGroup]['paths'][$sKey], '/');
		}
		
		return $sBaseUrl;
	}
	
	public function getProtocol() {
	    $sProtocol = 'http';
	
		$sHttps = $this->getServerVar('HTTPS');
		if($sHttps === 'on'){
			$sProtocol = 'https';
		}
		return $sProtocol;
	}
	
	public function getBaseUrl(){	    
	    $sBaseUrl = sprintf('%s://%s:%d/', $this->getProtocol(),
	                                        $this->getServerVar('SERVER_NAME'),
	                                        $this->getServerVar('SERVER_PORT'));
	    return $sBaseUrl; 	    
	}
	
	public function getServerVar($sName){
	    return filter_input(INPUT_SERVER, $sName, FILTER_SANITIZE_URL);
	}
	
	private function setRootPath(){		
		if(!isset($this->_aConfig['crispus']['paths']['root'])){
			/* Vendor directory is in the root, step up until we find vendor
			 * Limit to 10 levels. */
			$sCurDir = __DIR__;
			$sRelativePath = '';
			$oFilesystem = new Filesystem();
			
			for($iI = 0; $iI < 10; $iI++){
				$aDirs = $oFilesystem->getDirectories($sCurDir, false);
				
				if(!in_array('vendor', $aDirs)){
					$sCurDir = dirname($sCurDir);
					$sRelativePath .= '/..';
				}else{
					break;
				}
			}
			
			$sRelativePath .= '/';
		
			$this->_aConfig['crispus']['paths']['root'] = realpath(__DIR__.$sRelativePath);
		}		
	}
}
