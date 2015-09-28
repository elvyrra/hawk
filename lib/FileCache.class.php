<?php
/**
 * FileCache.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class is used by internal classes to manage precompiled formats in files
 * @package Core
 */
class FileCache{
	/**
	 * The subdirectory under /cache directory
	 * @var string
	 */
	private $subdir, 

	/**
	 * The source filename
	 * @var string
	 */
	$source, 


	/**
	 * The cache basename
	 * @var string
	 */
	$finalname, 

	/**
	 * The cache file extension
	 * @var string
	 */
	$extension, 

	/**
	 * The full path of the cache file
	 * @var string
	 */
	$cache_file;
	

	/**
	 * Constructor
	 * @param string $source The source file to cache
	 * @param string $subdir The target directory, under /cache
	 * @param string $extension The target file extension
	 */
	public function __construct($source, $subdir, $extension = 'php'){
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
	 * @return string The full path of the cache file, or FALSE if the cache file does not exists
	 */
	public function getFile(){
		return is_file($this->cache_file) ? realpath($this->cache_file) : false;
	}
	

	/**
	 * Save the cache content
	 * @param string $content The content to set in the cache file
	 */
	public function set($content){
		if(!is_dir($this->dir))
			mkdir($this->dir, 0755, true);
		
		return file_put_contents($this->cache_file, $content) !== false ? true : false;
	}
	
	/**
	 * Returns if the file is already cached
	 * @return bool TRUE if the file is cached, i.e the cache file does not exist or it mtime is lower the source mtime
	 */
	public function isCached(){
		$cacheFile = $this->getFile();	
		return $cacheFile !== false && filemtime($cacheFile) > filemtime($this->source);
	}	
}