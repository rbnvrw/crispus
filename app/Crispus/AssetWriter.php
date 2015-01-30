<?php
namespace Crispus;

/**
 * Crispus CMS
 *
 * @author Ruben Verweij
 * @link http://rubenverweij.nl
 * @license http://opensource.org/licenses/MIT
 * @version 0.1
 */
class AssetWriter extends \Assetic\AssetWriter {

    protected static $iMaxTimeDiff = 3600;

    protected static function write($sPath, $sContents)
    {
        // Check if dir exists, if not, create it
        self::checkDir($sPath);
        
        // Check if file already exist
        if(!file_exists($sPath)){
            self::writeToFile($sPath, $sContents);
        }elseif(self::getModificationTimeDiff($sPath) >= self::$iMaxTimeDiff){
            self::writeToFile($sPath, $sContents);
        }        
        
    }
    
    protected static function checkDir($sPath){
        $sDir = dirname($sPath);
        
        if (!is_dir($sDir) && false === @mkdir($sDir, 0777, true)) {
            throw new \RuntimeException('AssetWriter: Unable to create directory '.$sDir);
        }
        
        return true;
    }
    
    protected static function writeToFile($sPath, $sContents){
        if (false === @file_put_contents($sPath, $sContents)) {
            throw new \RuntimeException('AssetWriter: Unable to write file '.$sPath);
        }
        
        return true;
    }
    
    protected static function getModificationTimeDiff($sPath){        
        try{
            $iModTime = filemtime($sPath);
            clearstatcache(); 
            
            return time() - $iModTime;
        }catch(\Exception $e){
            return self::$iMaxTimeDiff+1;    
        }
    }

}
