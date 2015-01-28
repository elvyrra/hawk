<?php
/**********************************************************************
 *    						Route.class.js
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
class Route{
	private $data = array();
	
	public function __construct($url, $param){
		$this->url = $url;
		foreach($param as $key => $value){
			$this->$key = $value;
		}
		
		$this->args = array();
		$this->originalUrl = $this->url;		
		$this->url = preg_replace_callback("/\{(\w+)\}/", function($match){
			$this->args[] = $match[1];
			return "(" . $this->where[$match[1]] . ")";
		}, $this->url);
	}
	
	public function match($uri){
		if(preg_match("~^{$this->url}/?$~i", $uri, $m)){
			// The URL match, let's test the filters to access this URL are OK				
			foreach(array_slice($m, 1) as $i => $var){
				$this->setData($this->args[$i - 1], $var);
			}				
			return true;
				
		}
		return false;
	}
	
	public function getData($prop = null){
		if(!$prop)
			return $this->data;
		else
			return $this->data[$prop];
	}
	
	public function setData($key, $value){
		$this->data[$key] = $value;
	}
}