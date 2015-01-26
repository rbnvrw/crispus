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
	
	/**
     * @covers Crispus\Filesystem::getFileContents
     * @covers Crispus\Filesystem::getFiles
     * @covers Crispus\Filesystem::getAllPagesInDir
     */
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
		$aNames = array();
		foreach($aPages as $aPage){
		    $aNames[] = $aPage['name'];
		}
		$aExpected = array('404', 'about', 'index');
		sort($aNames);
		sort($aExpected);
		$this->assertEquals($aExpected, $aNames);		
	}
	
	/**
     * @covers Crispus\Config::get
     * @covers Crispus\SiteConfig::getBaseUrl
     * @covers Crispus\SiteConfig::getUrl
     * @covers Crispus\SiteConfig::getPath
     */
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
	
	/**
     * @covers Crispus\Page::getConfig
     */
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
	
	/**
     * @covers Crispus\Crispus::render
     */
	public function testCrispus(){
	    $oCrispus = new Crispus\Crispus($this->sRootPath.'/tests/resources/config/test_site_config.json');
	    $sOutput = $oCrispus->render();
	    
	    $this->assertContains('This is Crispus CMS.', $sOutput);
	}
	
}
