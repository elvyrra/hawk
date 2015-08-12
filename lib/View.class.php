<?php
/**
 * View.class.php
 * @author Elvyrra SAS
 */
 

/**
 * This class describes the behavior of views
 * @package View
 */
class View{	
	/**
	 * The source file of the view
	 */
	private $file, 

	/**
	 * The content of the source template
	 */
	$content, 

	/**
	 * The data injected in the view for generation
	 */
	$data,

	/**
	 * Defines if the view already cached or not
	 */
	$cached = false,

	/**
	 * The other views this views depends on
	 */
	$dependencies = array();
	
	const PLUGINS_VIEW = 'view-plugins/';

	const IMPORT_REGEX = '#\{\s*import\s*(["\'])(.+?)\\1\s*\}#is';
	const BLOCK_START_REGEX = '#\{(if|elseif|else|for|foreach|while)\s*(\(.+?\))?\s*\}#i';
	const BLOCK_END_REGEX = '#\{\/(if|for|foreach|while)\s*\}#is';
	const ECHO_REGEX = '#\{{2}\s*(.+?)\}{2}#is';

	const ASSIGN_REGEX = '#\{assign\s+name=(["\'])(\w+)\\1\s*\}(.*?)\{\/assign\}#ims';

	const PLUGIN_REGEX = '#\{(\w+)((\s+\w+\=([\'"])((?:\\\"|\\\'|.)*?)\\4)*)\s*\}#';
	const PLUGIN_ARGUMENTS_REGEX = '#(\w+)\=([\'"])(\{?)((?:\\\"|\\\'|.)*?)(\}?)\\2#';
	
	
	/**
	 * Constructor
	 * @param string $file The template file to parse
	 */
	public function __construct($file){
		/*** The selector can be a filename or a plain/html text ***/
		if(!is_file($file)){
			throw new ViewException(ViewException::TYPE_FILE_NOT_FOUND, $file);
		}
		
		$this->file = $file;
		
		$this->fileCache = new FileCache($this->file, 'views');

		$this->content = file_get_contents($file);				
		$this->getDependencies();
		if(! $this->fileCache->isCached()){
			$this->parse();
			$this->fileCache->set($this->content);
		}
		
		$this->include = $this->fileCache->get();
	}
	

	/**
	 * Get template dependencies
	 */
	private function getDependencies(){
		preg_match_all(self::IMPORT_REGEX, $this->content, $matches, PREG_SET_ORDER);
		foreach($matches as $match){
			$file = $match[2];
			$this->dependencies[$file] = new View($file{0} == '/' ? ROOT_DIR : dirname(realpath($this->file)) . '/' . $file);
		}
	}

	/**
	 * Set data to display in the view
	 * @param array The data to insert in the view. The keys of the data will become variables in the view
	 * @return View The view itself
	 */
	public function set($data = array()){
		$this->data = $data;
		return $this;
	}
	

	/**
	 * Add data to display in the view
	 * @param array The data to add in the view
	 * @return View The view itself
	 */
	public function add($data = array()){
		$this->data = array_merge($this->data, $data);	
		return $this;
	}
	

	/**
	 * Parse the view into a PHP file
	 * @return View The view itself, to permit chained expressions
	 */
	private function parse(){
		// Import sub templates		
		$this->content = preg_replace_callback(self::IMPORT_REGEX, function($m){
			$view = $this->dependencies[$m[2]];
			
			return "<?php include '" . $view->fileCache->get() . "'; ?>";
		
		} , $this->content);
		
		// Parse PHP Structures
		$replaces = array(
			self::BLOCK_START_REGEX => "<?php $1 $2 : ?>", // structure starts
			self::BLOCK_END_REGEX => "<?php end$1; ?>", // structures ends
			self::ECHO_REGEX => "<?php echo $1; ?>", // echo
			self::ASSIGN_REGEX => "<?php ob_start(); ?>$3<?php \$$2 = ob_get_clean(); ?>" // assign template part in variable
		);		
		$this->content = preg_replace(array_keys($replaces), $replaces, $this->content);
		
		
		// Parse plugins nodes		
		$this->content = preg_replace_callback(self::PLUGIN_REGEX, function($matches){			
			list($l, $component, $arguments) = $matches;
			$componentClass = 'ViewPlugin' . ucfirst($component);
			
			if(!class_exists($componentClass)){
				return $matches[0];
			}
			try{			
				
				$parameters = array();

				while(preg_match(self::PLUGIN_ARGUMENTS_REGEX, $arguments, $m)){
					$name= $m[1];
					if($m[3] && $m[5]){
						// That is a PHP expression to evaluate
						$value = $m[4];
					}
					else{
						// The value is a static string
						$value = $m[2] . $m[4] . $m[2];
					}
					
					$parameters[] = "'" . $name . "' => " . $value;
					
					// Remove the argument from the arguments list
					$arguments = str_replace($m[0], '', $arguments);
				}				
				
				return '<?php $instance = new ' . $componentClass . '( array(' . implode(',',$parameters) . ') ); echo $instance->display(); ?>';
			}
			catch(Exception $e){
				return $matches[0];
			}
		}, $this->content);
		
		return $this;
	}


	/**
	 * Replace the keys in the template and display it
	 * @return string The HTML result of the view, applied with the data
	 */
	public function display(){
		extract($this->data);
		ob_start();
		try{
			include $this->fileCache->getFile();
		}
		catch(Exception $e){
			$exception = new ViewException(ViewException::TYPE_EVAL, $this->file, $e);

			Response::set($exception->display());
			Response::end();
		}		
		return ob_get_clean();
	}	
	

	/**
	 * Generate a view result
	 * @param string $file The filename of the template
	 * @param array $data The data to apply to the view
	 * @return string The HTML result of the view
	 */
	public static function make($file, $data = array()){
		$view = new self($file);
		$view->set($data);
		return $view->display();
	}	


	/**
	 * Generate a view from a string
	 * @param string $content The string of the origin template
	 * @param array $data The data to apply to the view
	 * @return string The HTML result of the string
	 */
	public static function makeFromString($content, $data = array()){
		$file = tempnam('', '');
		file_put_contents($file, $content);

		$result = self::make($file, $data);

		unlink($file);

		return $result;
	}
}




/**
 * This class describes the View exceptions
 */
class ViewException extends Exception{
	const TYPE_FILE_NOT_FOUND = 1;
	const TYPE_EVAL = 2;
	
	/**
	 * Constructor
	 * @param int $type The type of exception 
	 * @param string $file The source file that caused this exception	 
	 * @param Exception $previous The previous exception that caused that one
	 */
	public function __construct($type, $file, $previous = null){
		$code = $type;
		switch($type){
			case self::TYPE_FILE_NOT_FOUND:
				$message = "Error creating a view from template file $file : No such file or directory";
			break;
			
			case self::TYPE_EVAL:
				debug($previous);
				$trace = array_map(function($t){
					return $t['file'] . ':' . $t['line'];
				}, $previous->getTrace());

				$message = "An error occured while building the view from file $file : " . $previous->getMessage() . PHP_EOL . implode(PHP_EOL, $trace);
			break;
		}
		
		parent::__construct($message, $code, $previous);
	}
}
