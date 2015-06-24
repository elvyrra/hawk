<?php
/**********************************************************************
 *    						record.event.listener.js
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
class ItemList{
	/*** Class constants ***/	
	const DEFAULT_MODEL = 'GenericModel';
	const ALL_LINES = 'all';
	public static $lineChoice = array(10, 20, 50, 100);
	const DEFAULT_LINE_CHOICE = 20;

	
	/*** Instance default values ***/
	public 	$controls = array(),
			$fields = array(),
			$searches = array(),
			$sorts = array(),
			$binds = array(),
			$lines = self::DEFAULT_LINE_CHOICE,
			$page = 1,
			$checkbox = false,
			$action,
			$model = self::DEFAULT_MODEL,
			$emptyMessage;
	private $dbo;
			
	/*______________________________________________________________________
	
		CONTRUCTOR : INIT THE ATTRIBUTES AND THE DATA IN THE DATABASE
	______________________________________________________________________*/
	public function __construct($params){
		/*** Default values ***/		
		$this->emptyMessage = Lang::get('main.list-no-result');
		$this->action = $_SERVER['REQUEST_URI'];		
		
		/*** Get the values from the parameters array **/
		foreach($params as $key => $value){
			$this->$key = $value;
		}
		
		$model = $this->model;
		
		if(!isset($this->reference)){
			$this->reference = $model::getPrimaryColumn();			
		}
		$this->refAlias = is_array($this->reference) ? reset($this->reference) : $this->reference;
		$this->refField = is_array($this->reference) ? reset(array_keys($this->reference)) : $this->reference;
		
		$model::setPrimaryColumn($this->refField);
		if(isset($this->table)){
			$model::setTable($this->table);
		}
		if(isset($this->dbname)){
			$model::setDbName($this->dbname);
		}
		
		$this->dbo= DB::get($model::getDbName());
		$this->table = $model::getTable();
		
		/*** initialize controls ***/
		foreach($this->controls as &$button){
			switch($button['template']){				
				case "refresh" :
					$button = array(
						"icon" => "refresh", 						
						"onclick" => "app.lists['$this->id'].refresh();"
					);
				break;
			}
		}
		
		/*** Get the filters sent by POST or registered in COOKIES ***/
		$parameters = array('searches', 'sorts', 'lines', 'page');
		$cookie = isset($_COOKIE["list-{$this->id}"]) ? json_decode($_COOKIE["list-$this->id"], true) : array();
		
		foreach($parameters as $name){
			if(isset($cookie[$name])){
				$this->$name = $cookie[$name];
			}
			
			if(isset($_POST[$name])){
				$this->$name = json_decode($_POST[$name],true);
			}
			
			// to register in cookie the current filters
			$cookie[$name] = $this->$name;
		}
		
		// register the filters in cookie for future list call
		setcookie("list-{$this->id}", json_encode($cookie), time() + 365 * 24 * 3600, '/');		
	}
	
	/*_____________________________________________________________________
	
		GET THE DATA TO DISPLAY FROM THE DATABASE, GET OR POST
	_____________________________________________________________________*/
	public function get(){		
	    $this->displayedColumns = 0;
		if(!empty($this->data) && is_array($this->data)){
	    	return $this->getFromArray($this->data);
	    }
	    elseif($this->model && $this->table){
			return $this->getFromDatabase();
		}
	}
	
	private function getFromDatabase(){
		$fields = array();
				
		$where = array();
		if(!empty($this->filter)){
			if($this->filter instanceof DBExample){
				$where[] = $this->filter->parse($this->binds);				
			}
			elseif(is_array($this->filter)){
				$where[] = $this->filter[0];
				$this->binds = $this->filter[1];
			}
			else{
				$where[] = $this->filter;
			}
		} 		
			
		
		/* insert the reference if not present in the fields **/
		if(!isset($this->fields[$this->refAlias])){
			$this->fields[$this->refAlias] = array(
				'field' => $this->refField,
				'hidden' => true
			);
		}
		
		/*** Prepare the fields to research ***/
		$searches = array();
		foreach($this->fields as $name => &$field){
			if(!$field['independant']){
				if(!isset($field['field'])){
					$field['field'] = $name;
				}
				$fields[$this->dbo->formatField($field['field'])] = $this->dbo->formatField($name);
				
				/*** Get the pattern condition ***/			
				if($pattern = $this->searches[$name]){
					$where[] = DBExample::make(array($field['field'] => array('$like' => "%$pattern%")), $this->binds);
				}	
			}

			/*** Get the number of displayed columns ***/
			if(!$field['hidden']){
				$this->displayedColumns ++;
			}
		}
		if($this->checkbox){
			$this->displayedColumns++;
		}

		try{
			$where = implode(" AND ", $where);
			$model = $this->model;			
			$this->recordNumber = $this->dbo->count($this->table, $where, $this->binds, $this->refField, $this->group);
			
			/*** Get the number of the page ***/
			if($this->lines == self::ALL_LINES){
				$this->lines = $this->recordNumber;
			}
			if($this->page > ceil($this->recordNumber / $this->lines) && $this->page > 1){
				$this->page= (ceil($this->recordNumber / $this->lines) > 0) ? ceil($this->recordNumber / $this->lines) : 1;					
			}
			$this->start = ($this->page-1) * $this->lines;  

			/*** Get the data from the database ***/
			$request = array(
				'fields' => $fields,
				'from' => $this->table,
				'where' => $where,
				'binds' => $this->binds,
				'orderby' => $this->sorts,
				'group' => $this->group,
				'limit' => "$this->start, $this->lines",
				'index' => $this->refAlias,
				'return' => $this->model
			);

			$this->results = $this->dbo->select($request);
			
			return true;
		}
		catch(DatabaseException $e){
			exit(DEBUG_MODE ? $e->getMessage() : Lang::get('main.list-error'));
		}  
	}
	
	/**
	 * Get the data of the list from a given array
	 */
	private function getFromArray($data){
		foreach($this->fields as $name => &$field){
			if(!$field['hidden']){
				$this->displayedColumns ++;
			}
			
			if($pattern = $this->searches[$name]){
				$data = array_filter($data, function($line) use($pattern, $name){
					return stripos($line[$name], $pattern) !== false;
				});
			}
			
			if($sort = $this->sorts[$name]){				 
				usort($data, function($a, $b) use($sort, $name){
					if($sort > 0){
						return $a[$name] < $b[$name];
					}
					else{
						return $b[$name] < $a[$name];
					}
				});
			}
		}
				
		$this->recordNumber = count($data);			
		
		if($this->page > ceil($this->recordNumber / $this->lines) && $this->page > 1){
			$this->page = (ceil($this->recordNumber / $this->lines) > 0) ? ceil($this->recordNumber / $this->lines) : 1;				
		}
		$this->start = ($this->page - 1) * $this->lines;  
		$this->results = array_slice(array_map(function($line){ return (object)$line; }, $data), $this->start, $this->lines);
		
		return true;
	}
	
	public function set($data){
		$this->getFromArray($data);
	}
	
	/*_____________________________________________________________________
	
		DISPLAY THE LIST (WITH OR WITHOUT NAVIGATION BAR)
	_____________________________________________________________________*/
	public function __toString(){
		try{
        	// get the data to display
        	$this->get();

			// get the total number of pages
	        $pages = (ceil($this->recordNumber / $this->lines) > 0) ? ceil($this->recordNumber / $this->lines) : 1;
			
			/*** At least one result to display ***/
			$display = array();
			$param = array();
			if(is_array($this->results)){
				foreach($this->results as $id => $line){
					$display[$id] = array();
					$param[$id] = array();

					if($this->selected == $id){
						$param[$id]['class'] = 'selected ';
					}
					if($this->lineClass){
						$function = $this->lineClass;
						$param[$id]['class'] .= $function($line);
					}

					foreach($this->fields as $name => $field){
						$display[$id][$name] = array();

						foreach(array('title', 'href', 'onclick', 'style', 'unit', 'class', 'display', 'target') as $prop){
							if(isset($field[$prop])){
								if(is_callable($field[$prop])){
									$field[$prop] = $field[$prop]($line->$name, $field, $line);
								}
								$display[$id][$name][$prop] = $field[$prop];
							}						
						}
						$display[$id][$name]['class'] .= " list-cell-$this->id-$name ";
						if(isset($field['onclick']) || isset($field['href'])){
							$display[$id][$name]['class'] .= " list-cell-clickable";
						}							 
								
						if($field['hidden']){
							$display[$id][$name]['style'] = 'display:none';
						}
							
						if(!isset($display[$id][$name]['display'])){
							$display[$id][$name]['display'] = $line->$name;
						}
					}
				}
			}
			return View::make(ThemeManager::getSelected()->getView("item-list.tpl"), array(			
				'list' => $this,
				'display' => $display,
				'linesParameters' => $param,
				'pages' => $pages
			));
		}
		catch(Exception $e){
			exception_handler($e);
		}
	}	
	
	/*_____________________________________________________________________
	
		GET THE FIELD ARRAY, BY THE NAME OF THE FIELD 
	_____________________________________________________________________*/
	private function getFieldByName($name){
		foreach($this->fields as $id => $field){
			if($field['name'] == $name || $field['field'] == $name)
				return $this->fields[$id];			
		}
		return null;
	}	
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/