<?php
/**
 * SelectorDOM.
 *
 * Persitant object for selecting elements.
 *
 *   $dom = new SelectorDOM($html);
 *   $links = $dom->select('a');
 *   $list_links = $dom->select('ul li a');
 *
 */

class DOMQuery{
	public static $instance;
	
    public function __construct($data) {
        $this->dom = new DOMDocument();	
		$this->dom->preserveWhiteSpace = false;
        $content = $this->dom->createDocumentFragment();
        $content->appendXML($data);
        @$this->dom->append($content);
        $this->xpath = new DOMXpath($this->dom);
		self::$instance = $this;
    }
	
	public static function getInstance(){
		if(isset(self::$instance)){
			return self::$instance;
		}
		else{
			throw new DOMQueryException(DOMQueryException::NO_INSTANCE);
		}
	}
  
    public function find($selector) {
		$this->selector = $selector;		
        $elements = $this->xpath->evaluate($this->selectorToXpath($selector));
        return new DOMQueryResult($elements);
    }
    
    public function save(){
        return str_replace('<?xml encoding="' . ENCODING . '">', '', $this->dom->saveHTML());
    }
    
    private function selectorToXpath($selector) {
        // remove spaces around operators
        $selector = preg_replace('/\s*>\s*/', '>', $selector);
        $selector = preg_replace('/\s*~\s*/', '~', $selector);
        $selector = preg_replace('/\s*\+\s*/', '+', $selector);
        $selector = preg_replace('/\s*,\s*/', ',', $selector);
        $selectors = preg_split("/\s+(?![^\[]+\])/", $selector);
      
        foreach ($selectors as &$selector) {
            // ,
            $selector = preg_replace('/\s*,\s*/', '|descendant-or-self::', $selector);
            // :button, :submit, etc 
            $selector = preg_replace('/:(button|submit|file|checkbox|radio|image|reset|text|password)/', 'input[@type="\1"]', $selector);
            // [id]
            $selector = preg_replace('/\[(\w+)\]/', '*[@\1]', $selector);
            // foo[id=foo]        
            $selector = preg_replace('/\[(\w+)=[\'"]?(.*?)[\'"]?\]/', '[@\1="\2"]', $selector);
            // [id=foo]
            $selector = preg_replace('/^\[/', '*[', $selector);
              // div#foo
            $selector = preg_replace('/([\w\-]+)\#([\w\-]+)/', '\1[@id="\2"]', $selector);
            // #foo
            $selector = preg_replace('/\#([\w\-]+)/', '*[@id="\1"]', $selector);
            // div.foo
            $selector = preg_replace('/([\w\-]+)\.([\w\-]+)/', '\1[contains(concat(" ",@class," ")," \2 ")]', $selector);
            // .foo
            $selector = preg_replace('/\.([\w\-]+)/', '*[contains(concat(" ",@class," ")," \1 ")]', $selector);
            // div:first-child
            $selector = preg_replace('/([\w\-]+):first-child/', '*/\1[position()=1]', $selector);
            // div:last-child
            $selector = preg_replace('/([\w\-]+):last-child/', '*/\1[position()=last()]', $selector);
            // :first-child
            $selector = str_replace(':first-child', '*/*[position()=1]', $selector);
            // :last-child
            $selector = str_replace(':last-child', '*/*[position()=last()]', $selector);
            // :nth-last-child
            $selector = preg_replace('/:nth-last-child\((\d+)\)/', '[position()=(last() - (\1 - 1))]', $selector);
            // div:nth-child
            $selector = preg_replace('/([\w\-]+):nth-child\((\d+)\)/', '*/*[position()=\2 and self::\1]', $selector);
            // :nth-child
            $selector = preg_replace('/:nth-child\((\d+)\)/', '*/*[position()=\1]', $selector);
            // :contains(Foo)
            $selector = preg_replace('/([\w\-]+):contains\((.*?)\)/', '\1[contains(string(.),"\2")]', $selector);
            // >
            $selector = preg_replace('/\s*>\s*/', '/', $selector);
            // ~
            $selector = preg_replace('/\s*~\s*/', '/following-sibling::', $selector);
            // + 
            $selector = preg_replace('/\s*\+\s*([\w\-]+)/', '/following-sibling::\1[position()=1]', $selector);
            $selector = str_replace(']*', ']', $selector);
            $selector = str_replace(']/*', ']', $selector);
        }  
        // ' '
        $selector = implode('/descendant::', $selectors);
        $selector = 'descendant-or-self::' . $selector;
        return $selector;    
    }
}

class DOMQueryResult{
    public $nodes;
	public $length;
    
    public function __construct(DOMNodeList $nodes){
        $this->nodes = $nodes;
		$this->length = $this->nodes->length;
    }
	
	private function apply($function){
		foreach($this->nodes as $node){
			$function($node);
		}
		return $this;
	}
	
	/** 
	 * Find sub elements
	 * @param String $selector, the "sub selector" to find elements
	 */
	public function find($selector){
		$instance = DomQuery::getInstance();
		return $instance->find($instance->selector . " " . $selector);
	}
	
	/**
	 * Filter the found elements
	 * @param string $selector, the selector to filter the results
	 */
	public function filter($selector){
		$instance = DomQuery::getInstance();
		return $instance->find($instance->selector . $selector);
	}
	
	/**
	 * Get the x th element of the result set
	 * @param int $index, the index of the element to get
	 * @return DOMNode
	 */
	public function get($index){
		return $this->nodes->get($index);
	}
    
	public function first(){
        return new self(new DOMNodeList($this->nodes->item(0)));
		//return $this->filter(':first-child');
	}
	
	public function last(){
        return new self(new DOMNodeList($this->nodes->item($this->nodes->length - 1)));
		//return $this->filter(':last-child');		
	}
	
    /**
     * Set or get the html content of the ndoes
     * @param String $html the content to insert. If not set, returns the html content of the first node   
     */
    public function html($html = null){
        if($html === null){
            // Get the html content of the first node
            if(!$this->length){
				return '';
			}
			
			$html = ""; 
			$firstNode = $this->nodes->item(0);
			$children  = $firstNode->childNodes;

			foreach ($children as $child) { 
				$html .= $firstNode->ownerDocument->saveHTML($child);
			}			
			return trim($html);
        }
        else{
			// Set HTML content 						
			return $this->apply(function($node) use($html){				
				// Delete the current content of the node	
				while($node->childNodes->length){
					$node->removeChild($node->firstChild);
				}                
                
                // Add the new content
                $f = $node->ownerDocument->createDocumentFragment();
                $f->appendXML($html);
                $node->appendChild($f);				
			});            
        }
    }	
    
    /**
     * Set or get the text content of the ndoes
     * @param String $text the content to insert. If not set, returns the text content of the first node   
     */
	public function text($text = null){
        if($text == null){
            // Get the text content of the first node
            return $this->length ? trim($this->nodes->item(0)->textContent) : '';
        }
        else{
			// Set the text content of all nodes
			return $this->apply(function($node) use($text){
				// Delete the current content of the node	
				while($node->childNodes->length){
					$node->removeChild($node->firstChild);
				}  
                
                // Add the new text content to the node
                $t = $node->ownerDocument->createTextNode($text);            
                $node->appendChild($t);
				
			});            
        }
    }
    
    /**
     * Append Html content to the nodes
     * @param String $html the content to append     
     */
	public function append($html){
        return $this->apply(function($node) use($html){
			$f = $node->ownerDocument->createDocumentFragment();
            $f->appendXML($html);
            $node->appendChild($f);	
		});		
    }
    
    /**
     * Prepend Html content to the nodes
     * @param String $html the content to prepend     
     */
	public function prepend($html){
		return $this->apply(function($node) use($html){        
            $f = $node->ownerDocument->createDocumentFragment();
            $f->appendXML($html);
            
            if($node->childNodes->length)
                $node->insertBefore($f, $node->childNodes->item(0));
            else
                $node->appendChild($f);
        });
    }
    
    /**
     * Insert html content just after the nodes
     * @param String $html the content to insert right after     
     */
	public function after($html){
		return $this->apply(function($node) use($html){        
            $f = $node->ownerDocument->createDocumentFragment();
            $f->appendXML($html);
            
            if($node->nextSibling !== null){
                $node->parentNode->insertBefore($f, $node->nextSibling);
            }
            else{
                $node->parentNode->appendChild($f);
            }
        });
    }
    
    /**
     * Insert html content just after the nodes
     * @param String $html the content to insert right before
     */
	public function before($html){
		return $this->apply(function($node) use($html){	
            $f = $node->ownerDocument->createDocumentFragment();
            $f->appendXML($html);
            
            $node->parentNode->insertBefore($f, $node);
        });
    }
	
    /**
     * Remove nodes     
     */    
	public function remove(){
        $this->apply(function($node){
            $node->parentNode->removeChild($node);
        });
    }
    
    /**
     * Set attribute values or get the attribute values of the nodes
     * @param mixed $prop The property(ies) to get or set. If an array, then set multiple properties
     * @param String $value The value to set for the given property     
     */
	public function attr($prop, $value = null){
        if($value == null && is_string($prop)){
            // Get the value of the attribute of the first node
            if($this->length){
                return $this->nodes->item(0)->getAttribute($prop);                
            }
            else{
                return '';
            }
        }
        else{
            // Set the attribute(s) value(s) to the nodes
            return $this->apply(function($node) use($prop, $value){
                if(is_array($prop)){
                    // Set multiple
                    foreach($prop as $key => $val){
                        $node->setAttribute($prop, $value);
                    }
                }
                else{
                    $node->setAttribute($prop, $value);
                }
            });
        }
    } 

	/**
	 * Add class to the node 
	 */
	public function addClass($classes){			
		$nodeClass = $this->attr('class');
		if($nodeClass){
			$classes = explode(" ", $classes);
			foreach($classes as $class){
				if(!preg_match("#\b$class\b#", $classes)){
					$nodeClass .= " $class";
				}
			}
			return $this->attr('class', $nodeClass);		
		}
		else{
			return $this->attr('class', $classes);
		}
	}
	
	/**
	 * Remove a class to the node
	 */
	public function removeClass($classes){
		$classes = explode(" ", $classes);
		$nodeClass = $this->attr('class');
		foreach($classes as $class){
			$nodeClass = preg_replace("#\b$class\b#", "", $nodeClass);
		}
		return $this->attr('class', $nodeClass);
	}
	
	/**
	 * Add css property to a node
	 */
	public function css($prop, $value = null){
		$css = $this->attr('style');
		$reg = "#\b($prop)\s*\:\s*(.+?)\s*(\;|$)#";
		
		if($value == null && is_string($prop)){
			// Get the css property of the first node			
			if(preg_match($reg, $css, $matches)){				
				return $matches[2];
			}
			else{
				return '';
			}
		}
		else{
			// Set the css properties to the nodes
			$this->apply(function($node) use($prop, $value){
				if(is_array($prop)){
					foreach($prop as $key => $value){
						$this->css($key, $value);
					}
				}
				else{
					$insert = "$prop: $value;";
					if($css){
						if(preg_match($reg, $css, $matches)){
							debug('ici');
							$css = preg_replace($reg, $insert, $css);
						}
						else{
							debug('la');
							$css .= ($css{strlen($css) - 1} == ';' ? '' : ';') . $insert;
						}
					}
					else{
						$css = $insert;
					}
					return $this->attr('style', $css);
				}				
			});
		}
	}
	
	public function hide(){
		$this->css('display', 'none');
	}
	
	public function show(){
		$this->css('display', 'block');
	}
	
	public function outerHtml(){
		// Get the html content of the first node
		if(!$this->length){
			return '';
		}
			
		return $this->nodes->item(0)->ownerDocument->saveHTML($this->nodes->item(0));						
	}
	
	
}

class DOMQueryException extends Exception{
	const NO_INSTANCE = 1;
	public function __construct($code){
		switch($code){
			case self::NO_INSTANCE :
				$message = "No DOMQuery instance is created yet";
			break;
		}
		
		parent::__construct($message, $code);
	}
}