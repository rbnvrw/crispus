<?php

class CrispusTest extends PHPUnit_Framework_TestCase
{

    private $oCrispus;
	private $sRootPath;
    
    public function setUp(){		
		$this->sRootPath = (defined('ROOT_PATH') && strlen(ROOT_PATH) > 0) ? ROOT_PATH : realpath('../'.dirname(__FILE__));
    }
    
    public function tearDown(){
        unset($this->oCrispus);
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testRenderPage(){
		// Test if the "About" page is correctly routed and passed through Markdown and Twig
        $this->oCrispus = new Crispus\Crispus($this->sRootPath.'/config/test_config_about.json');
        
        // Expect a non-empty body tag, with <h1>About Crispus CMS</h1> in the body
        $this->expectOutputRegex('/\<body.*(?=\>)>(.*\<h1\>About Crispus CMS\<\/h1\>.*)(?=\<\/body>)/sU');
    }
	
	/**
     * @runInSeparateProcess
     */
	public function testConfig(){
		// Request the about page
		$this->oCrispus = new Crispus\Crispus($this->sRootPath.'/config/config.json');
		
		// Test if config values can be successfully retrieved
		$sMd = $this->oCrispus->config('crispus', 'content_extension');
		$this->assertEquals('md', $sMd);
	}
	
	/**
     * @runInSeparateProcess
     */
	public function testNotFoundPage(){
		// Test if the 404 not found page is correctly routed and passed through Markdown and Twig
        $this->oCrispus = new Crispus\Crispus($this->sRootPath.'/config/test_config_404.json');
        
        // Expect a non-empty body tag, with <h1>404: Not found</h1> in the body
        $this->expectOutputRegex('/\<body.*(?=\>)>(.*\<h1\>404\: Not found\<\/h1\>.*)(?=\<\/body>)/sU');
	}
}
