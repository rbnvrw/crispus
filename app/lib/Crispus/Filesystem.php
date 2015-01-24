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
	 * Helper function to recursively get all files in a directory
	 *
	 * @param string $sDirectory start directory
	 * @param string $sExt optional limit to file extensions
	 * @return array the matched files
	 */ 
	public function getFiles($sDirectory, $sExt = '')
	{
	    $aFiles = array();
	    
	    foreach(new \DirectoryIterator($sDirectory) as $oFileInfo){
	        if($oFileInfo->isDir() && !$oFileInfo->isDot()){
	            $aFiles = array_merge($aFiles, $this->getFiles($oFileInfo->getPathname(), $sExt));  
	        }elseif($oFileInfo->isFile()){
	            if(empty($sExt) || $sExt == $oFileInfo->getExtension()){
	                $aFiles[] = $oFileInfo->getPathname();
	            }
	        }	            
	    }
	    
	    return $aFiles;
	}
	
	public function getFileContents($sPath) {
		// Read contents
		if(file_exists($sPath)){
			return file_get_contents($sPath);
		} 
		
		return null;		
	}

}