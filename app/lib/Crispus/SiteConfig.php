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
        
        $this->_aConfig['request_uri'] = (isset($this->_aConfig['request_uri'])) ? $this->_aConfig['request_uri'] : filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
                
        $this->_aConfig['server_protocol'] = (isset($this->_aConfig['server_protocol'])) ? $this->_aConfig['server_protocol'] : filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
                
        $this->_aConfig['http_host'] = (isset($this->_aConfig['http_host'])) ? $this->_aConfig['http_host'] : filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL);
		
		$this->_aConfig['site']['base_url'] = (isset($this->_aConfig['site']['base_url'])) ? $this->_aConfig['site']['base_url'] : '/';
                
        $this->getProtocol();
	}
	
	public function getProtocol() {
		if(!isset($this->_aConfig['protocol'])){
			$sHttps = filter_input(INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_STRING);
            if($sHttps != 'off'){
		        $this->_aConfig['protocol'] = 'https';
	        }else{
	            $this->_aConfig['protocol'] = 'http';
	        }
		}
		return $this->_aConfig['protocol'];
	}
	
	public function getPath($sKey, $sGroup = 'crispus'){
		$sRoot = '';
		if(isset($this->_aConfig['crispus']['paths']['root'])){
			$sRoot = $this->_aConfig['crispus']['paths']['root'];
		}
	
		if(isset($this->_aConfig[$sGroup]['paths'][$sKey])){
			return $sRoot.$this->_aConfig[$sGroup]['paths'][$sKey];
		}
		
		return $sRoot;
	}
	
	public function getBaseUrl(){
		$sBaseUrl = '/';
		if(isset($this->_aConfig['site']['base_url'])){
			$sBaseUrl = $this->_aConfig['site']['base_url'];
		}
		return $sBaseUrl;
	}
	
	public function getUrl($sKey, $sGroup = 'crispus'){
		$sBaseUrl = $this->getBaseUrl();
	
		if(isset($this->_aConfig[$sGroup]['paths'][$sKey])){
			return $sBaseUrl.$this->_aConfig[$sGroup]['paths'][$sKey];
		}
		
		return $sBaseUrl;
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
