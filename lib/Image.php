<?php
/**
 * Image.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to treat images. This class can resize images, or compress them
 *
 * @package Utils
 */
abstract class Image{
    /**
     * The width of the image
     */
    protected $width,

    /**
     * The height of the image
     */
    $height,

    /**
     * The type of the image : IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG
     */
    $type,

    /**
     * The mime type of the image
     */
    $mime,

    /**
     * The filename of the image
     */
    $filename;

    /**
     * The allowed types of images
     */
    private static $types = array(
        IMAGETYPE_GIF => 'GifImage',
        IMAGETYPE_PNG => 'PngImage',
        IMAGETYPE_JPEG => 'JpegImage'
    );


    /**
     * Create a new Image instance
     *
     * @param string $filename The file path of the image
     *
     * @return Image The image instance
     */
    public static function getInstance($filename){
        $info = getimagesize($filename);
        $data = array(
        'width' => $info[0],
        'height' => $info[1],
        'type' => $info[2],
        'mime' => $info['mime']
        );
        if(!isset(self::$types[$data['type']])) {
            throw new ImageException("The type of the file $filename is not supported");
        }

        $class = self::$types[$data['type']];
        return new $class($filename, $data);
    }


    /**
     * Constructor
     *
     * @param string $filename The file path of the image
     * @param array  $data     The data of the file (width, height, type, mime type)
     */
    protected function __construct($filename, $data){
        $this->filename = $filename;
        foreach($data as $key => $value){
            $this->$key = $value;
        }
    }

    /**
     * Return the filename of the image
     *
     * @return string The file path of the image
     */
    public function getFilename(){
        return $this->filename;
    }

    /**
     * Return the width of the image
     *
     * @return int The width (in pixels) of the image
     */
    public function getWidth(){
        return $this->width;
    }


    /**
     * Return the height of the image
     *
     * @return int The height (in pixels) if the image
     */
    public function getHeight(){
        return $this->height;
    }


    /**
     * Return the type of the image
     *
     * @return int The type of the image :  IMAGETYPE_GIF, IMAGETYPE_PNG, or IMAGETYPE_JPEG
     */
    public function getType(){
        return $this->type;
    }


    /**
     * Return the mime type of the image
     *
     * @return string The mime type of the image
     */
    public function getMimeType(){
        return $this->mime;
    }


    /**
     * Return the file size of the image
     *
     * @return int The file size (in bytes) of the image
     */
    public function getFileSize(){
        return filesize($this->filename);
    }


    /**
     * Return the file extension of the image, depending on the type of the image, not the image file
     *
     * @return string The extension of the image
     */
    public function getExtension(){
        return image_type_to_extension($this->type);
    }


    /**
     * Resize the image
     *
     * @param int    $width    The new width of the image (in pixels)
     * @param int    $height   The new height of the image (in pixels)
     * @param string $filename The path of the file to save the resized image
     *
     * @return Image The new resized image
     */
    public function resize($width, $height, $filename){
        $fs = $this->createResource();
        $fd = imagecreatetruecolor($width, $height);

        imagecopyresampled($fd, $fs, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        $this->save($fd, $filename);
        return self::getInstance($filename);
    }

    /**
     * Compress the image
     *
     * @param float  $rate     The rate compression, between 0 and 1
     * @param string $filename The path where to save the new compressed file
     *
     * @return Image The compressed image
     */
    abstract public function compress($rate, $filename);
}


/**
 * This class describes the actions on image specific to PNG images
 *
 * @package Utils
 */
class PngImage extends Image{
    /**
     * Create a image resource to be treated
     *
     * @return resource The created resource
     */
    private function createResource(){
        return imagecreatefrompng($this->filename);
    }

    /**
     * Save the image in a file
     *
     * @param resource $resource    The image resource to save
     * @param string   $filename    The file path where to save the image
     * @param int      $compression The compression to apply
     */
    private function save($resource, $filename, $compression = 0){
        imagepng($resource, $filename, $compression);
    }


    /**
     * Compress the image
     *
     * @param float  $rate     The rate compression, between 0 and 1
     * @param string $filename The path where to save the new compressed file
     *
     * @return Image The compressed image
     */
    public function compress($rate, $filename){
        $fs = $this->createResource();
        $compression = ceil(-8 / 100 * $rate + 9);
        $this->save($fs, $filename, $compression);
        return Image::getInstance($filename);
    }
}



/**
 * This class describes the actions on image specific to JPEG images
 *
 * @package Utils
 */
class JpegImage extends Image{
    /**
     * Create a image resource to be treated
     *
     * @return resource The created resource
     */
    private function createResource(){
        return imagecreatefromjpeg($this->filename);
    }


    /**
     * Save the image in a file
     *
     * @param resource $resource The image resource to save
     * @param string   $filename The file path where to save the image
     * @param int      $quality  The quality to apply
     */
    private function save($resource, $filename, $quality = 100){
        imagejpeg($resource, $filename, $quality);
    }


    /**
     * Compress the image
     *
     * @param float  $rate     The rate compression, between 0 and 1
     * @param string $filename The path where to save the new compressed file
     *
     * @return Image The compressed image
     */
    public function compress($rate, $filename){
        $fs = $this->createResource();
        $quality = 100 - $rate;
        $this->save($fs, $filename, $quality);

        return Image::getInstance($filename);
    }
}



/**
 * This class describes the actions on image specific to GIF images
 *
 * @package Utils
 */
class GifImage extends Image{
    /**
     * Create a image resource to be treated
     *
     * @return resource The created resource
     */
    private function createResource(){
        return imagecreatefromgif($this->filename);
    }

    /**
     * Save the image in a file
     *
     * @param resource $resource The image resource to save
     * @param string   $filename The file path where to save the image
     */
    private function save($resource, $filename){
        imagegif($resource, $filename);
    }


    /**
     * Compress the image
     *
     * @param float  $rate     The rate compression, between 0 and 1
     * @param string $filename The path where to save the new compressed file
     *
     * @return Image The compressed image
     */
    public function compress($rate, $filename){
        $fs = $this->createResource();
        $this->save($fs, $filename);
        return Image::getInstance($filename);
    }
}