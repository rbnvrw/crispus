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
    
    /**
     * @runInSeparateProcess
     */
    public function testRenderPage(){
        $this->oCrispus = new Crispus\Crispus('/home/ruben/Development/GitHub/crispus/', 'index', '', 'HTTP/1.1', 'rubenverweij.nl');
        
        // Expect a non-empty body
        $this->expectOutputRegex('/\<body.*(?=\>)>((.)+)(?=\<\/body>)/sU');
    }
}
