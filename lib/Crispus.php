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

    private $sDefaultController = 'IndexController';
	private $sControllerExt = 'php';
	private $oCurrentPage;
	private $sDefaultTemplate = 'index';

    public function __construct()
    {               
        // Set up router
        $this->setupRouter();      
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
        $sContentDir = Config::$crispus['paths']['content'] . '/';
        $sContentExt = '.'.Config::$crispus['content_extension'];
        
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
	
		$sControllerDir = Config::$crispus['paths']['controllers'] . '/';
		
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
		$sCurrentTheme = Config::$site['theme'];
		$sCurrentTemplate = $this->sDefaultTemplate;
		
		if(!empty($this->oCurrentPage)){
			$sCurrentTheme = (empty($this->oCurrentPage->sTheme)) ? $sCurrentTheme : $this->oCurrentPage->sTheme;
			$sCurrentTemplate = (empty($this->oCurrentPage->sTemplate)) ? $sCurrentTemplate : $this->oCurrentPage->sTemplate;			
		}else{
			$sContent = $this->get404Page();
		}
		
		// Get all javascript and css assets
		$sThemePath = Config::$crispus['urls']['themes'].'/' . $sCurrentTheme;
		
		// Run Twig
		$aTwigConfig = $this->oCurrentPage->aCustomTwigConfig + Config::$twig;
		$aTwigVars = array(
			'content' => $sContent,
			'js_prefix' => $this->getAssetString('js'),
			'css_prefix' => $this->getAssetString('css'),
			'js' => $this->oCurrentPage->sJs,
			'css' => $this->oCurrentPage->sCss,
			'theme_path' => $sThemePath,
			'custom' => $this->oCurrentPage->aCustomTwigVars,
			'config' => Config::$site
		);
		echo $this->runTwig($sCurrentTheme, $sCurrentTemplate, $aTwigConfig, $aTwigVars);
	}
	
	private function runTwig($sTheme, $sTemplate, $aConfig, $aVars){
		// Pass it through Twig (load the theme)
		\Twig_Autoloader::register();
		
		$sThemePath = Config::$crispus['paths']['themes'].'/' . $sTheme . '/';
		
		$oLoader = new \Twig_Loader_Filesystem($sThemePath);		
		
		$oTwig = new \Twig_Environment($oLoader, $aConfig);
		
		return $oTwig->render($sTemplate.'.html', $aVars);
	}
	
	private function getAssetString($sType = 'js'){
	    $sMuneePath = Config::$munee['path'];
	    $bMinify = Config::$munee['minify'];
	    $bMinify = Config::$munee['packer'];
	    
	    // First filter params, then file list, so you can append in theme file
	    if($sType == 'js'){
            return $sMuneePath . '?packer=' . var_export($bMinify, true) . '&files=';
        }else{
            return $sMuneePath . '?minify=' . var_export($bMinify, true) . '&files=';
        }   
	}
	    
    private function get404Page(){
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		
		// Settings
        $sNotFoundPage = Config::$site['not_found_page'];
        
        return $this->getPage($sNotFoundPage);
    }

}
