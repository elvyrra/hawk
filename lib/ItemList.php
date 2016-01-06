<?php
/**
 * ItemList.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class is used to generate and display smart lists, getting data from the database or a given array
 * @package List
 */
class ItemList{
	use Utils;

	/*** Class constants ***/	
	const DEFAULT_MODEL = 'GenericModel';
	const ALL_LINES = 'all';
	const DEFAULT_LINE_CHOICE = 20;

	/**
	 * The possible choices for the number of lines to display
	 */
	public static $lineChoice = array(10, 20, 50, 100);
	
	/**
	 * The list control buttons
	 * @var array
	 */
	public 	$controls = array(),

	/**
	 * The list fields
	 * @var array
	 */
	$fields = array(),

	/**
	 * The user researches in the list
	 * @var array
	 */
	$searches = array(),

	/**
	 * The user sorts
	 * @var array
	 */
	$sorts = array(),

	/**
	 * The binded values for the SQL queries
	 * @var array
	 */
	$binds = array(),

	/**
	 * The number of lines to display
	 * @var int
	 */
	$lines = self::DEFAULT_LINE_CHOICE,

	/**
	 * The page number to display
	 * @var int
	 */
	$page = 1,

	/**
	 * The URI called to refresh the list
	 * @var string 
	 */
	$action,

	/**
	 * The model used to get the data in the database
	 * @var string
	 */
	$model = self::DEFAULT_MODEL,

	/**
	 * The fields group in the search query
	 * @var array
	 */
	$group = array(),

	/**
	 * The id of the selected line 
	 * @var mixed
	 */
	$selected = null,

	/**
	 * The class to apply to the list lines
	 * @var strnig|function
	 */
	$lineClass = null,

	/**
	 * Defines if the navigation bar of the list must be displayed
	 * @var bool
	 */
	$navigation = true,

	/**
	 * If set to true, the columns headers are not displayed
	 * @var bool	 
	 */
	$noHeader = false,

	/**
	 * If not empty, define the CSS selector of the node where to display the list refreshing result
	 * @var string
	 */
	$target = '',

	/**
	 * Define the message to display if no result are found for the list
	 * @var string
	 */
	$emptyMessage,


	/**
	 * The whole list (list + navigation bar) view filename
	 */
	$tpl,

	/**
	 * The navigation bar view filename
	 */
	$navigationBarTpl,

	/**
	 * The list view filename
	 */
	$listTpl,

	/**
	 * The result view filename
	 */
	$resultTpl;


	/**
	 * The DB instance used to make the database queries to get the list results
	 */
	private $dbo,

	/**
	 * Define if the list refreshing or displayed for the first time
	 * @var boolean
	 */
	$refresh = false;


	/**
	 * Constructor
	 * @param arary $params The parameter of the list
	 */
	public function __construct($params){
		/*** Default values ***/		
		$this->emptyMessage = Lang::get('main.list-no-result');
		$this->action = getenv('REQUEST_URI');
		$this->refresh = !!App::request()->getParams('refresh');
		
		/*** Get the values from the parameters array **/
		$this->map($params);

		if(!class_exists($this->model)){
			$trace = debug_backtrace();
			$reflection = new \ReflectionClass($trace[1]['class']);
			$this->model = $reflection->getNamespaceName() . '\\' . $this->model;
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
		}
		
		/*** initialize fields default values ***/
		foreach($this->fields as $name => &$field){
			$field = new ItemListField($name, $field, $this);
			if(isset($this->searches[$name])){
				$field->searchValue = $this->searches[$name];
			}

			if(!empty($this->sorts[$name])){
				$field->sortValue = $this->sorts[$name];
			}
		}

		$event = new Event('list.' . $this->id . '.instanciated', array(
			'list' => $this
		));
		$event->trigger();
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
			$this->fields[$this->refAlias] = new ItemListField($this->refAlias, array(
				'field' => $this->refField,
				'hidden' => true
			), $this);
		}
		
		/*** Prepare the fields to research ***/
		$searches = array();
		foreach($this->fields as $name => &$field){
			if(!$field->independant){
				$fields[$this->dbo->formatField($field->field)] = $this->dbo->formatField($name);
				
				/*** Get the pattern condition ***/			
				$sql = $field->getSearchCondition($this->binds);				
				if($sql){
					$where[] = $sql;
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
				'start' => $this->start,
				'limit' => $this->lines,
				'index' => $this->refAlias,
				'return' => $this->model
			);

			$this->results = $this->dbo->select($request);
			
			return $this->results;
		}
		catch(DBException $e){
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
		
		return $this->results;
	}
	

	/**
	 * Set the list data
	 * @param array $data The data to set
	 */
	public function set($data){
		$this->getFromArray($data);
	}
	
	
	/**
	 * Get the list views files
	 */
	private function getViews(){
		if(empty($this->tpl)){
			$this->tpl = Theme::getSelected()->getView('item-list/container.tpl');
		}

		if(empty($this->navigationBarTpl)){
			$this->navigationBarTpl = Theme::getSelected()->getView('item-list/navigation-bar.tpl');
		}

		if(empty($this->listTpl)){
			$this->listTpl = Theme::getSelected()->getView('item-list/list.tpl');
		}

		if(empty($this->resultTpl)){
			$this->resultTpl = Theme::getSelected()->getView('item-list/result.tpl');
		}
	}

	/**
	 * Display the list (alias)
	 * @return string The HTML result of displaying
	 */
	public function __toString(){
    	return $this->display();
	}

	/**
	 * display the list
	 * @return string The HTML result of displaying
	 */	
	public function display(){
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
						$data[$id][$name] = $field->displayCell($id);
					}
				}
			}

			$this->getViews();

			// Generate the script to insert the list in the application , client side
			if($this->refresh){
				$tplFile = $this->resultTpl;
				$script = 
					'app.ready(function(){
				        if(list = app.lists["' . $this->id . '"]){
				            list.selected = ' . ($this->selected !== false ? '"' . $this->selected . '"' : 'null') .' 
							list.maxPages(' . $pages . ');
							list.recordNumber(' . $this->recordNumber . ');
				        }
				    });';
			}
			else{
				$script = 
					'require(["app"], function(){
						app.ready(function(){									
							var list = new List({
								id : "' . $this->id . '",
								action : "' . $this->action . '",
								target : "' . $this->target . '",
								fields : ' . json_encode(array_keys($this->fields)) .',
							});
							
							list.selected = ' . ($this->selected !== false ? '"' . $this->selected . '"' : 'null') .' 
							list.maxPages(' . $pages . ');
							list.recordNumber(' . $this->recordNumber . ');

							app.lists["' . $this->id . '"] = list;			
						});
					});';
				
				$tplFile = $this->tpl;
			}

			return 				
				View::make($tplFile, array(			
					'list' => $this,
					'data' => $data,
					'linesParameters' => $param,
					'pages' => $pages
				)) . 
				'<script type="text/javascript">' . $script . '</script>';
		}
		catch(\Exception $e){
			App::errorHandler()->exception($e);
		}
	}


	/**
	 * Check if the list is refreshing or displayed for the first time
	 */
	public function isRefreshing(){
		return $this->refresh;
	}




}