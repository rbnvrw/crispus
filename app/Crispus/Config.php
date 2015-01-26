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

	protected $_aConfig;
	    
    public function __construct($sConfigFile = 'config.json'){
               
		$oFilesystem = new Filesystem();		
		$sContents = $oFilesystem->getFileContents($sConfigFile);
		
		if(!empty($sContents)){
			$this->_aConfig = json_decode($sContents, true);
		}else{
			$this->_aConfig = array();
		}        
    }
	
	public function get(){	
		$aArgs = func_get_args();
		
		$aResult = null;
		foreach($aArgs as $sArg){
			if(empty($aResult)){
				if(isset($this->_aConfig[$sArg])){
					$aResult = $this->_aConfig[$sArg];
				}else{
				    $aResult = null;
				    break;
				}
			}else{
				if(isset($aResult[$sArg])){
					$aResult = $aResult[$sArg];
				}else{
				    $aResult = null;
				    break;
				}
			}
		}
		return $aResult;	
	}
	
	public function getConfig(){
		return $this->_aConfig;
	}
	
}
