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
	private $oConfig;

    private $sDefaultController = 'Crispus\IndexController';
	private $sControllerExt = 'php';
	private $oCurrentPage;
	private $sDefaultTemplate = 'index';
	private $aPages = array();

    public function __construct($sConfigFile = '../../config/config.json')
    {            
        $this->oConfig = new \Crispus\Config($sConfigFile);
        
		// Set up router
        $this->setupRouter();    		
    }
	    
    /**
    * Set up routing
    */
    private function setupRouter(){
        // Get request url and script url
		$sUrl = '';
		$sRequestUri = $this->oConfig::get('request_uri');
		$sScriptPath = $this->oConfig::get('script_path');
		
		// Get our url path and trim the / of the left and the right
		if($sRequestUri != $sScriptPath){
		    $sUrl = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $sScriptPath)) .'/', '', $sRequestUri, 1), '/');
		    $sUrl = preg_replace('/\?.*/', '', $sUrl); // Strip query string
		}else{
		    $sUrl = $sRequestUri;
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
        $sContentDir = $this->oConfig::getPath('content') . '/';
        $sContentExt = '.'.$this->oConfig::get('crispus','content_extension');
        
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
		$sControllerDir = $this->oConfig::getPath('controllers') . '/';
		
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
		$sCurrentTheme = $this->oConfig::get('site','theme');
		$sCurrentTemplate = $this->sDefaultTemplate;
		
		if(!empty($this->oCurrentPage)){
			$sCurrentTheme = (empty($this->oCurrentPage->sTheme)) ? $sCurrentTheme : $this->oCurrentPage->sTheme;
			$sCurrentTemplate = (empty($this->oCurrentPage->sTemplate)) ? $sCurrentTemplate : $this->oCurrentPage->sTemplate;			
		}else{
			$sContent = $this->get404Page();
		}
		
		// Get all javascript and css assets
		$sThemePath = $this->oConfig::get('crispus','urls','themes').'/' . $sCurrentTheme;
		
		// Run Twig
		$aTwigConfig = $this->oCurrentPage->aCustomTwigConfig + $this->oConfig::get('twig');
		$aTwigVars = array(
			'base_url' => $this->oConfig::getMethod('getBaseUrl'),
			'content' => $sContent,
			'js_prefix' => $this->getAssetString('js'),
			'css_prefix' => $this->getAssetString('css'),
			'js' => $this->oCurrentPage->sJs,
			'css' => $this->oCurrentPage->sCss,
			'theme_path' => $sThemePath,
			'page' => $this->oCurrentPage->aCustomTwigVars,
			'config' => $this->oConfig::get('site'),
			'pages' => $this->getAllPages($this->oConfig::get('site','menu','sort_by'), 
										((strtolower($this->oConfig::get('site','menu','sort_order')) == 'asc') ? true : false),
										$this->oConfig::get('site', 'render_content_page_list'))
		);
		return $this->runTwig($sCurrentTheme, $sCurrentTemplate, $aTwigConfig, $aTwigVars);
	}
	
	private function runTwig($sTheme, $sTemplate, $aConfig, $aVars){
		// Pass it through Twig (load the theme)
		\Twig_Autoloader::register();
		
		$sThemePath = $this->oConfig::get('crispus','paths','themes').'/' . $sTheme . '/';
		
		$oLoader = new \Twig_Loader_Filesystem($sThemePath);		
		
		$oTwig = new \Twig_Environment($oLoader, $aConfig);
		
		return $oTwig->render($sTemplate.'.html', $aVars);
	}
	
	private function getAssetString($sType = 'js'){
	    $sMuneePath = $this->oConfig::get('munee','path');
	    $bMinify = var_export($this->oConfig::get('munee','minify'), true);
	    $bPacker = var_export($this->oConfig::get('munee','packer'), true);
	    
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
			$sContentPath = $this->oConfig::get('crispus','paths','content');
			$sContentExt = $this->oConfig::get('crispus','content_extension');
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
        $sUrl = $this->oConfig::get('site','not_found_page');
        $sContentDir = $this->oConfig::get('crispus','paths','content') . '/';
        $sContentExt = '.'.$this->oConfig::get('crispus','content_extension');
        
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
