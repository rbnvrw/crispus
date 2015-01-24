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

class Config {

	private $_aConfig;
	    
    public function __construct($sConfigFile = 'config.json'){
               
		if(file_exists($sConfigFile)){
			$sConfig = file_get_contents($sConfigFile);
		} else {
			throw new \Exception("Config file doesn't exist: ".$sConfigFile);
		}
		
		$this->_aConfig = json_decode($sConfig, true);
        
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
	
	public function getConfig(){
		return $this->_aConfig;
	}
	
}
