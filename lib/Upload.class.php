<?php

class Upload{
	private $files = array();

	public function getInstance($name){
		try{
			return new self($name);
		}
		catch(UploadException $e){
			return null;
		}
	}

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


	public function getFiles(){
		return $this->files;
	}

	public function getFile($index = 0){
		return $this->files[$index];
	}

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

class UploadException extends Exception{}