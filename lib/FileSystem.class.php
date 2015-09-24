<?php

/**
 * FileSystem.class.php
 * @author Elyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class has utilities to manage the file system, for missing functions in PHP
 * @package Utils
 */
class FileSystem{
	const DP = DIRECTORY_SEPARATOR;

	const FIND_FILE_ONLY = 'file';
	const FIND_DIR_ONLY = 'dir';
	const FIND_ANY_TYPE = 'any';

	/**
	 * Equivalent to cp -r 
	 * @param string $source The source file or directory to copy
	 * @param string $dest The destination file or directory
	 */
	public static function copy($source, $dest){
		if(! file_exists($source)){
			throw new FileSystemException('Cannot copy ' . $source . ' : No such file or directory');
		}

		if(is_file($source)){
			// Copy a file
			if(is_dir($dest)){
				$dest = $dest . self::DP . basename($source);
			}
			copy($source, $dest);
		}
		else{
			// Copy a directory
			$base = basename($source);
			if(!is_dir($dest . self::DP . $base)){
				mkdir($dest . self::DP . $base, fileperms($source), true);
			}

			// Copy all files and folder under this directory
			foreach(glob($source . self::DP . '*') as $element){				
				self::copy($element, $dest . self::DP . $base);
			}
		}
	}


	/**
	 * Find files by a pattern
	 * @param string $source The directory to search in	 
	 * @param string $type The type of source to find : 'file', 'dir', 'any'	 
	 * @return array The list of files or directories found
	 */
	public static function find($source, $pattern, $type = self::FIND_ANY_TYPE){
		if(!is_dir($source)){
			throw new FileSystemException('The method ' . __METHOD__ . ' requires the first argument to be an existing directory : ' . $source . ' is not a directory');
		}
		switch($type){
			case self::FIND_FILE_ONLY :
				$result = array_filter(glob($source . self::DP . $pattern), 'is_file');
				break;

			case self::FIND_DIR_ONLY :
				$result = glob($source . self::DP . $pattern, GLOB_ONLYDIR);
				break;

			default :
				$result = glob($source . self::DP . $pattern);
				break;
		}		

		$subdirs = glob($source . self::DP . '*', GLOB_ONLYDIR);
		foreach($subdirs as $dir){			
			$result = array_merge($result, self::find($dir, $pattern, $type));
		}

        return $result;
	}


	/**
	 * Remove a directory or a file
	 * @param string $source The file or directory name to remove
	 * @return boolean, TRUE if the source was removed, else FALSE
	 */
	public static function remove($source){		
		if(! file_exists($source)){
			throw new FileSystemException('Cannot remove ' . $source . ' : No such file or directory');
		}

		if(is_file($source)){
			// remove a file
			return unlink($source);
		}
		else{
			// remove a directory 
			foreach(glob($source . self::DP . '*') as $element){
				self::remove($element);				
			}
			return rmdir($source);
		}		
	}
}


/**
 * This class describes exceptions throwed by FileSystem class
 */
class FileSystemException extends \Exception{}