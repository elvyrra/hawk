<?php
/**
 * ItemListField.class.php
 * @author Elvyrra SAS
 * @license MIT
 */

/**
 * This class describes the field displayed in a smart list.
 * All properties of an instance of this class can be scalar, or a function taking as arguments :
 *	-	$value The value of the cell
 *	-	$field The field itself
 *	-	$line All the values in the list results line
 * @package Core\List
 */
class ItemListField {
	/**
	 * The list of the properties that can be either scalar or callable
	 */
	private static $callableProperties = array(
		'class',
		'title', 
		'href',
		'target',
		'onclick', 
		'style', 
		'unit',
		'display', 
	);


	/**
	 * The name of the field in the list. Must be unique for each field in a list
	 */
	public $name,

	/**
	 * The field name in the search table 
	 * @var string
	 */
	$field = null,

	/**
	 * The class attribute to add to the result cell
	 * @var string|callable
	 */
	$class = null,

	/**
	 * The 'title' attribute on hover on the cell
	 * @var string|callable
	 */
	$title = null, 

	/**
	 * This property, if set, will permit to open the set URL in the target defined by the property $target on a click event
	 * @var string|callable
	 */
	$href = null,

	/**
	 * The target where to open the URL defined in $href property
	 * @var string|callable
	 */
	$target = null,
	
	/**
	 * The 'onclick' attribute
	 * @var string|callable
	 */
	$onclick = null, 

	/**
	 * The 'style' attribute
	 * @var string|callable
	 */
	$style = null, 
	
	/**
	 * A unit to add after the value of the cell
	 * @var string|callable
	 */
	$unit = null,

	/**
	 * Define if you want a specific displaying for this cell
	 * @var string|callable
	 */
	$display = null, 

	/**
	 * Display the widgets to sort the list by this field values
	 * @var bool
	 */
	$sort = true,

	/**
	 * The sort value 
	 * @var string (ASC or DESC)
	 */
	$sortValue = null,

	/**
	 * Displays the serach input for this field
	 * @var bool
	 */
	$search = true,

	/**
	 * The search value
	 * @var string
	 */
	$searchValue = null,
	
	/**
	 * If set to true, this field will not be searched in the database
	 * @var bool
	 */
	$independant = false,
			
	/**
	 * The label to display in the list header
	 * @var string
	 */
	$label = null,

	/**
	 * if set to true, this field will appear in the DOM, but wille be not visible
	 * @var bool
	 */
	$hidden = false,

	/**
	 * The list the field is associated with
	 * @var ItemList
	 */
	$list = null;

	/**
	 * Constructor
	 * @param string $name The field name
	 * @param array $param The field parameters
	 * @param ItemList The list the field is associated with
	 */
	public function __construct($name, $param, ItemList $list){
		$this->name = $name;
		foreach($param as $key => $value){
			$this->$key = $value;
		}

		if(!$this->field){
			$this->field = $this->name;
		}		

		$this->list = $list;
	}

	/**
	 * Get the Search SQL expression on this field
	 * @param array $binds The binded values, passe by reference that will be filled
	 * @return string The SQL expression for the search on this field
	 */
	public function getSearchCondition(&$binds){
		if($this->searchValue){
			return DBExample::make(array($this->field => array('$like' => '%' . $this->searchValue . '%')), $binds);
		}	
	}


	/**
	 * Display the search field in the list header
	 * @return string The HTML result to display
	 */
	public function displaySearchInput(){
		if($this->search){
			if(!is_array($this->search)){
				$this->search = array(
					'type' => 'text'
				);
			}

			switch($this->search['type']){
				case 'select' :
					$input = new SelectInput(array(
						'options' => $this->search['options'],
						'invitation' => isset($this->search['invitation']) ? $this->search['invitation'] : null,
						'emptyValue' => isset($this->search['emptyValue']) ? $this->search['emptyValue'] : null,
						'attributes' => array(
							'data-bind' => 'value: search',
						)
					));
					break;

				case 'checkbox' :
					$input = new CheckboxInput(array(
						'attributes' => array(
							'data-bind' => 'checked: search',
						)
					));
					break;

				
				case 'text' :
				default :
					$input = new TextInput(array(
						'after' => '<i class="fa fa-times-circle clean-search" data-bind="click: function(data){ data.search(null); }, visible: search()"></i>',
						'attributes' => array(
							'data-bind' => "textInput: search, css : search() ? 'alert-info not-empty' : 'empty'",
						)
					));
					break;
			}
			$input->attributes['data-field'] = $this->name;
			$input->class = ' list-search-input';

			return $input->__toString();
		}
		else{
			return '';
		}
	}

	/**
	 * Display the field header
	 * @return string The HTML result to display	 
	 */
	public function displayHeader(){
		return View::make(ThemeManager::getSelected()->getView('item-list/field-header.tpl'), array(
			'field' => $this,
		));
	}

	/**
	 * Get the displayed value
	 * @param array $lineIndex The index of the line in the list results to display
	 * @return string The HTML result to display
	 */
	public function displayCell($lineIndex){
		$line = $this->list->results[$lineIndex];
		$name = $this->name;		

		$cell = new StdClass;
		foreach(self::$callableProperties as $prop){
			if(! is_null($this->$prop) && is_callable($this->$prop)){
				$func = $this->$prop;				
				$cell->$prop = $func(!empty($line->$name) ? $line->$name : null, $this, $line);
			}
			else{
				$cell->$prop = $this->$prop;
			}
		}

		// Compute the cell content
		if($cell->display){
			$cell->content = $cell->display;
		}
		else{
			$cell->content = isset($line->$name) ? $line->$name : '';
		}

		// Add a unit to the displayed value
		if($cell->unit && !$cell->content){
			$cell->content .= ' ' . $cell->unit;
		}

		return View::make(ThemeManager::getSelected()->getView('item-list/result-cell.tpl'), array(
			'cell' => $cell,
			'field' => $this
		));
	}
	
}