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
    
    private static $sRequestUri;
	private static $sPathToSelf;
	private static $sHttpHost;
	private static $sProtocol;
    
    public function __construct($sRootPath = '', $sRequestUri = '', $sPathToSelf = '', $sHttpHost = '', $sProtocol = 'http'){
        
        if(empty($sRootPath)){
            self::$root_path = realpath(dirname(__FILE__).'/../../');
        }else{
            self::$root_path = $sRootPath;
        } 
        
        // Set up parameters
        if(empty($sRequestUri)){
            self::$sRequestUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        }else{
            self::$sRequestUri = $sRequestUri;
        }   
        
        if(empty($sPathToSelf)){
            self::$sPathToSelf = filter_input(INPUT_SERVER, 'PHP_SELF');
        }else{
            self::$sPathToSelf = $sPathToSelf;
        }  
        
        if(empty($sHttpHost)){
            self::$sHttpHost = filter_input(INPUT_SERVER, 'HTTP_HOST');
        }else{
            self::$sHttpHost = $sHttpHost;
        }
        
        if(empty($sProtocol)){
			$sHttps = filter_input(INPUT_SERVER, 'HTTPS');
            if($sHttps != 'off'){
		        self::$sProtocol = 'https';
	        }else{
	            self::$sProtocol = 'http';
	        }
        }else{
            self::$sProtocol = $sProtocol;
        }
        
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
			),
			'render_content_page_list' => false
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
    
    public static function getInstance($sRootPath = '', $sRequestUri = '', $sPathToSelf = '', $sHttpHost = '', $sProtocol = 'http'){
        if(!(self::$_instance instanceof \Config)){           
            if(!(self::$_instance instanceof \Crispus\Config)){
                if(class_exists('\\Config')){
                    self::$_instance = new \Config($sRootPath, $sRequestUri, $sPathToSelf, $sHttpHost, $sProtocol);
                }elseif(class_exists('\\Crispus\\Config')){
                    self::$_instance = new \Crispus\Config($sRootPath, $sRequestUri, $sPathToSelf, $sHttpHost, $sProtocol);
                }
            }
        }  
        
        return self::$_instance;      
    }
	
	public static function getBaseUrl(){
		if(!empty(self::$root_url)){
			return self::$root_url;
		}
		
		$sUrl = '';
		if(self::$sRequestUri != self::$sPathToSelf){
			$sUrl = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', self::$sPathToSelf)) .'/', '', self::$sRequestUri, 1), '/');
		}

		return rtrim(str_replace($sUrl, '', self::$sProtocol . "://" . self::$sHttpHost . self::$sRequestUri), '/');
	}
	
	protected static function setPathsAndUrls(){
		
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
			
		self::$munee['path'] = self::$crispus['urls']['bin'].'/munee.php';
			
	}
}
