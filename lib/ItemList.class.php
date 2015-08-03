<?php
/**
 * ItemList.class.php
 * @author Elvyrra SAS
 */



/**
 * This class is used to generate and display smart lists, getting data from the database or a given array
 */

class ItemList{
	/*** Class constants ***/	
	const DEFAULT_MODEL = 'GenericModel';
	const ALL_LINES = 'all';
	const DEFAULT_LINE_CHOICE = 20;

	public static $lineChoice = array(10, 20, 50, 100);
	
	/*** Instance default values ***/
	public 	$controls = array(),
			$fields = array(),
			$searches = array(),
			$sorts = array(),
			$binds = array(),
			$lines = self::DEFAULT_LINE_CHOICE,
			$page = 1,
			$action,
			$model = self::DEFAULT_MODEL,
			$group = array(),
			$selected = null,
			$lineClass = null,
			$style = '',
			$navigation = true,
			$navigationClass = '',
			$noHeader = false,
			$target = '',
			$emptyMessage;
	private $dbo;

	private static $fieldsProperties = array(
		'field' => null,
		'class' => null,
		'title' => null, 
		'href' => null,
		'onclick' => null, 
		'style' => null, 
		'unit' => null,
		'display' => null, 
		'target' => null,
		'sort' => true,
		'search' => true,
		'independant' => false,
		'hidden' => false,
	);
			
	/**
	 * Constructor
	 * @param arary $params The parameter of the list
	 */
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
		
		/*** initialize fields default values ***/
		foreach($this->fields as $name => &$field){
			$field = new ItemListField($field);			
		}

		/*** initialize controls ***/
		foreach($this->controls as &$button){
			if(!empty($button['template'])){
				switch($button['template']){				
					case "refresh" :
						$button = array(
							"icon" => "refresh", 						
							"onclick" => "app.lists['$this->id'].refresh();"
						);
					break;
				}
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
	
	/**
	 * Get the data to display
	 * @return array The data to display
	 */
	public function get(){		
		if(isset($this->data) && is_array($this->data)){
	    	return $this->getFromArray($this->data);
	    }
	    elseif($this->model && $this->table){
			return $this->getFromDatabase();
		}
	}
	

	/**
	 * Get the data from the database
	 * @return array The data taken from the databases
	 */
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
			$this->fields[$this->refAlias] = new ItemListField(array(
				'field' => $this->refField,
				'hidden' => true
			));
		}
		
		/*** Prepare the fields to research ***/
		$searches = array();
		foreach($this->fields as $name => &$field){
			if(!$field->independant){

				if(!$field->field){
					$field->field = $name;
				}
				
				$fields[$this->dbo->formatField($field->field)] = $this->dbo->formatField($name);
				
				/*** Get the pattern condition ***/			
				$pattern = !empty($this->searches[$name]) ? $this->searches[$name] : '';
				if($pattern){
					$where[] = DBExample::make(array($field->field => array('$like' => "%$pattern%")), $this->binds);
				}	
			}
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
	 * @param array $data The array where to take the data to display
	 */
	private function getFromArray($data){
		foreach($this->fields as $name => &$field){
			$pattern = isset($this->searches[$name]) ? $this->searches[$name] : null;
			if($pattern){
				$data = array_filter($data, function($line) use($pattern, $name){
					return stripos($line[$name], $pattern) !== false;
				});
			}
			
			$sort = isset($this->sorts[$name]) ? $this->sorts[$name] : null;
			if($sort){
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
	

	/**
	 * Set the list data
	 * @param array $data The data to set
	 */
	public function set($data){
		$this->getFromArray($data);
	}
	
	

	/**
	 * Display the list
	 * @return string The HTML result of displaying
	 */
	public function __toString(){
    	try{
	    	// get the data to display
	    	$this->get();

			// get the total number of pages
	        $pages = (ceil($this->recordNumber / $this->lines) > 0) ? ceil($this->recordNumber / $this->lines) : 1;
			
			/*** At least one result to display ***/
			$data = array();
			$param = array();
			if(is_array($this->results)){
				foreach($this->results as $id => $line){
					$data[$id] = array();
					$param[$id] = array(
						'class' => ''
					);

					if($this->selected === $id){
						$param[$id]['class'] .= 'selected ';
					}
					if($this->lineClass){
						$function = $this->lineClass;
						$param[$id]['class'] .= $function($line);
					}

					foreach($this->fields as $name => $field){
						$cell = array(
							'class' => '',
							'style' => '',
							'display' => !empty($line->$name) ? $line->$name : ''
						);

						foreach(self::$fieldsProperties as $prop => $default){
							if(!is_null($field->$prop)){
								if(is_callable($field->$prop)){
									$func = $field->$prop;
									$cell[$prop] = $func(!empty($line->$name) ? $line->$name : null, $field, $line);									
								}
								else{
									$cell[$prop] = $field->$prop;									
								}
							}						
						}

						$cell['class'] .= " list-cell-$this->id-$name ";
						if($field->onclick || $field->href){
							$cell['class'] .= " list-cell-clickable";
						}							 
								
						if($field->hidden) {
							$cell['class'] = ' list-cell-hidden';
						}

						$data[$id][$name] = $cell;
					}
				}
			}
			
			return View::make(ThemeManager::getSelected()->getView("item-list.tpl"), array(			
				'list' => $this,
				'data' => $data,
				'linesParameters' => $param,
				'pages' => $pages
			));
		}
		catch(Exception $e){
			ErrorHandler::exception($e);
		}
	}	
}