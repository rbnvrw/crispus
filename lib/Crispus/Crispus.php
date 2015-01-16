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
class Crispus {

    private $sDefaultController = 'Crispus\IndexController';
	private $sControllerExt = 'php';
	private $oCurrentPage;
	private $sDefaultTemplate = 'index';
	private $aPages = array();
	
	private $sRootPath;
	private $sRequestUri;
	private $sPathToSelf;
	private $sServerProtocol;
	private $sHttpHost;
	private $sProtocol;

    public function __construct($sRootPath = '', $sRequestUri = '', $sPathToSelf = '', $sServerProtocol = '', $sHttpHost = '', $sProtocol = 'http')
    {            
        // Set up parameters
        if(empty($sRootPath)){
            $this->sRootPath = realpath(dirname(__FILE__).'/../../');
        }else{
            $this->sRootPath = $sRootPath;
        } 
        
        if(empty($sRequestUri)){
            $this->sRequestUri = $_SERVER['REQUEST_URI'];
        }else{
            $this->sRequestUri = $sRequestUri;
        }   
        
        if(empty($sPathToSelf)){
            $this->sPathToSelf = $_SERVER['PHP_SELF'];
        }else{
            $this->sPathToSelf = $sPathToSelf;
        }  
        
        if(empty($sServerProtocol)){
            $this->sServerProtocol = $_SERVER['SERVER_PROTOCOL'];
        }else{
            $this->sServerProtocol = $sServerProtocol;
        }  	
        
        if(empty($sHttpHost)){
            $this->sHttpHost = $_SERVER['HTTP_HOST'];
        }else{
            $this->sHttpHost = $sHttpHost;
        }
        
        if(empty($sProtocol)){
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
		        $this->sProtocol = 'https';
	        }else{
	            $this->sProtocol = 'http';
	        }
        }else{
            $this->sProtocol = $sProtocol;
        }
        
		// Set up router
        $this->setupRouter();    		
    }
	
	public function config() {
		// Get Config instance
		$sConfig = '\\Crispus\\Config';
		if(class_exists('\\Config')){
			$sConfig = '\\Config';
		}
		
		$sConfig::getInstance($this->sRootPath, $this->sRequestUri, $this->sPathToSelf, $this->sHttpHost, $this->sProtocol);
		
		$aArgs = func_get_args();
		
		$aResult = array();
		foreach($aArgs as $sArg){
			if(empty($aResult)){
				if(isset($sConfig::$$sArg)){
					$aResult = $sConfig::$$sArg;
				}
			}else{
				if(isset($aResult[$sArg])){
					$aResult = $aResult[$sArg];
				}
			}
		}
		return $aResult;
	}
	
	public function configMethod($sMethod) {
		// Get Config instance
		$sConfig = '\\Crispus\\Config';
		if(class_exists('\\Config')){
			$sConfig = '\\Config';
		}
		
		$sConfig::getInstance($this->sRootPath, $this->sRequestUri, $this->sPathToSelf, $this->sHttpHost, $this->sProtocol);
		
		return $sConfig::$sMethod();
	}
    
    /**
    * Set up routing
    */
    private function setupRouter(){
        // Get request url and script url
		$sUrl = '';
		
		// Get our url path and trim the / of the left and the right
		if($this->sRequestUri != $this->sPathToSelf){
		    $sUrl = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $this->sPathToSelf)) .'/', '', $this->sRequestUri, 1), '/');
		    $sUrl = preg_replace('/\?.*/', '', $sUrl); // Strip query string
		}else{
		    $sUrl = $this->sRequestUri;
		}	
		
		// Now that we now the relative URL, serve the page
		echo $this->getPage($sUrl);
    }
    
    /**
    * Gets the page
    */
    private function getPage($sUrl){
        if(empty($sUrl)){
            $sUrl = 'index';
        }
        
        // Settings
        $sContentDir = $this->config('crispus','paths','content') . '/';
        $sContentExt = '.'.$this->config('crispus','content_extension');
        
        // Get the path to this page's file
        $sFilePath =  $sContentDir . $sUrl;
        
        // If this is a directory, we need the index
		if(is_dir($sFilePath)) {		
		    $sFilePath = $sContentDir . $sUrl .'/index'. $sContentExt;
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
		return $this->renderPage($sOutput);		
    }
	
	private function getPageController($sUrl){
		$sControllerDir = $this->config('crispus','paths','controllers') . '/';
		
		// Get the path to this page's controller file
        $sFilePath =  $sControllerDir . $sUrl;
        
        // If this is a directory, we need the index
		if(is_dir($sFilePath)) {		
		    $sFilePath = $sControllerDir . $sUrl .'/index'. $this->sControllerExt;
		}else{
		    $sFilePath .= '.'.$this->sControllerExt;
	    }
	    
	    // Open the file
	    if(file_exists($sFilePath)){
			require_once($sFilePath);
			// Get the correct class name
			$aParts = explode('/' , $sFilePath);
			$sLast = end($aParts);
			$sControllerName = ucfirst(str_replace('.'.$this->sControllerExt, '', $sLast))
								. 'Controller';
			$oCurrentPage = new $sControllerName($this);
		} else {
			$oCurrentPage = new $this->sDefaultController($this);
		}
		
		return $oCurrentPage;
	}
	
	private function processPage($sUrl, $sContent){
	
		$this->oCurrentPage = $this->getPageController($sUrl);
		
		$sContent = $this->oCurrentPage->processPage($sUrl, $sContent);
		
		return $sContent;
	}
	
	private function renderPage($sContent){
		// Ask the page controller which theme and template to render
		$sCurrentTheme = $this->config('site','theme');
		$sCurrentTemplate = $this->sDefaultTemplate;
		
		if(!empty($this->oCurrentPage)){
			$sCurrentTheme = (empty($this->oCurrentPage->sTheme)) ? $sCurrentTheme : $this->oCurrentPage->sTheme;
			$sCurrentTemplate = (empty($this->oCurrentPage->sTemplate)) ? $sCurrentTemplate : $this->oCurrentPage->sTemplate;			
		}else{
			$sContent = $this->get404Page();
		}
		
		// Get all javascript and css assets
		$sThemePath = $this->config('crispus','urls','themes').'/' . $sCurrentTheme;
		
		// Run Twig
		$aTwigConfig = $this->oCurrentPage->aCustomTwigConfig + $this->config('twig');
		$aTwigVars = array(
			'base_url' => $this->configMethod('getBaseUrl'),
			'content' => $sContent,
			'js_prefix' => $this->getAssetString('js'),
			'css_prefix' => $this->getAssetString('css'),
			'js' => $this->oCurrentPage->sJs,
			'css' => $this->oCurrentPage->sCss,
			'theme_path' => $sThemePath,
			'page' => $this->oCurrentPage->aCustomTwigVars,
			'config' => $this->config('site'),
			'pages' => $this->getAllPages($this->config('site','menu','sort_by'), 
										((strtolower($this->config('site','menu','sort_order')) == 'asc') ? true : false),
										$this->config('site', 'render_content_page_list'))
		);
		return $this->runTwig($sCurrentTheme, $sCurrentTemplate, $aTwigConfig, $aTwigVars);
	}
	
	private function runTwig($sTheme, $sTemplate, $aConfig, $aVars){
		// Pass it through Twig (load the theme)
		\Twig_Autoloader::register();
		
		$sThemePath = $this->config('crispus','paths','themes').'/' . $sTheme . '/';
		
		$oLoader = new \Twig_Loader_Filesystem($sThemePath);		
		
		$oTwig = new \Twig_Environment($oLoader, $aConfig);
		
		return $oTwig->render($sTemplate.'.html', $aVars);
	}
	
	private function getAssetString($sType = 'js'){
	    $sMuneePath = $this->config('munee','path');
	    $bMinify = var_export($this->config('munee','minify'), true);
	    $bPacker = var_export($this->config('munee','packer'), true);
	    
	    // First filter params, then file list, so you can append in theme file
	    if($sType == 'js'){
            return $sMuneePath . '?packer=' . $bPacker . '&files=';
        }else{
            return $sMuneePath . '?minify=' . $bMinify . '&files=';
        }   
	}
	
	private function getAllPages($sSortByHeader = '', $bAsc = true, $bRenderContent = false){
		if(empty($this->aPages)){
			// Get all pages
			$sContentPath = $this->config('crispus','paths','content');
			$sContentExt = $this->config('crispus','content_extension');
			$aPageFiles = $this->getFiles($sContentPath, $sContentExt);
			
			foreach($aPageFiles as $sPage){
				// Strip directory and extension
				$sUrl = str_replace(array($sContentPath, 
											'.'.$sContentExt), '', $sPage);
				// Read contents
				$sContent = '';
				if(file_exists($sPage)){
					$sContent = file_get_contents($sPage);
				} else {
					break;
				}
											
				// Get page controller
				$oPage = $this->getPageController($sUrl);
				
				$aHeaders = $oPage->getHeaders($sUrl, $sContent);
				
				if($bRenderContent){
				    $sPageContent = $oPage->processPage($sUrl, $sContent);
				    $this->aPages[] = array('url' => $this->formatUrl($sUrl), 
				                            'headers' => $aHeaders,
				                            'content' => $sPageContent);
				}else{
				    $this->aPages[] = array('url' => $this->formatUrl($sUrl), 'headers' => $aHeaders);
				}
				
			}
					
			if(!empty($sSortByHeader)){
				$aSortArray = array();
				foreach ($this->aPages as $sKey => $aPage){
					$aSortArray[$sKey] = (isset($aPage['headers'][$sSortByHeader])) ? $aPage['headers'][$sSortByHeader] : '';
				}
				
				$iOrder = ($bAsc) ? SORT_ASC : SORT_DESC;
				
				array_multisort($aSortArray, $iOrder, $this->aPages);	
			}
			
			return $this->aPages;
		}else{
			return $this->aPages;
		}
	}
	
	/**
	 * Helper function to recusively get all files in a directory
	 *
	 * @param string $directory start directory
	 * @param string $ext optional limit to file extensions
	 * @return array the matched files
	 */ 
	protected function getFiles($sDirectory, $sExt = '')
	{
	    $aFiles = array();
	    if($oHandle = opendir($sDirectory)){
	        while(false !== ($oFile = readdir($oHandle))){
	            if(preg_match("/^(^\.)/", $oFile) === 0){
	                if(is_dir($sDirectory. "/" . $oFile)){
	                    $aFiles = array_merge($aFiles, $this->getFiles($sDirectory. "/" . $oFile, $sExt));
	                } else {
	                    $oFile = $sDirectory . "/" . $oFile;
	                    if(!$sExt || strstr($oFile, $sExt)) $aFiles[] = preg_replace("/\/\//si", "/", $oFile);
	                }
	            }
	        }
	        closedir($oHandle);
	    }
	    return $aFiles;
	}
	
	private function formatUrl($sUrl){
		return preg_replace('#/index#i', '', $sUrl);
	}
	    
    private function get404Page(){
        header($this->sServerProtocol.' 404 Not Found');
		
		// Settings
        $sUrl = $this->config('site','not_found_page');
        $sContentDir = $this->config('crispus','paths','content') . '/';
        $sContentExt = '.'.$this->config('crispus','content_extension');
        
        // Get the path to this page's file
        $sFilePath =  $sContentDir . $sUrl;
        
        // If this is a directory, we need the index
		if(is_dir($sFilePath)) {		
		    $sFilePath = $sContentDir . $sUrl .'/index'. $sContentExt;
		}else{
		    $sFilePath .= $sContentExt;
	    }
	    
	    // Open the file
	    if(file_exists($sFilePath)){
			$sContent = file_get_contents($sFilePath);
		} else {
			$sContent = "# Page not found";
		}
		
		return $sContent;
    }

}
