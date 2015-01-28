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
class Filesystem {

	/**
	 * Helper function to get all files in a directory
	 *
	 * @param string $sDirectory start directory
	 * @param string $sExt optional limit to file extensions
	 * @return array the matched files
	 */ 
	public function getFiles($sDirectory, $sExt = '')
	{
	    $aFiles = array();
	    
	    if(!is_dir($sDirectory)){
			return null;
		}
	    
	    foreach(new \DirectoryIterator($sDirectory) as $oFileInfo){
	        if($oFileInfo->isFile()){
	            if(empty($sExt) || $sExt == $oFileInfo->getExtension()){
	                $aFiles[] = $oFileInfo->getPathname();
	            }
	        }	            
	    }
	    
	    return $aFiles;
	}
	
	public function getDirectories($sDirectory, $bWithPath = true){
		$aDirs = array();
		
		if(!is_dir($sDirectory)){
			return $aDirs;
		}
	    
	    foreach(new \DirectoryIterator($sDirectory) as $oFileInfo){
	        if($oFileInfo->isDir() && !$oFileInfo->isDot()){
				if($bWithPath){
					$aDirs[] = $oFileInfo->getPathname();
				}else{
					$aDirs[] = $oFileInfo->getFilename();
				}
	        }	            
	    }
	    
	    return $aDirs;
	}
	
	public function getAllPagesInDir($sPath, $sUrl, $sGlobalConfigFile){		
		$oConfig = new SiteConfig($sGlobalConfigFile);
		$sSortKey = $oConfig->get('site', 'menu', 'sort_by');
		$bAsc = (bool)$oConfig->get('site', 'menu', 'sort_asc');
				
		$aPages = array();
		$aDirs = $this->getDirectories($sPath, false);
		foreach($aDirs as $sDir){
		    $aPage = $this->getPageFromDir($sDir, $sUrl, $sGlobalConfigFile);
		    
		    if(isset($aPage['config'][$sSortKey]) && !empty($aPage['config'][$sSortKey])){
		        $aPages[$aPage['config'][$sSortKey]] = $aPage;
		    }else{
		        $aPages[] = $aPage;
		    }  
		}
		
		if($bAsc === false){
		    krsort($aPages);
		}else{
		    ksort($aPages);
		}
		
		return $aPages;
	}
	
	public function getFileContents($sPath) {
		// Read contents
		if(file_exists($sPath)){
			return file_get_contents($sPath);
		} 
		
		return null;		
	}
	
	private function getPageFromDir($sDir, $sUrl, $sGlobalConfigFile){
	    $sNewUrl = $sUrl.'/'.$sDir;
		    
	    $oPage = new Page($sNewUrl, $sGlobalConfigFile);
	    $aConfig = $oPage->getConfig();
		
		if(is_array($aConfig)){
		    $aConfig = array_change_key_case($aConfig, CASE_LOWER);
		}else{
		    $aConfig = array();
		}
	
	    return array('name' => $sDir, 'url' => $sNewUrl, 'config' => $aConfig, 'children' => $oPage->getChildren());
	}

}
