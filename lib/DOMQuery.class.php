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
    public function __construct($data) {
        $this->dom = new DOMDocument();
        @$this->dom->loadHTML($data);
        $this->xpath = new DOMXpath($this->dom);
    }
  
    public function find($selector) {
        $elements = $this->xpath->evaluate($this->selectorToXpath($selector));
        return new DOMQueryResult($elements);
    }
    
    public function save(){
        return $this->dom->saveHTML();
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
    private $nodes;
    
    public function __construct(DOMNodeList $nodes){
        $this->nodes = $nodes;
    }
    
    /**
     * Set or get the html content of the ndoes
     * @param String $html the content to insert. If not set, returns the html content of the first node   
     */
    public function html($html = null){
        if($html === null){
            // Get the html content of the first node
            return $this->nodes->length ? $this->nodes->item(0)->ownerDocument->saveHTML($this->nodes->item(0)) : '';
        }
        else{
            // Set the html content of all nodes
            foreach($this->nodes as $node){
                // Delete the current content of the node
                foreach($node->childNodes as $child){
                    $node->removeChild($child);
                }
                
                // Add the new content
                $f = $node->ownerDocument->createDocumentFragment();
                $f->appendXML($html);
                $node->appendChild($f);
            }
            
            return $this;
        }
    }
    
    /**
     * Set or get the text content of the ndoes
     * @param String $text the content to insert. If not set, returns the text content of the first node   
     */
	public function text($text = null){
        if($text == null){
            // Get the text content of the first node
            return $this->nodes->length ? $this->nodes->item(0)->textContent : '';
        }
        else{
            // Set the text content of all nodes
            foreach($this->nodes as $node){                
                // Delete the current content of the node
                foreach($node->childNodes as $child){
                    $node->removeChild($child);
                }
                
                // Add the new text content to the node
                $t = $node->ownerDocument->createTextNode($text);            
                $node->appendChild($t);
            }
            return $this;
        }
    }
    
    /**
     * Append Html content to the nodes
     * @param String $html the content to append     
     */
	public function append($html){
        foreach($this->nodes as $node){
            $f = $node->ownerDocument->createDocumentFragment();
            $f->appendXML($html);
            $node->appendChild($f);
        }
        
        return $this;
    }
    
    /**
     * Prepend Html content to the nodes
     * @param String $html the content to prepend     
     */
	public function prepend($html){
        foreach($this->nodes as $node){            
            $f = $node->ownerDocument->createDocumentFragment();
            $f->appendXML($html);
            
            if($node->childNodes->length)
                $node->insertBefore($f, $node->childNodes->item(0));
            else
                $node->appendChild($f);
        }
        
        return $this;
        
    }
    
    /**
     * Insert html content just after the nodes
     * @param String $html the content to insert right after     
     */
	public function after($html){
        foreach($this->nodes as $node){            
            $f = $node->ownerDocument->createDocumentFragment();
            $f->appendXML($html);
            
            if($node->nextSibling !== null){
                $node->parentNode->insertBefore($f, $node->nextSibling);
            }
            else{
                $node->parentNode->appendChild($f);
            }
        }
        
        return $this;
    }
    
    /**
     * Insert html content just after the nodes
     * @param String $html the content to insert right before
     */
	public function before($html){
        foreach($this->nodes as $node){            
            $f = $node->ownerDocument->createDocumentFragment();
            $f->appendXML($html);
            
            $node->parentNode->insertBefore($f, $node);
        }
        
        return $this;            
    }
	
    /**
     * Remove nodes     
     */    
	public function remove(){
        foreach($this->nodes as $node){                        
            $node->parentNode->removeChild($node);
        }
    }
    
    /**
     * Set attribute values or get the attribute values of the nodes
     * @param mixed $prop The property(ies) to get or set. If an array, then set multiple properties
     * @param String $value The value to set for the given property     
     */
	public function attr($prop, $value = null){
        if($value == null && is_string($prop)){
            // Get the value of the attribute of the first node
            if($this->nodes->length){
                $node = $this->nodes->item(0);
                $attribute = $node->attributes->getNamesItem($prop);
                if($attribute !== null){
                    return $attribute->nodeValue;
                }
                else{
                    return '';
                }
            }
            else{
                return '';
            }
        }
        else{
            // Set the attribute(s) value(s) to the nodes
            foreach($this->nodes as $node){                
                if(is_array($prop)){
                    // Set multiple
                    foreach($prop as $key => $val){
                        $node->setAttribute($prop, $value);
                    }
                }
                else{
                    $node->setAttribute($prop, $value);
                }
            }
            return $this;
        }
    }    
}