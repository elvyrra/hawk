<?php
/**
 * Cache.php
 *
 * @author  Elvyrra S.A.S
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to manage cache in Hawk. It allows to save cache files in the folder /cache,
 * and detect if a file is cached. This class is very useful to increase the application performances.
 *
 * @package Core
 */
class Cache extends Singleton{

    /**
     * The cache instance
     *
     * @var Cache
     */
    protected static $instance;

    /**
     * Get the full path for a given cache file path, relative to CACHE_DIR
     *
     * @param string $cacheFile The path of the cache file
     *
     * @return string The full path of the cache file
     */
    public function getCacheFilePath($cacheFile){
        return CACHE_DIR . $cacheFile;
    }

    /**
     * Check if a file is cached. This function returns true if $cacheFile does not exist or if $source is newer than $cacheFile
     *
     * @param string $source    The path of the source file
     * @param string $cacheFile The path of the cache file, relative to CACHE_DIR
     *
     * @return boolean True if the file cached, else false
     */
    public function isCached($source, $cacheFile){
        return is_file($this->getCacheFilePath($cacheFile)) && filemtime($source) < filemtime($this->getCacheFilePath($cacheFile));
    }


    /**
     * Get the content of a cache file
     *
     * @param string $cacheFile The path of the cache file
     *
     * @return string The content of the cache file
     */
    public function getCacheContent($cacheFile){
        return file_get_contents($this->getCacheFilePath($cacheFile));
    }


    /**
     * Include a cache file
     *
     * @param string $cacheFile The cache file to include
     *
     * @return mixed The data returned in the cache file
     */
    public function includeCache($cacheFile){
        return include $this->getCacheFilePath($cacheFile);
    }


    /**
     * Save data in a cache file
     *
     * @param string $cacheFile The path of the cache file, relative to CACHE_DIR
     * @param string $content   The content to write in the cache file
     */
    public function save($cacheFile, $content){
        if(!is_dir(dirname($this->getCacheFilePath($cacheFile)))) {
            mkdir(dirname($this->getCacheFilePath($cacheFile)), 0755, true);
        }
        file_put_contents($this->getCacheFilePath($cacheFile), $content);
    }


    /**
     * Clear a cache file or directory
     *
     * @param string $cacheFile The cache file or directory to clear
     */
    public function clear($cacheFile = '*'){
        App::fs()->remove($this->getCacheFilePath($cacheFile));
    }



}