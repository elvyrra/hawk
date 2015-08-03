<?php
/**
 * Uplaod.class.php
 * @author Elvyrra SAS
 */



/**
 * This class permits to treat AJAX uploads 
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
		if(empty($_FILES[$name])){
			throw new UploadException();
		}

		if(is_array($_FILES[$name]['name'])){
			foreach($_FILES[$name]['name'] as $i => $data){
				$this->files[$i]  = (object) array(
					'basename' => $_FILES[$name]['name'][$i],
					'tmpFile' => $_FILES[$name]['tmp_name'][$i],
					'mime' => $_FILES[$name]['type'][$i],
					'size' => $_FILES[$name]['size'][$i],
					'extension' => pathinfo($_FILES[$name]['name'][$i], PATHINFO_EXTENSION)
				);				
			}
		}
		else{			
			$this->files[] = (object) array(
				'basename' => $_FILES[$name]['name'],
				'tmpFile' => $_FILES[$name]['tmp_name'],
				'mime' => $_FILES[$name]['type'],
				'size' => $_FILES[$name]['size'],
				'extension' => pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION)
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

		$i = 0;
		$filename = $basename;
		while(is_file($directory . '/' . $filename)){
			$filename = $i++ . '_' . $basename;
		}

		return rename($file->tmpFile, $directory . '/' . $filename);
	}
}

/**
 * This class describes the exceptions throwed by Upload class
 */
class UploadException extends Exception{}