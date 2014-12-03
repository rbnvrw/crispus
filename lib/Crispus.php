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
	private $sDefaultController = 'IndexController';
	private $sControllerExt = 'php';
	private $oCurrentPage;
	private $sDefaultTemplate = 'index';

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
		
		// Pass the contents to the Page controller
		$sOutput = $this->processPage($sUrl, $sContent);
		
		// Render the page
		$this->renderPage($sOutput);		
    }
	
	private function processPage($sUrl, $sContent){
	
		$sControllerDir = $this->getConfig('crispus_paths', 'controllers') . '/';
		
		// Get the path to this page's controller file
        $sFilePath =  $sControllerDir . $sUrl;
        
        // If this is a directory, we need the index
		if(is_dir($sFilePath)) {		
		    $sFilePath = $sControllerDir . $url .'/index'. $this->sControllerExt;
		}else{
		    $sFilePath .= $this->sControllerExt;
	    }
	    
	    // Open the file
	    if(file_exists($sFilePath)){
			require_once($sFilePath);
			// Get the correct class name
			$sControllerName = capitalize(
								str_replace($this->sControllerExt, '', 
											last(
												explode('/' , $sFilePath))))
								. 'Controller';
			$this->oCurrentPage = new $sControllerName;
		} else {
			require_once($sControllerDir . $this->sDefaultController . '.php');
			$this->oCurrentPage = new $this->sDefaultController;
		}	
		
		$sContent = $this->oCurrentPage->processPage($sUrl, $sContent);
		
		return $sContent;
	}
	
	private function renderPage($sContent){
		// Ask the page controller which theme and template to render
		$sCurrentTheme = $this->getConfig('site', 'theme');
		$sCurrentTemplate = $this->sDefaultTemplate;
		
		if(!empty($this->oCurrentPage)){
			$sCurrentTheme = (empty($this->oCurrentPage->sTheme)) ? $sCurrentTheme : $this->oCurrentPage->sTheme;
			$sCurrentTemplate = (empty($this->oCurrentPage->sTemplate)) ? $sCurrentTemplate : $this->oCurrentPage->sTemplate;			
		}else{
			$sContent = $this->get404Page();
		}
		
		// Pass it through Twig (load the theme)
		\Twig_Autoloader::register();
		
		$oLoader = new \Twig_Loader_Filesystem($this->getConfig('crispus_paths', 'themes').'/' . $sCurrentTheme . '/');
		
		// Todo: default twig config + controller options
		$aTwigConfig = $this->oCurrentPage->aCustomTwigConfig + $this->getConfig('twig');
		
		$oTwig = new \Twig_Environment($oLoader, $aTwigConfig);
		
		// Twig variables
		$aTwigVars = $this->oCurrentPage->aCustomTwigVars + array(
			'content' => $sContent
		);
		
		$sOutput = $oTwig->render($sCurrentTemplate.'.html', $aTwigVars);
	
		echo $sOutput;
	}
    
    private function get404Page(){
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		
		// Settings
        $sNotFoundPage = $this->getConfig('site', 'not_found_page');
        
        return $this->getPage('/'.$sNotFoundPage);
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
