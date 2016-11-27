<?php
/**
 * Import.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to import a view file in another view.
 * Importation will transfer all the view context (variables) in the imported view
 * Exemple :
 * <code>{import file="{$filename}"}</code>
 *
 * @package View\Plugins
 */
class Import extends \Hawk\ViewPlugin{
    /**
     * The id of the form to display
     *
     * @var string
     */
    public $file;

    /**
     * Import the file
     */
    public function display(){
        if($this->file{0} == '/') {
            // Absolute path
            $basedir = ROOT_DIR;
            $this->file = preg_replace('#^' . ROOT_DIR . '#', '', $this->file);
        }
        else{
            // Path relative to the view that included the file
            $basedir = dirname(realpath($this->viewFile));
        }

        $data = $this->viewData;

        foreach(get_object_vars($this) as $key => $value) {
            if($key !== 'file') {
                $data[$key] = $value;
            }
        }

        return \Hawk\View::make($basedir . '/' . $this->file, $data);
    }
}