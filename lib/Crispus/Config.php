<?php
namespace Crispus;

// Class to hold all configuration

class Config {

    public static $_instance;
	private static $_aConfig;
	    
    public function __construct($sConfigFile = '../../config/config.json'){
               
		if(file_exists($sConfigFile)){
			$sConfig = file_get_contents($sConfigFile);
		} else {
			throw new Exception("Config file doesn't exist: ".$sConfigFile);
		}
		
		self::$_aConfig = json_decode($json_data, true);
		
		self::setup();
        
    }
	
	public static function setup() {
		// Set up parameters
        if(!isset(self::$_aConfig['crispus']['paths']['root']) || empty(self::$_aConfig['crispus']['paths']['root'])){
            self::$_aConfig['crispus']['paths']['root'] = realpath(dirname(__FILE__).'/../../');
        }
        
        if(!isset(self::$_aConfig['request_uri']) || empty(self::$_aConfig['request_uri'])){
            self::$_aConfig['request_uri'] = filter_input(INPUT_SERVER, 'REQUEST_URI');
        }
        
        if(!isset(self::$_aConfig['script_path']) || empty(self::$_aConfig['script_path'])){
            self::$_aConfig['script_path'] = filter_input(INPUT_SERVER, 'PHP_SELF');
        }
        
        if(!isset(self::$_aConfig['server_protocol']) || empty(self::$_aConfig['server_protocol'])){
            self::$_aConfig['server_protocol'] = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL');
        }
        
        if(!isset(self::$_aConfig['http_host']) || empty(self::$_aConfig['http_host'])){
            self::$_aConfig['http_host'] = filter_input(INPUT_SERVER, 'HTTP_HOST');
        }
        
        if(!isset(self::$_aConfig['protocol']) || empty(self::$_aConfig['protocol'])){
            $sHttps = filter_input(INPUT_SERVER, 'HTTPS');
            if($sHttps != 'off'){
		        self::$_aConfig['protocol'] = 'https';
	        }else{
	            self::$_aConfig['protocol'] = 'http';
	        }
        }
	}
	
	public static function get(){	
		$aArgs = func_get_args();
		
		$aResult = array();
		foreach($aArgs as $sArg){
			if(empty($aResult)){
				if(isset(self::$_aConfig[$sArg])){
					$aResult = self::$_aConfig[$sArg];
				}
			}else{
				if(isset($aResult[$sArg])){
					$aResult = $aResult[$sArg];
				}
			}
		}
		return $aResult;	
	}
	
	public static function getPath($sKey, $sGroup = 'crispus'){
		$sRoot = '';
		if(isset(self::$_aConfig['crispus']['paths']['root'])){
			$sRoot = self::$_aConfig['crispus']['paths']['root'];
		}
	
		if(isset(self::$_aConfig[$sGroup]['paths'][$sKey])){
			return $sRoot.self::$_aConfig[$sGroup]['paths'][$sKey];
		}
		
		return $sRoot;
	}
	
}
