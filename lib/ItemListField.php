<?php
/**
 * ItemListField.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the field displayed in a smart list.
 * All properties of an instance of this class can be scalar, or a function taking as arguments :
 *    -    $value The value of the cell
 *    -    $field The field itself
 *    -    $line All the values in the list results line
 *
 * @package List
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
     *
     * @var string
     */
    $field = null,

    /**
     * The class attribute to add to the result cell
     *
     * @var string|callable
     */
    $class = null,

    /**
     * The 'title' attribute on hover on the cell
     *
     * @var string|callable
     */
    $title = null,

    /**
     * This property, if set, will permit to open the set URL in the target defined by the property $target on a click event
     *
     * @var string|callable
     */
    $href = null,

    /**
     * The target where to open the URL defined in $href property
     *
     * @var string|callable
     */
    $target = null,

    /**
     * The 'onclick' attribute
     *
     * @var string|callable
     */
    $onclick = null,

    /**
     * The 'style' attribute
     *
     * @var string|callable
     */
    $style = null,

    /**
     * A unit to add after the value of the cell
     *
     * @var string|callable
     */
    $unit = null,

    /**
     * A specified format
     *
     * @var string
     */
    $format = null,

    /**
     * The default number of decimals to display in case of format 'number'
     *
     * @var integer
     */
    $decimals = 2,

    /**
     * the default decimal separator in case of format 'number'
     *
     * @var string
     */
    $decimalSep = '.',


    /**
     * The default thousand separator in case of format 'number'
     *
     * @var string
     */
    $thousandSep = ' ',

    /**
     * Define if you want a specific displaying for this cell
     *
     * @var string|callable
     */
    $display = null,

    /**
     * Display the widgets to sort the list by this field values
     *
     * @var boolean
     */
    $sort = true,

    /**
     * The sort value
     *
     * @var string (ASC or DESC)
     */
    $sortValue = null,

    /**
     * Displays the serach input for this field
     *
     * @var boolean
     */
    $search = true,

    /**
     * The search value
     *
     * @var string
     */
    $searchValue = null,

    /**
     * If set to true, this field will not be searched in the database
     *
     * @var boolean
     */
    $independant = false,

    /**
     * The label to display in the list header
     *
     * @var string
     */
    $label = null,

    /**
     * If set to true, this field will appear in the DOM, but wille be not visible
     *
     * @var boolean
     */
    $hidden = false,

    /**
     * The list the field is associated with
     *
     * @var ItemList
     */
    $list = null;

    /**
     * Constructor
     *
     * @param string   $name  The field name
     * @param array    $param The field parameters
     * @param ItemList $list  The list the field is associated with
     */
    public function __construct($name, $param, ItemList $list){
        $this->name = $name;
        foreach($param as $key => $value){
            $this->$key = $value;
        }

        if(!$this->field) {
            $this->field = $this->name;
        }

        $this->list = $list;
    }

    /**
     * Get the Search SQL expression on this field
     *
     * @param array $binds The binded values, passe by reference that will be filled
     *
     * @return string The SQL expression for the search on this field
     */
    public function getSearchCondition(&$binds) {
        if($this->searchValue !== null) {
            switch($this->search['type']) {
                case 'select' :
                    return DBExample::make(
                        array(
                            $this->field => $this->getInput()->dbvalue()
                        ),
                        $binds
                    );

                case 'date' :
                    return DBExample::make(
                        array(
                            'DATE(' . $this->field . ')' => $this->getInput()->dbvalue()
                        ),
                        $binds
                    );

                case 'date-interval' :
                    $values = $this->getInput()->dbvalue();
                    switch(count($values)) {
                        case 0 :
                            return '';

                        case 1 :
                            return DBExample::make(
                                array(
                                    'DATE(' . $this->field . ')' => $values[0]
                                ),
                                $binds
                            );

                        default :
                            return DBExample::make(
                                array(
                                    'DATE(' . $this->field . ')' => array(
                                        '$between' => array(
                                            min($values),
                                            max($values)
                                        )
                                    )
                                ),
                                $binds
                            );

                    }


                default :
                    return DBExample::make(
                        array(
                            $this->field => array(
                                '$like' => '%' . $this->getInput()->dbvalue() . '%'
                            )
                        ),
                        $binds
                    );
            }
        }

        return '';
    }


    /**
     * Get the input corresponding to the field
     *
     * @return FormInput the input instance
     */
    public function getInput(){
        if(!is_array($this->search)) {
            $this->search = array(
                'type' => 'text'
            );
        }

        switch($this->search['type']) {
            case 'select' :
                $input = new SelectInput(array(
                    'options' => $this->search['options'],
                    'invitation' => isset($this->search['invitation']) ? $this->search['invitation'] : null,
                    'emptyValue' => isset($this->search['emptyValue']) ? $this->search['emptyValue'] : null,
                    'attributes' => array(
                        'e-value' => 'search',
                        'e-class' => "search ? 'alert-info not-empty' : 'empty'"
                    )
                ));
                break;

            case 'checkbox' :
                $input = new CheckboxInput(array(
                    'attributes' => array(
                        'e-value' => 'search'
                    )
                ));
                break;

            case 'date' :
                $input = new DatetimeInput(array(
                    'id' => uniqid(),
                    'after' => Icon::make(array(
                        'icon' => 'times-circle',
                        'class' => 'clean-search',
                        'e-click' => 'function(){ search = null; }',
                        'e-show' => 'search'
                    )),
                    'attributes' => array(
                        'e-value' => 'search',
                        'e-class' => "search ? 'alert-info not-empty' : 'empty'"
                    ),
                ));
                break;

            case 'date-interval' :
                $input = new DatetimeInput(array(
                    'id' => uniqid(),
                    'after' => Icon::make(array(
                        'icon' => 'times-circle',
                        'class' => 'clean-search',
                        'e-click' => 'function(){ search = null; }',
                        'e-show' => 'search'
                    )),
                    'attributes' => array(
                        'e-value' => 'search',
                        'e-class' => "search ? 'alert-info not-empty' : 'empty'"
                    ),
                    'interval' => true
                ));
                break;


            case 'text' :
            default :
                $input = new TextInput(array(
                    'after' => Icon::make(array(
                        'icon' => 'times-circle',
                        'class' => 'clean-search',
                        'e-click' => 'function(){ search = null; }',
                        'e-show' => 'search'
                    )),
                    'attributes' => array(
                        'e-input' => 'search',
                        'e-class' => "search ? 'alert-info not-empty' : 'empty'"
                    )
                ));
                break;
        }
        $input->attributes['data-field'] = $this->name;
        $input->class = ' list-search-input';
        $input->value = $this->searchValue;

        return $input;
    }


    /**
     * Display the search field in the list header
     *
     * @return string The HTML result to display
     */
    public function displaySearchInput(){
        if($this->search) {
            $input = $this->getInput();

            return $input->__toString();
        }
        else{
            return '';
        }
    }

    /**
     * Display the field header
     *
     * @return string The HTML result to display
     */
    public function displayHeader(){
        return View::make(Theme::getSelected()->getView('item-list/field-header.tpl'), array(
            'field' => $this
        ));
    }

    /**
     * Get the display function for a given 'format'
     * @return callable The displaying function
     */
    private function getFormatDisplayFunction() {
        switch($this->format) {
            case 'number' :
                $this->display = function($value) {
                    return number_format($value, $this->decimals, $this->decimalSep, $this->thousandSep);
                };
                break;

            case 'date' :
                $this->display = function($value) {
                    if(!is_numeric($value)) {
                        $value = strtotime($value);
                    }
                    return date(Lang::get('main.date-format'), $value);
                };
                break;

            case 'datetime' :
                $this->display = function($value) {
                    if(!is_numeric($value)) {
                        $value = strtotime($value);
                    }
                    return date(Lang::get('main.time-format'), $value);
                };
                break;

            case 'relative-time' :
                $this->display = function($value) {
                    if(!is_numeric($value)) {
                        $value = strtotime($value);
                    }
                    return Utils::timeAgo($value);
                };
                break;

            case 'email' :
                $this->display = function($value) {
                    return '<a href="mailto:' . $value . '">' . $value . '</a>';
                };
                break;
        }
    }

    /**
     * Get the displayed value
     *
     * @param array $lineIndex The index of the line in the list results to display
     *
     * @return string The HTML result to display
     */
    public function displayCell($lineIndex){
        $line = $this->list->results[$lineIndex];
        $name = $this->name;

        $this->getFormatDisplayFunction();

        $cell = new \StdClass;
        foreach(self::$callableProperties as $prop){
            if(! is_null($this->$prop) && is_callable($this->$prop)) {
                $func = $this->$prop;
                $cell->$prop = $func(isset($line->$name) ? $line->$name : null, $this, $line);
            }
            else{
                $cell->$prop = $this->$prop;
            }
        }



        // Compute the cell content
        if(isset($cell->display)) {
            $cell->content = $cell->display;
        }
        else{
            $cell->content = isset($line->$name) ? $line->$name : '';
        }

        // Add a unit to the displayed value
        if($cell->unit && $cell->content) {
            $cell->content .= ' ' . $cell->unit;
        }

        return View::make(Theme::getSelected()->getView('item-list/result-cell.tpl'), array(
            'cell' => $cell,
            'field' => $this
        ));
    }

}
