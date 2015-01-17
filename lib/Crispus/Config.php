<?php
namespace Crispus;

// Class to hold all configuration

class Config {

	private $_aConfig;
	    
    public function __construct($sConfigFile = 'config.json'){
               
		if(file_exists($sConfigFile)){
			$sConfig = file_get_contents($sConfigFile);
		} else {
			throw new \Exception("Config file doesn't exist: ".$sConfigFile);
		}
		
		$this->_aConfig = json_decode($sConfig, true);
		
		$this->setup();
        
    }
	
	private function setup() {
		// Set up parameters
        	
		$this->_aConfig['crispus']['paths']['root'] = (isset($this->_aConfig['crispus']['paths']['root'])) ? $this->_aConfig['crispus']['paths']['root'] : realpath(__DIR__.'/../../');
        
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
	
	public function get(){	
		$aArgs = func_get_args();
		
		$aResult = null;
		foreach($aArgs as $sArg){
			if(empty($aResult)){
				if(isset($this->_aConfig[$sArg])){
					$aResult = $this->_aConfig[$sArg];
				}
			}else{
				if(isset($aResult[$sArg])){
					$aResult = $aResult[$sArg];
				}
			}
		}
		return $aResult;	
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
	
	public function getUrl($sKey, $sGroup = 'crispus'){
		$sBaseUrl = '/';
		if(isset($this->_aConfig['site']['base_url'])){
			$sBaseUrl = $this->_aConfig['site']['base_url'];
		}
	
		if(isset($this->_aConfig[$sGroup]['paths'][$sKey])){
			return $sBaseUrl.$this->_aConfig[$sGroup]['paths'][$sKey];
		}
		
		return $sBaseUrl;
	}
	
}
