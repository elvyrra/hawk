<?php
/**********************************************************************
 *    						View.class.js
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 * Description : This class is used to create a DOM template and
 *                  manipulate the DOM (add nodes, replace attribues, ... etc)
 *
 **********************************************************************/
class View{	
	private $file, $content, $data, $type;
	private $cached = false;
	
	const CACHE_NO_USE = false;
	const CACHE_USE = true;
	const PLUGINS_VIEW = 'view-plugins/';
	
	/*_______________________________________________________
	
	    Constructor :
	    @param : 
	        o string $selector : HTML code or filename to load a template
	_______________________________________________________*/
	public function __construct($file, $cache = self::CACHE_USE){
		/*** The selector can be a filename or a plain/html text ***/
		if(!is_file($file)){
			throw new ViewException(ViewException::TYPE_FILE_NOT_FOUND, $file);
		}
		
		$this->file = $file;
		
		$this->fileCache = new FileCache($this->file, 'views', 'php');			
		if($this->fileCache->isCached()){
			/*** The cached file exists and is earlier than the source we can load it ***/				
			$this->phpcode = file_get_contents($this->fileCache->get());
		}
		else{
			$this->content = file_get_contents($file);				
			$this->parse();
			if($cache){
				$this->fileCache->set($this->phpcode);					
			}
		}			
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
			{endif} => endif;
			{for(...)} => for(...) :
			{endfor} => endfor;
			{foreach(...)} => foreach(...) :
			{endforeach} => endforeach;
			{while(...))} => while(...) :
			{endwhile} => endwhile;
			{{ ... }} => echo ... ;
			{include ...} => include ...;
	_______________________________________________________*/
	private function parse(){
		$this->phpcode = $this->content;	

		// Import sub templates
		$reg = "#\{\s*import\s*([\"'])(.+?)\\1\s*\}#is";
		while(preg_match($reg, $this->phpcode)){			
			$this->phpcode = preg_replace_callback($reg, function($m){
				$file = $m[2]{0} == '/' ? ROOT_DIR : dirname(realpath($this->file)) . '/' . $m[2];
				
				$view = new View($file);
				return "<?php include '" . $view->fileCache->get() . "'; ?>";
			
			} , $this->phpcode);
		}
		
		// Parse PHP Structures
		$replaces = array(
			"#\{(if|elseif|else|for|foreach|while)\s*(\(.+?\))?\s*\}#i" => "<?php $1 $2 : ?>", // structure starts
			"#\{\/(if|for|foreach|while)\s*\}#is" => "<?php end$1; ?>", // structures ends
			"#\{{2}\s*(.+?)\}{2}#is" => "<?php echo $1; ?>" // echo
		);		
		$this->phpcode = preg_replace(array_keys($replaces), $replaces, $this->phpcode);
		
		// Parse plugins nodes		
		$pattern = "#\{(\w+)((\s+\w+\=(['\"])(.*?)\\4)*)\s*\}#";
		$this->phpcode = preg_replace_callback($pattern, function($matches){
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
		}, $this->phpcode);
		
		return $this;
	}

	/*_______________________________________________________
	
	    Replace the keys in the template and display it
	_______________________________________________________*/
	public function display(){
		extract($this->data);
		ob_start();
		$result = eval(' ?>' . $this->phpcode);
		$errors = error_get_last();
		if($result === false && !empty($errors)){
			throw new ViewException(ViewException::TYPE_EVAL, $this->file, error_get_last());
		}
		return ob_get_clean();
	}	
	
	public static function make($file, $data = array(), $cache = self::CACHE_USE ){
		$view = new self($file, $cache);
		$view->set($data);
		return $view->display();
	}	
}

class ViewException extends Exception{
	const TYPE_FILE_NOT_FOUND = 1;
	const TYPE_EVAL = 2;
	
	public function __construct($type, $file, $message = ""){
		$code = $type;
		switch($type){
			case self::TYPE_FILE_NOT_FOUND:
				$message = "Error creating a view from template file $file : No such file or directory";
			break;
			
			case self::TYPE_EVAL:
				$message = "An error occured while building the view from file $file : " . implode(PHP_EOL, $message);
			break;
		}
		
		parent::__construct($message, $code);
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/