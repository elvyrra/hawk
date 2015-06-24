<?php

/**
 * This class describes the behavior of views
 */
class View{	
	private $file, $content, $data, $type;
	private $cached = false;
	
	const PLUGINS_VIEW = 'view-plugins/';

	const IMPORT_REGEX = '#\{\s*import\s*(["\'])(.+?)\\1\s*\}#is';
	const BLOCK_START_REGEX = '#\{(if|elseif|else|for|foreach|while)\s*(\(.+?\))?\s*\}#i';
	const BLOCK_END_REGEX = '#\{\/(if|for|foreach|while)\s*\}#is';
	const ECHO_REGEX = '#\{{2}\s*(.+?)\}{2}#is';

	const PLUGIN_REGEX = '#\{(\w+)((\s+\w+\=([\'"])((?:\\\"|.)*?)\\4)*)\s*\}#';
	const PLUGIN_ARGUMENTS_REGEX = '#(\w+)\=([\'"])(\{?)(.*?)(\}?)\\2#';
	
	
	/**
	 * Constructor
	 * @constructs
	 * @param strin $file The template file to parse
	 */
	public function __construct($file){
		/*** The selector can be a filename or a plain/html text ***/
		if(!is_file($file)){
			throw new ViewException(ViewException::TYPE_FILE_NOT_FOUND, $file);
		}
		
		$this->file = $file;
		
		$this->fileCache = new FileCache($this->file, 'views', 'php');			
		if(! $this->fileCache->isCached()){
			$this->content = file_get_contents($file);				
			$this->parse();
			$this->fileCache->set($this->content);
		}
		
		$this->include = $this->fileCache->get();
	}
	

	/**
	 * Set data to display in the view
	 * @param array The data to insert in the view. The keys of the data will become variables in the view
	 * @return void
	 */
	public function set($data = array()){
		$this->data = $data;
		return $this;
	}
	

	/**
	 * Add data to display in the view
	 * @param array The data to add in the view
	 * @return void
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
		$this->content = $this->content;	

		// Import sub templates
		while(preg_match(self::IMPORT_REGEX, $this->content)){			
			$this->content = preg_replace_callback($reg, function($m){
				$file = $m[2]{0} == '/' ? ROOT_DIR : dirname(realpath($this->file)) . '/' . $m[2];
				
				$view = new View($file);
				return "<?php include '" . $view->fileCache->get() . "'; ?>";
			
			} , $this->content);
		}
		
		// Parse PHP Structures
		$replaces = array(
			self::BLOCK_START_REGEX => "<?php $1 $2 : ?>", // structure starts
			self::BLOCK_END_REGEX => "<?php end$1; ?>", // structures ends
			self::ECHO_REGEX => "<?php echo $1; ?>" // echo
		);		
		$this->content = preg_replace(array_keys($replaces), $replaces, $this->content);
		
		
		// Parse plugins nodes		
		$this->content = preg_replace_callback(self::PLUGIN_REGEX, function($matches){			
			list($l, $component, $arguments) = $matches;
			
			try{			
				$componentClass = 'ViewPlugin' . ucfirst($component);
				
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
			throw new ViewException(ViewException::TYPE_EVAL, $this->file, $e);
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
}




/**
 * This class describes the View exceptions
 */
class ViewException extends Exception{
	const TYPE_FILE_NOT_FOUND = 1;
	const TYPE_EVAL = 2;
	
	public function __construct($type, $file, $previous = null){
		$code = $type;
		switch($type){
			case self::TYPE_FILE_NOT_FOUND:
				$message = "Error creating a view from template file $file : No such file or directory";
			break;
			
			case self::TYPE_EVAL:
				$trace = array_map(function($t){
					return $t['file'] . ':' . $t['line'];
				}, $previous->getTrace());

				$message = "An error occured while building the view from file $file : " . $previous->getMessage() . PHP_EOL . implode(PHP_EOL, $trace);
			break;
		}
		
		parent::__construct($message, $code);
	}
}
