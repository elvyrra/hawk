<?php
/**********************************************************************
 *    						FileCache.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
class FileCache{
	private $subdir, $source, $finalname, $extension, $cache_file;
	
	public function __construct($source, $subdir, $extension){
		$this->source = realpath($source);
		$this->subdir = $subdir;
		$this->dir = CACHE_DIR . $this->subdir;		
		$this->extension = $extension;
		
		$tmp = explode('.', basename($this->source));
		$ext = $tmp[count($tmp) - 1];
		unset($tmp);
		
		$finalname = str_replace('/', '-', str_replace(ROOT_DIR, '', $this->source));
		$this->finalname = preg_replace(
			array('/^\-/', "/$ext$/"),
			array('', $this->extension),
			$finalname
		);
		
		$this->cache_file = $this->dir.'/'.$this->finalname;
	}
	
	/**
	 * Get the cache file if exists
	 */
	public function get(){		
		return is_file($this->cache_file) ? realpath($this->cache_file) : false;
	}
	
	/**
	 * Save the cache content
	 */
	public function set($content){
		if(!is_dir($this->dir))
			mkdir($this->dir, 0755, true);
		
		return file_put_contents($this->cache_file, $content) !== false ? true : false;
	}
	
	/**
	 * Returns if the file is already cached
	 */
	public function isCached(){
		$cacheFile = $this->get();	
		return $cacheFile !== false && filemtime($cacheFile) > filemtime($this->source);
	}	
}