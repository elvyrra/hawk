<?php
/**
 * Import.php
 * @author Elvyrra SAS
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to display a form in a view
 * @package View\Plugins
 */
class Import extends \Hawk\ViewPlugin{
    /**
     * The id of the form to display
     */
    public $file;    

    /**
     * Import the file
     */
    public function display(){
        if($this->file{0} == '/'){
            // Absolute path
            $basedir = ROOT_DIR;
            $this->file = preg_replace('#^' . ROOT_DIR . '#', '', $this->file);
        }
        else{
            // Path relative to the view that included the file
            $basedir = dirname(realpath($this->viewFile));
        }

        return \Hawk\View::make( $basedir . '/' . $this->file, $this->viewData );        
    }
}