<?php
/**
 * Uplaod.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class permits to treat AJAX uploads 
 * @package Core
 */
class Upload{
	/**
	 * The uploaded files
	 */
	private $files = array();

	/**
	 * Get an upload instance by the name of the uplaod
	 * @param string $name the name of the upload
	 * @return Upload The upload instance
	 */
	public static function getInstance($name){
		try{
			return new self($name);
		}
		catch(UploadException $e){
			return null;
		}
	}

	/**
	 * Constructor
	 * @param string $name the name of the upload
	 */
	private function __construct($name){
		$files = Request::getFiles();
		if(empty($files[$name])){
			throw new UploadException();
		}

		if(is_array($files[$name]['name'])){
			foreach($files[$name]['name'] as $i => $data){
				if(!is_file($files[$name]['tmp_name'][$i])){
					throw new UploadException();
				}

				$this->files[$i]  = (object) array(
					'basename' => $files[$name]['name'][$i],
					'tmpFile' => $files[$name]['tmp_name'][$i],
					'mime' => $files[$name]['type'][$i],
					'size' => $files[$name]['size'][$i],
					'extension' => pathinfo($files[$name]['name'][$i], PATHINFO_EXTENSION)
				);				
			}
		}
		else{
			if(!is_file($files[$name]['tmp_name'])){
				throw new UploadException();
			}

			$this->files[] = (object) array(				
				'basename' => $files[$name]['name'],
				'tmpFile' => $files[$name]['tmp_name'],
				'mime' => $files[$name]['type'],
				'size' => $files[$name]['size'],
				'extension' => pathinfo($files[$name]['name'], PATHINFO_EXTENSION)
			);
		}
	}

	/**
	 * Get the uploaded files
	 * @return array The uploaded files, where each element is a StdClass instance containing the properties : basename, tmpFile, mime, size and extension	  
	 */
	public function getFiles(){
		return $this->files;
	}

	/**
	 * Get one of the uploaded files. 
	 * @param int $index The index of the uploaded files to get. If not set, this function will return the first (or the only one) uploaded file
	 * @return StdClass The uploaded file at the given index
	 */
	public function getFile($index = 0){
		return $this->files[$index];
	}

	/**
	 * Move a uploaded file to a directory
	 * @param StdClass $file The file to move
	 * @param string $directory The directory where to move the file
	 * @param string $basename The basename to apply for the moved file
	 */
	public function move($file, $directory, $basename = null){
		if($basename === null){
			$basename = $file->basename;
		}

		return move_uploaded_file($file->tmpFile, $directory . '/' . $basename);
	}
}

/**
 * This class describes the exceptions throwed by Upload class
 */
class UploadException extends \Exception{}
