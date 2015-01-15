<?php

class CrispusTest extends PHPUnit_Framework_TestCase
{

    private $oCrispus;
    
    public function setUp(){
        if(!defined('ROOT_PATH')){
            define('ROOT_PATH', realpath('../'.dirname(__FILE__)));
        }
    }
    
    public function tearDown(){
        unset($this->oCrispus);
    }
    
    public function testRenderPage(){
        $this->oCrispus = new Crispus\Crispus('/index');
        
        // Expect a non-empty body
        $this->expectOutputRegex('/<body>[^<]*[^<\s][^<]*<\/body>/imx');
    }
}
