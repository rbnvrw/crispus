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
		$this->assertEquals(array($sTestPath.'one.txt', $sTestPath.'two.txt', $sTestPath.'three.txt'), $aFiles);
	}
	
	public function testSiteConfig(){
		$oConfig = new Crispus\SiteConfig($this->sRootPath.'/tests/resources/config/test_site_config.json');
		
		// Test Config->get()
		$this->assertEquals('crisp', $oConfig->get('site', 'theme'));
		
		// Test SiteConfig->getBaseUrl()
		$this->assertEquals('http://rubenverweij.nl', $oConfig->getBaseUrl());
		
		// Test SiteConfig->getUrl()
		$this->assertEquals('http://rubenverweij.nl/vendor', $oConfig->getUrl('vendor'));
		
		// Test SiteConfig->getPath()
		$this->assertEquals($this->sRootPath.'/vendor', $oConfig->getPath('vendor'));		
	}
}
