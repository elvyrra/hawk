<?php
/**
 * Less.php
 *
 * @author  Elvyrra
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class use the library lessc to compile less files into CSS, using cache system to build only if necessary
 *
 * @package Utils
 */
class Less{
    const CACHE_DIR = 'less/';

    /**
     * The source file
     */
    private $source;

    /**
     * Constructor
     *
     * @param string $source The Less source filename
     */
    public function __construct($source){
        if(!is_file($source)) {
            throw new \Exception('Impossible to compile the file ' . $source . ' : No such file');
        }

        $this->source = $source;
    }

    /**
     * Get the filename containing the information about the last compilation
     *
     * @return The filename path
     */
    private function getLastCompilationInfoFilename(){
        return 'less/' . str_replace(array(ROOT_DIR, '/'), array('', '-'), realpath($this->source)) . '.php';
    }

    /**
     * Save the result of the last compilation
     *
     * @param array $info The information array
     */
    private function saveLastCompilationInfo($info){
        App::cache()->save($this->getLastCompilationInfoFilename(), '<?php return ' . var_export($info, true) . ';');
    }

    /**
     * Build the Less file
     *
     * @param string $dest      The destination CSS file
     * @param bool   $force     If set to true, will build whereas the cache status
     * @param array  $variables Less variables to set before compiling the Less file
     */
    public function build($dest, $force = false, $variables = array()){
        if(!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        $compiler = new \lessc;


        $lastCompilationFile = App::cache()->getCacheFilePath($this->getLastCompilationInfoFilename());
        if(!$force && is_file($lastCompilationFile)) {
            $cache = include $lastCompilationFile;
        }
        else{
            $cache = $this->source;
        }

        $compiler->setFormatter('compressed');
        $compiler->setPreserveComments(false);
        $compilation = $compiler->cachedCompile($cache, $force);

        if(!is_array($cache) || $compilation['updated'] > $cache['updated']) {
            file_put_contents($dest, '/*** ' . date('Y-m-d H:i:s') . ' ***/' . PHP_EOL . $compilation['compiled']);

            App::getInstance()->trigger('built-less', array('source' => $this->source, 'dest' => $dest));

            // Save the compilation information
            unset($compilation['compiled']);
            $this->saveLastCompilationInfo($compilation);
        }
    }


    /**
     * Build a source Less file to a Css destination file
     *
     * @param string $source    The Less source filename
     * @param string $dest      The destination CSS file
     * @param bool   $force     If set to true, will build whereas the cache status
     * @param array  $variables Less variables to set before compiling the Less file
     */
    public static function compile($source, $dest, $force = false, $variables = array()){
        if(!is_file($dest)) {
            $force = true;
        }

        $less = new self($source);

        $less->build($dest, $force, $variables);
    }
}