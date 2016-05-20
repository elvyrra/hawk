<?php

/**
 * FileSystem.php
 *
 * @author  Elyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class has utilities to manage the file system, for missing functions in PHP
 *
 * @package Utils
 */
final class FileSystem extends Singleton{
    /**
     * Directory separator
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Constant to find only files in Filsystem::find
     */
    const FIND_FILE_ONLY = 'file';

    /**
     * Constant to find only directories in Filsystem::find
     */
    const FIND_DIR_ONLY = 'dir';

    /**
     * Constant to find both files and directories in Filsystem::find
     */
    const FIND_ANY_TYPE = 'any';

    /**
     * The filesystem instance
     *
     * @var FileSysteme
     */
    protected static $instance;


    /**
     * Get all files in a directory, including those beginning by '.'
     *
     * @param string $dir The directory to find files into
     *
     * @return array The list of files matching the pattern
     */
    public function getAllFilesInDir($dir){
        $files = array_merge(glob($dir . self::DS . '*'), glob($dir . self::DS . '.*'));
        return array_filter(
            $files, function ($file) {
                return basename($file) != '.' && basename($file) != '..';
            }
        );
    }

    /**
     * Equivalent to cp -r
     *
     * @param string $source The source file or directory to copy
     * @param string $dest   The destination file or directory
     */
    public function copy($source, $dest){
        if(basename($source) == '*') {
            foreach($this->getAllFilesInDir(dirname($source)) as $element){
                $this->copy($element, $dest);
            }
        }
        else{
            if(! file_exists($source)) {
                throw new FileSystemException('Cannot copy ' . $source . ' : No such file or directory');
            }

            if(is_file($source)) {
                // Copy a file
                if(is_dir($dest)) {
                    $dest = $dest . self::DS . basename($source);
                }
                copy($source, $dest);
            }
            else{
                // Copy a directory
                $base = basename($source);
                if(!is_dir($dest . self::DS . $base)) {
                    mkdir($dest . self::DS . $base, fileperms($source), true);
                }

                // Copy all files and folder under this directory
                foreach(glob($source . self::DS . '*') as $element){
                    $this->copy($element, $dest . self::DS . $base);
                }
            }
        }
    }


    /**
     * Find files by a pattern
     *
     * @param string $source  The directory to search in
     * @param string $pattern The pattern to find the files
     * @param string $type    The type of source to find : 'file', 'dir', 'any'
     *
     * @return array The list of files or directories found
     */
    public function find($source, $pattern, $type = self::FIND_ANY_TYPE){
        if(!is_dir($source)) {
            throw new FileSystemException(
                'The method ' . __METHOD__ . ' requires the first argument to be an existing directory : ' .
                $source . ' is not a directory'
            );
        }
        switch($type){
            case self::FIND_FILE_ONLY :
                $result = array_filter(glob($source . self::DS . $pattern), 'is_file');
                break;

            case self::FIND_DIR_ONLY :
                $result = glob($source . self::DS . $pattern, GLOB_ONLYDIR);
                break;

            default :
                $result = glob($source . self::DS . $pattern);
                break;
        }

        $subdirs = glob($source . self::DS . '*', GLOB_ONLYDIR);
        foreach($subdirs as $dir){
            $result = array_merge($result, self::find($dir, $pattern, $type));
        }

        return $result;
    }


    /**
     * Remove a directory or a file
     *
     * @param string $source The file or directory name to remove
     *
     * @return boolean, TRUE if the source was removed, else FALSE
     */
    public function remove($source){
        if(basename($source) == '*') {
            foreach(self::getAllFilesInDir(dirname($source)) as $element){
                $this->remove($element);
            }
        }
        else{
            if(! file_exists($source)) {
                throw new FileSystemException('Cannot remove ' . $source . ' : No such file or directory');
            }

            if(is_file($source)) {
                // remove a file
                return unlink($source);
            }
            else{
                // remove a directory
                foreach(self::getAllFilesInDir($source) as $element){
                    $this->remove($element);
                }
                return rmdir($source);
            }
        }
    }
}