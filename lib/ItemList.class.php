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
	private $TEMPLATE_DIR;
	
	/*______________________________________________________________________
	
		CONTRUCTOR : INIT THE ATTRIBUTES AND THE DATA IN THE DATABASE
	______________________________________________________________________*/
	public function __construct($params){
		/*** Default values ***/
		$this->buttons = array();
		$this->fields = array();
		$this->emptyMessage = Lang::get('no-result', 'list');
		$this->searches = array();
        $this->sort = array();        
        $this->linesNumber = 20;
        $this->pageNumber = '1';
		$this->filter = array();		
		$this->file = $_SERVER['REQUEST_URI'];
		$this->TEMPLATE_DIR = TEMPLATE_DIR ."/ItemList";
		
		/*** Get the values from the parameters array **/
		foreach($params as $key => $value)
			$this->$key = $value;
		
		/*** initialize buttons ***/
		foreach($this->buttons as &$button){
			switch($button['template']){				
				case "refresh" :
					$button = array(
						"icon" => "refresh", 						
						"onclick" => "page.lists['$this->id'].refresh();"
					);
				break;
			}
		}
		
      	/*** Get the filters sent by POST ***/
		if(isset($_POST['searches'])) $this->searches = json_decode($_POST['searches'],true);		
        if(isset($_POST['sort'])) $this->sort = json_decode($_POST['sort'],true);        
		if(isset($_POST['filter'])) $this->filter = json_decode($_POST['filter'],true);
        if(isset($_POST['linesNumber'])) $this->linesNumber = $_POST['linesNumber'];
        if(isset($_POST['pageNumber'])) $this->pageNumber = $_POST['pageNumber'];                
        
		/*** Set the default sort ****/
		if(empty($this->sort) && $params['sort'])
			$this->sort = $params['sort'];
		
		/*** Set the default search ***/
		if(empty($this->search) && $params['search'])
			$this->search = $params['search'];
			
		/*** Set the number of the first result ***/
		$this->start = ($this->pageNumber-1) * $this->linesNumber;  
       
		/*______________________________________________________________________
	
					Get the data to display on the database
		______________________________________________________________________*/		
		$this->get();
	}
	
	/*_____________________________________________________________________
	
		GET THE DATA TO DISPLAY FROM THE DATABASE, GET OR POST
	_____________________________________________________________________*/
	public function get(){		
	    $this->displayedColumns = 0;
		if(isset($_POST["set-$this->id"])){			
	        $this->force = is_array($_POST["set-$this->id"]) ? $_POST["set-$this->id"] : array();
	        $this->recordNumber = count($this->force);			
				
	        if($this->linesNumber != "all"){
				if($this -> pageNumber > ceil($this->recordNumber / $this->linesNumber) && $this->pageNumber > 1){
					$this -> pageNumber= (ceil($this->recordNumber / $this->linesNumber) > 0) ? ceil($this->recordNumber / $this->linesNumber) : 1;
					$this -> start = ($this->pageNumber - 1) * $this->linesNumber;  
				}
	        	$this->results = array_slice($this->force, $this->start, $this->linesNumber);
	        }
			else
				$this->results = $this->force;
			
			foreach($this->fields as $fieldId => &$field){
				$field['search'] = false;
				$field['sort'] = false;
				if(!$field['hidden'] && !($_GET['print'] && $field['pdf']=== false)){
					$this->displayedColumns ++;
				}
			}
	        return true;
	    }			
	    elseif($this->database){
			$fields = array();
			$conditions = $this->condition ? array($this->condition) : array();		
			$binds = !empty($this->binds) ? $this->binds : array();
			$referenceAlias = preg_replace('/^\w+\.(\w+)$/', '$1', $this->reference);
			
			/*** Prepare the fields to research ***/
			/* First, insert the reference if not in the fields **/
			if($this->getFieldByName($this->reference) == null)
				array_push($this->fields , array('field' => $this->reference, 'name' => $referenceAlias, "hidden" => true));

			
			foreach($this->fields as $fieldId => &$field){
				if(!isset($field['field']))
					$field['field'] = $field['name'];
				if(!$field['independant']){					
					if($field['field'] == $field['name'])
						$fields[] = $field['field'];
					else
						$fields[$field['field']] = $field['name'];							
				}
				/*** Get the pattern condition ***/		
				if($pattern = addslashes($this->searches[$field['name']])){
					$key = uniqid();
					$conditions[] = $field['field'] . " LIKE :$key";
					$binds[$key] = "%$pattern%";
				}

				/*** Get the number of displayed columns ***/
				if(!$field['hidden']){
					$this->displayedColumns ++;
				}
			}
			if(!empty($this->checkbox))
				$this->displayedColumns++;

			$condition = implode(" AND ",$conditions);			       		
			try{
				/** Get the number of records in the database **/      
				$this->recordNumber = $this->database->count($this->table, $condition, $binds, "", $this->group);

				/*** Get the number of the page ***/
				if($this->linesNumber != "all")
					if($this -> pageNumber > ceil($this->recordNumber / $this->linesNumber) && $this->pageNumber > 1){
						$this -> pageNumber= (ceil($this->recordNumber / $this->linesNumber) > 0) ? ceil($this->recordNumber / $this->linesNumber) : 1;
						$this -> start = ($this->pageNumber-1) * $this->linesNumber;  
					}		

				/*** Get the data from the database ***/
				
				$request = array(
					"table" => $this->table,
					"fields" => $fields,
					'binds' => $binds,
					"condition" => $condition,
					"sort" => $this->sort,
					"group" => $this->group,
					"limit" => ($this->linesNumber === "all") ? "" : "$this->start, $this->linesNumber",
					'index' => $referenceAlias
				);

				$this->results = $this->database->select($request);
				return true;
			}
			catch(DatabaseException $e){
				exit($e->getMessage());
			}  
		}
		else
			return false;
	}
	
	public function set($data){
		$_POST["set-$this->id"] = $data;
		$this->get();
	}
	
	/*_____________________________________________________________________
	
		DISPLAY THE LIST (WITH OR WITHOUT NAVIGATION BAR)
	_____________________________________________________________________*/
	public function __toString(){
		// get the total number of pages
        $pages = (ceil($this->recordNumber / $this->linesNumber) > 0) ? ceil($this->recordNumber / $this->linesNumber) : 1;
		
		/*** At least one result to display ***/
		$display = array();
		if(is_array($this->results)){
			foreach($this->results as $id => $line){
				$display[$id] = array();
				foreach($this->fields as $field){
					$name = $field['name'];
					$display[$id][$name] = array();

					foreach(array("title", "onclick", "style", 'unit', 'class', 'display') as $prop){
						if(isset($field[$prop])){
							if(is_callable($field[$prop]))
								$field[$prop] = $field[$prop]($line[$field['name']], $field, $line);

							$display[$id][$name][$prop] = $field[$prop];
						}						
					}
					$display[$id][$name]['class'] .= " list-cell-$this->id-{$field['name']} ".
							(isset($field['onclick']) ? " list-cell-clickable " : "").
							(isset($this->selected) && $this->selected !== false && $this->selected == $id  ? " ui-state-active " : "");
							
					if($field['hidden'])
						$display[$id][$name]['style'] = 'display:none';
					if(!isset($display[$id][$name]['display']))
						$display[$id][$name]['display'] = $line[$field['name']];
				}
			}
		}
		return View::makestr(ThemeManager::getView("item-list.tpl"), array(			
			'list' => $this,
			'display' => $display,
			'pages' => $pages
		));		
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