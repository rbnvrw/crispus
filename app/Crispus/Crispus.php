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
		
	public $_oConfig;	
	private $sConfigFile;
	
	private $aHeaders;

    public function __construct($sConfigFile = 'config.json')
    {                   
		$this->sConfigFile = $sConfigFile;
		$this->_oConfig = new SiteConfig($this->sConfigFile); 		
    }
    
    public function render(){
        // Set up router
        $sUrl = $this->determineUrl();  

		// Now that we now the relative URL, serve the page
		return $this->renderPage($sUrl);
    }
	    
    /**
    * Set up routing
    */
    private function determineUrl(){
        // Get request url and script url
		$sUrl = '';
		$sRequestUri = $this->_oConfig->get('request_uri');
		
		// Get our url path and trim the / of the left and the right
		$sUrl = trim($sRequestUri, '/');
		$sUrl = preg_replace('/\?.*/', '', $sUrl); // Strip query string
		$sUrl = preg_replace('#index$#i', '', $sUrl); // Strip 'index' off end
		
		if(empty($sUrl)){
			return 'index';
		}
		
		return $sUrl;
    }
    	
	private function renderPage($sUrl){
		// Build page object
		$oPage = new Page($sUrl, $this->sConfigFile);
		$oPage->build();
		
		// Get the theme
		$sTheme = $this->_oConfig->get('site', 'theme');
		$oTheme = new Theme($sTheme, $this->sConfigFile);
		
		// Add assets, blocks and config
		$oTheme->setPageConfig($oPage->getConfig());
		$oTheme->setTemplate($oPage->getTemplate());
		$oTheme->addBlocks($oPage->getBlocks());
		$oTheme->addAssets($oPage->getAssets());
		$oTheme->setChildren($oPage->getChildren());

		// Add page list for menus
		$sPath = $this->_oConfig->getPath('pages');
		
		$oFilesystem = new Filesystem();
		$oTheme->setPageList($oFilesystem->getAllPagesInDir($sPath, '', $this->sConfigFile));
		
		return $oTheme->renderPage();		
	}

}
