<?php
namespace RubenVerweij;

/**
 * Crispus CMS
 *
 * @author Ruben Verweij
 * @link http://rubenverweij.nl
 * @license http://opensource.org/licenses/MIT
 * @version 0.1
 */
class Crispus {

    private static $aConfig = array();

    public function __construct($sConfigPath)
    {
        // Load the configuration
        $this->setConfigFromFile($sConfigPath);
        
        // Set up router
        $this->setupRouter();
        
        
    }
    
    /**
    * Set the config from the config file
    */
    private function setConfigFromFile($sConfigPath) {
        if(!empty($sConfigPath)){
            self::$aConfig = parse_ini_file($sConfigPath, true);
        }
    }
    
    /**
    * Set up routing
    */
    private function setupRouter(){
        // Get request url and script url
		$sUrl = '';
		
		$sRequestUrl = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
		$sScriptUrl  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';

		// Get our url path and trim the / of the left and the right
		if($sRequestUrl != $sScriptUrl){
		    $sUrl = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $sScriptUrl)) .'/', '', $sRequestUrl, 1), '/');
		    $sUrl = preg_replace('/\?.*/', '', $sUrl); // Strip query string
		}else{
		    $sUrl = $sRequestUrl;
		}	
		
		// Now that we now the relative URL, serve the page
		$this->getPage($sUrl);
    }
    
    /**
    * Gets the page
    */
    private function getPage($sUrl){
        if(empty($sUrl)){
            $sUrl = 'index';
        }
        
        // Settings
        $sContentDir = $this->getConfig('crispus_paths', 'content') . '/';
        $sContentExt = '.'.$this->getConfig('crispus', 'content_extension');
        
        // Get the path to this page's file
        $sFilePath =  $sContentDir . $sUrl;
        
        // If this is a directory, we need the index
		if(is_dir($sFilePath)) {		
		    $sFilePath = $sContentDir . $url .'/index'. $sContentExt;
		}else{
		    $sFilePath .= $sContentExt;
	    }
	    
	    // Open the file
	    if(file_exists($sFilePath)){
			$sContent = file_get_contents($sFilePath);
		} else {
			$sContent = $this->get404Page();
		}
		
    }
    
    public function get404Page(){
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		// Settings
        $sContentDir = $this->getConfig('crispus_paths', 'content') . '/';
        $sContentExt = '.'.$this->getConfig('crispus', 'content_extension');
        $sNotFoundPage = $this->getConfig('site', 'not_found_page');
        
        $sFilePath =  $sContentDir . $sNotFoundPage . $sContentExt;
        
        if(file_exists($sFilePath)){
			$sContent = file_get_contents($sFilePath);
		} else {
			$sContent = 'Oops, 404 page also not found';
		}       
		
		return $sContent; 
    }
    
    /** 
    * Get a config value
    * Arg: section, value, ...
    */
    public static function getConfig(){
        // Get arguments
        $aArgs = func_get_args();
        
        if(!empty(self::$aConfig) && !empty($aArgs)){
            $mValue = null;
            $aConfig = self::$aConfig;
            
            // Return the config value
            foreach($aArgs as $sKey){
                if(isset($aConfig[$sKey])){
                    $mValue = $aConfig[$sKey];
                    $aConfig = $aConfig[$sKey];
                } 
            }
            return $mValue;
        }else{
            return null;
        }
    }

}
