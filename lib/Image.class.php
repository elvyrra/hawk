<?php

class Image{
	protected $width, $height, $type, $mime, $filename;
	
	private static $types = array(
		IMAGETYPE_GIF => 'GifImage',		
		IMAGETYPE_PNG => 'PngImage',		
		IMAGETYPE_JPEG => 'JpegImage'
	);
	
	public static function getInstance($filename){
		$info = getimagesize($filename);
		$data = array(
			'width' => $info[0],
			'height' => $info[1],
			'type' => $info[2],
			'mime' => $info['mime']		
		);
		if(!isset(self::$types[$data['type']])){
			throw new ImageException("The type of the file $filename is not supported");
		}			
		
		$class = self::$types[$data['type']];		
		return new $class($filename, $data);
	}
	
	protected function __construct($filename, $data){
		$this->filename = $filename;
		foreach($data as $key => $value){
			$this->$key = $value;
		}
	}
	public function getFilename(){
		return $this->filename;
	}
	
	public function getWidth(){
		return $this->width;
	}
	
	public function getHeight(){
		return $this->height;
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function getMimeType(){
		return $this->mime;
	}
	
	public function getFileSize(){
		return filesize($this->filename);
	}
	
	public function getExtension(){
		return image_type_to_extension($this->type);
	}
	
	public function resize($width, $height, $filename){
		$fs = $this->createResource();
		$fd = imagecreatetruecolor($width, $height);
		
		imagecopyresampled($fd, $fs, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		$this->save($fd, $filename);
		return self::getInstance($filename);
	}	
}

class PngImage extends Image{
	private function createResource(){
		return imagecreatefrompng($this->filename);
	}
	
	private function save($resource, $filename, $compression = 0){
		imagepng($resource, $filename, $compression);
	}
	
	public function compress($rate, $filename){
		$fs = $this->createResource();
		$compression = ceil(-8 / 100 * $rate + 9);
		$this->save($fs, $filename, $compression);
		return Image::getInstance($filename);
	}
}

class JpegImage extends Image{
	private function createResource(){
		return imagecreatefromjpeg($this->filename);
	}
	
	private function save($resource, $filename, $quality = 100){
		imagejpeg($resource, $filename, $quality);
	}
	
	public function compress($rate, $filename){
		$fs = $this->createResource();
		$quality = 100 - $rate;
		$this->save($fs, $filename, $quality);
		return Image::getInstance($filename);
	}
}

class GifImage extends Image{
	private function createResource(){
		return imagecreatefromgif($this->filename);
	}
	
	private function save($resource, $filename){
		imagegif($resource, $filename);
	}
	
	public function compress($rate, $filename){
		$fs = $this->createResource();		
		$this->save($fs, $filename);
		return Image::getInstance($filename);
	}
}

class ImageException extends Exception{}