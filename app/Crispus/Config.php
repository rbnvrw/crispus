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
	protected $sError = "There was an error decoding the config file %s. JSON error: %s";
	    
    public function __construct($sConfigFile = 'config.json'){
               
		$oFilesystem = new Filesystem();		
		$sContents = $oFilesystem->getFileContents($sConfigFile);
		
		if(!empty($sContents)){
			$this->_aConfig = json_decode($sContents, true);
			
			if(empty($this->_aConfig)){
			    throw new BadConfigException(sprintf($this->sError, $sConfigFile, $this->getJSONError(json_last_error())));
			}
		}else{
			throw new BadConfigException("Config file is empty: ".$sConfigFile);
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
	
	protected function getJSONError($iError){
	    switch ($iError) {
            case JSON_ERROR_NONE:
                return 'Success';
            break;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                return 'Unknown error';
            break;
        }
	}
}
