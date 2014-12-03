<?php
namespace RubenVerweij;

// Class to hold all configuration

class Config {

    public static $_instance;

    public static $root_path;    
    public static $root_url;
    
    public static $site;
    
    public static $twig;
    
    public static $crispus;
    
    public static $munee;
    
    protected function __construct(){
    
        if(!defined('ROOT_PATH') || empty(ROOT_PATH)){
            self::$root_path = realpath(dirname(__FILE__)) .'/';
        }else{
            self::$root_path = ROOT_PATH;
        }
        
        self::$root_url = '/';
    
        self::$site = array(
            'title' => 'Crispus CMS',
            'theme' => 'crisp',
            'not_found_page' => '404'
        );
        
        self::$twig = array(
            'cache' => false,
            'autoescape' => false
        );
        
        self::$crispus = array(
            'content_extension' => 'md',
            'paths' => array(
                'cache' => self::$root_path.'data/cache',
                'content' => self::$root_path.'content',
                'controllers' => self::$root_path.'controllers',
                'data' => self::$root_path.'data',
                'lib' => self::$root_path.'lib',
                'plugins' => self::$root_path.'plugins',
                'themes' => self::$root_path.'themes',
                'vendor' => self::$root_path.'vendor'
            ),
            'urls' => array(
                'cache' => self::$root_url.'data/cache',
                'content' => self::$root_url.'content',
                'controllers' => self::$root_url.'controllers',
                'data' => self::$root_url.'data',
                'lib' => self::$root_url.'lib',
                'plugins' => self::$root_url.'plugins',
                'themes' => self::$root_url.'themes',
                'vendor' => self::$root_url.'vendor'
            ),
        );
        
        self::$munee = array(
            'path' => self::$root_url.'lib/Munee.php',
            'minify' => true
        );
        
    }
    
    public static function getInstance(){
        if(!(self::$_instance instanceof Config)){
            self::$_instance = new Config();
        }  
        
        return self::$_instance;      
    }
}

// Initialize config
Config::getInstance();
