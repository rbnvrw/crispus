<?php

class CrispusTest extends PHPUnit_Framework_TestCase
{

    private $oCrispus;
	private $sRootPath;
    
    public function setUp(){		
		$this->sRootPath = (defined('ROOT_PATH') && strlen(ROOT_PATH) > 0) ? ROOT_PATH : dirname(__DIR__);
    }
    
    public function tearDown(){
        unset($this->oCrispus);
    }
	
	public function testFilesystem(){
		// Test Filesystem->getFileContents()		
		$oFilesystem = new Crispus\Filesystem();
		$sContent = $oFilesystem->getFileContents($this->sRootPath.'/tests/resources/data/getFileContentsTest.txt');		
		$this->assertEquals('success', $sContent);
		
		// Test Filesystem->getFiles()
		$sTestPath = $this->sRootPath.'/tests/resources/data/test_list_files/';
		$aFiles = $oFilesystem->getFiles($sTestPath, 'txt');		
		$aExpected = array($sTestPath.'one.txt', $sTestPath.'two.txt', $sTestPath.'three.txt');
		sort($aExpected);
		sort($aFiles);
		$this->assertEquals($aExpected, $aFiles);
		
		// Test Filesystem->getAllPagesInDir($sPath, $sUrl, $sGlobalConfigFile, $sSortKey = 'sorting', $bAsc = true)
		$sTestPath = $this->sRootPath.'/tests/resources/pages/';
		$aPages = $oFilesystem->getAllPagesInDir($sTestPath, '', $this->sRootPath.'/tests/resources/config/test_site_config.json');
		$aExpected = 
		    array (
                  0 => 
                  array (
                    'name' => 'index',
                    'url' => '/index',
                    'config' => 
                    array (
                      'title' => 'Welcome to Crispus CMS!',
                      'menu' => 'true',
                      'sorting' => '0',
                    ),
                    'children' => 
                    array (
                    ),
                  ),
                  1 => 
                  array (
                    'name' => 'about',
                    'url' => '/about',
                    'config' => 
                    array (
                      'title' => 'About Crispus CMS',
                      'menu' => 'true',
                      'sorting' => '1',
                    ),
                    'children' => 
                    array (
                    ),
                  ),
                  2 => 
                  array (
                    'name' => '404',
                    'url' => '/404',
                    'config' => 
                    array (
                      'title' => 'Page not found',
                      'menu' => 'false',
                      'sorting' => '2',
                    ),
                    'children' => 
                    array (
                    ),
                  ),
                );
		
		$this->assertEquals($aExpected, $aPages);		
	}
	
	public function testSiteConfig(){
		$oConfig = new Crispus\SiteConfig($this->sRootPath.'/tests/resources/config/test_site_config.json');
		
		// Test Config->get()
		$this->assertEquals('crisp', $oConfig->get('site', 'theme'));
		
		// Test SiteConfig->getBaseUrl()
		$this->assertEquals('http://rubenverweij.nl/', $oConfig->getBaseUrl());
		
		// Test SiteConfig->getUrl()
		$this->assertEquals('http://rubenverweij.nl/vendor', $oConfig->getUrl('vendor'));
		
		// Test SiteConfig->getPath()
		$this->assertEquals($this->sRootPath.'/vendor', $oConfig->getPath('vendor'));		
	}
	
	public function testPage(){
	    $oPage = new Crispus\Page('/about', $this->sRootPath.'/tests/resources/config/test_site_config.json');
	    $oPage->build();
	    
	    // Test Page->getConfig()
	    $aConfig = $oPage->getConfig();
	    $aExpected = array('title' => "About Crispus CMS", 'menu' => "true", 'sorting' => "1");
	    sort($aConfig);
	    sort($aExpected);
	    $this->assertEquals($aExpected, $aConfig);
	}
	
	public function testCrispus(){
	    $oCrispus = new Crispus\Crispus($this->sRootPath.'/tests/resources/config/test_site_config.json');
	    $sOutput = $oCrispus->render();
	    
	    $this->assertContains('This is Crispus CMS.', $sOutput);
	}
	
}
