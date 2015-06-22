<?php

/**
 * This class describes the behavior of views
 */
class View{	
	private $file, $content, $data, $type;
	private $cached = false;
	
	const PLUGINS_VIEW = 'view-plugins/';
	
	/*_______________________________________________________
	
	    Constructor :
	    @param : 
	        o string $selector : HTML code or filename to load a template
	_______________________________________________________*/
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
	
	public function set($data = array()){
		$this->data = $data;
		return $this;
	}
	
	public function add($data = array()){
		$this->data = array_merge($this->data, $data);	
		return $this;
	}
	
	/*_______________________________________________________
	
	    Parse the template and construct the PHP code
		Format of template :
			A template is an html page, containing the following tags :
			{if(...)} => if(...) :
			{elseif(...)} => elseif(...) :
			{else} => else :
			{/if} => endif;
			{for(...)} => for(...) :
			{/for} => endfor;
			{foreach(...)} => foreach(...) :
			{/foreach} => endforeach;
			{while(...))} => while(...) :
			{/while} => endwhile;
			{{ ... }} => echo ... ;
			{include ...} => include ...;
	_______________________________________________________*/
	private function parse(){
		$this->content = $this->content;	

		// Import sub templates
		$reg = "#\{\s*import\s*([\"'])(.+?)\\1\s*\}#is";
		while(preg_match($reg, $this->content)){			
			$this->content = preg_replace_callback($reg, function($m){
				$file = $m[2]{0} == '/' ? ROOT_DIR : dirname(realpath($this->file)) . '/' . $m[2];
				
				$view = new View($file);
				return "<?php include '" . $view->fileCache->get() . "'; ?>";
			
			} , $this->content);
		}
		
		// Parse PHP Structures
		$replaces = array(
			"#\{(if|elseif|else|for|foreach|while)\s*(\(.+?\))?\s*\}#i" => "<?php $1 $2 : ?>", // structure starts
			"#\{\/(if|for|foreach|while)\s*\}#is" => "<?php end$1; ?>", // structures ends
			"#\{{2}\s*(.+?)\}{2}#is" => "<?php echo $1; ?>" // echo
		);		
		$this->content = preg_replace(array_keys($replaces), $replaces, $this->content);
		
		// Parse plugins nodes		
		$pattern = "#\{(\w+)((\s+\w+\=(['\"])((?:\\\"|.)*?)\\4)*)\s*\}#";
		$this->content = preg_replace_callback($pattern, function($matches){			
			$component = $matches[1];
			
			try{			
				$componentClass = 'ViewPlugin' . ucfirst($component);
				$arguments = $matches[2];
				$parameters = array();
				$reg = "#(\w+)\=(['\"])(\{?)(.*?)(\}?)\\2#";
				while(preg_match($reg, $arguments, $m)){
					$value = $m[3] && $m[5] ? $m[4] : $m[2] . $m[4] . $m[2];
					$parameters[] = "'{$m[1]}' => $value";
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

	/*_______________________________________________________
	
	    Replace the keys in the template and display it
	_______________________________________________________*/
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
	
	public static function make($file, $data = array()){
		$view = new self($file);
		$view->set($data);
		return $view->display();
	}	
}

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
