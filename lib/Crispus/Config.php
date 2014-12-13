<?php
namespace Crispus;

// Class to hold all configuration

class Config {

    public static $_instance;
	
	public static $crispus_path;
	public static $crispus_url;

    public static $root_path;    
    public static $root_url;
    
    public static $site;
    
    public static $twig;
    
    public static $crispus;
    
    public static $munee;
    
    protected function __construct(){
        self::$root_url = '';
    
        self::$site = array(
            'title' => 'Crispus CMS',
            'theme' => 'crisp',
            'not_found_page' => '404',
            'css_theme_folder' => 'css',
            'js_theme_folder' => 'js',
			'excerpt_length' => 150,
			'menu' => array(
				'sort_order' => 'asc',
				'sort_by'	 =>	'sorting'
			)
        );
        
        self::$twig = array(
            'cache' => false,
            'autoescape' => false
        );
        
        self::$crispus = array(
            'content_extension' => 'md'
        );
        
        self::$munee = array(
            'minify' => true,
            'packer' => true
        );
		
		self::setPathsAndUrls();
        
    }
    
    public static function getInstance(){
        if(!(self::$_instance instanceof Config)){
            self::$_instance = new Config();
        }  
        
        return self::$_instance;      
    }
	
	public static function getBaseUrl(){
		if(!empty(self::$root_url)){
			return self::$root_url;
		}
		
		$sUrl = '';
		$sRequestUrl = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
		$sScriptUrl  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';
		if($sRequestUrl != $sScriptUrl){
			$sUrl = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $sScriptUrl)) .'/', '', $sRequestUrl, 1), '/');
		}

		$sProtocol = 'http';
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
			$sProtocol = 'https';
		}
		return rtrim(str_replace($sUrl, '', $sProtocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']), '/');
	}
	
	protected static function setPathsAndUrls(){
		
		// Set site root path
		if(!defined('ROOT_PATH') || empty(ROOT_PATH)){
            self::$root_path = realpath(dirname(__FILE__));
        }else{
            self::$root_path = ROOT_PATH;
        }
		
		// Set crispus root path & url
		self::$crispus_path = self::$root_path . '/vendor/rbnvrw/crispus';
		self::$crispus_url = self::$root_url . '/vendor/rbnvrw/crispus';
		
		self::$crispus['paths'] = 
		array(
                'bin'	=> self::$crispus_path.'/bin',
				'cache' => self::$root_path.'/data/cache',
                'content' => self::$root_path.'/content',
                'controllers' => self::$root_path.'/controllers',
                'data' => self::$root_path.'/data',
                'lib' => self::$crispus_path.'/lib',
                'plugins' => self::$root_path.'/plugins',
                'themes' => self::$root_path.'/themes',
                'vendor' => self::$root_path.'/vendor'
            );
			
		self::$crispus['urls'] = 
		array(
                'bin'	=> self::$crispus_url.'/bin',
				'cache' => self::$root_url.'/data/cache',
                'content' => self::$root_url.'/content',
                'controllers' => self::$root_url.'/controllers',
                'data' => self::$root_url.'/data',
                'lib' => self::$crispus_url.'/lib',
                'plugins' => self::$root_url.'/plugins',
                'themes' => self::$root_url.'/themes',
                'vendor' => self::$root_url.'/vendor'
            );
			
		self::$munee['path'] = self::$crispus['urls']['bin'].'/Munee.php';
			
	}
}
