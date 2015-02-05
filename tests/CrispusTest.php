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
	
	public function testFilesystemGetFileContents(){
		$oFilesystem = new Crispus\Filesystem();
		$sContent = $oFilesystem->getFileContents($this->sRootPath.'/tests/resources/data/getFileContentsTest.txt');		
		$this->assertEquals('success', $sContent);
    }
    
    public function testFilesystemGetAllPagesInDir(){		
        $oFilesystem = new Crispus\Filesystem();
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
	
	public function testSiteConfig(){
		$oConfig = new Crispus\SiteConfig($this->sRootPath.'/tests/resources/config/test_site_config.json');
		
		// Test Config->get()
		$this->assertEquals('crisp', $oConfig->get('site', 'theme'));
		
		// Test SiteConfig->getPath()
		$this->assertEquals($this->sRootPath.'/vendor', $oConfig->getPath('vendor'));		
	}
	
	public function testConfig(){
	    // Mal-formed json file
	    $this->setExpectedException('\Crispus\BadConfigException');
	    $oConfig = new Crispus\Config($this->sRootPath.'/tests/resources/config/malformed.json'); 
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
