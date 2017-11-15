<?php
/**
 * ItemList.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to generate and display smart lists, getting data from the database or a given array
 *
 * @package List
 */
class ItemList {
    use Utils;

    // Class constants
    const DEFAULT_MODEL       = '\StdClass';
    const ALL_LINES           = 'all';
    const DEFAULT_LINE_CHOICE = 20;

    /**
     * The possible choices for the number of lines to display
     */
    public static $lineChoice = array(10, 20, 50, 100);

    /**
     * The list id
     *
     * @var string
     */
    public $id,

    /**
     * The list control buttons
     *
     * @var array
     */
    $controls = array(),

    /**
     * The list fields. This corresponds to the properties of each column in the list
     *
     * @var array
     */
    $fields = array(),

    /**
     * The user researches in the list
     *
     * @var array
     */
    $searches = array(),

    /**
     * The user sorts
     *
     * @var array
     */
    $sorts = array(),

    /**
     * The binded values for the SQL queries
     *
     * @var array
     */
    $binds = array(),

    /**
     * The number of lines to display
     *
     * @var int
     */
    $lines = self::DEFAULT_LINE_CHOICE,

    /**
     * The page number to display
     *
     * @var int
     */
    $page = 1,

    /**
     * The URI called to refresh the list
     *
     * @var string
     */
    $action,

    /**
     * The model used to get the data in the database
     *
     * @var string
     */
    $model = self::DEFAULT_MODEL,

    /**
     * The table where to get the data if "model" is not set
     *
     * @var string
     */
    $table,

    /**
     * The default reference field, used to index the list result table
     *
     * @var string
     */
    $reference,

    /**
     * The list filter
     *
     * @var string
     */
    $filter,

    /**
     * The default db instance name
     *
     * @var string
     */
    $dbname = MAINDB,

    /**
     * The raw data to display in the list (overrides table, model, dbname and reference properties)
     *
     * @var array
     */
    $data = null,

    /**
     * The fields group in the search query
     *
     * @var array
     */
    $group = array(),

    /**
     * The class to apply to the list lines
     *
     * @var string|function
     */
    $lineClass = null,

    /**
     * Defines if the navigation bar of the list must be displayed
     *
     * @var bool
     */
    $navigation = true,

    /**
     * If set to true, the columns headers are not displayed
     *
     * @var bool
     */
    $noHeader = false,

    /**
     * If not empty, define the CSS selector of the node where to display the list refreshing result
     *
     * @var string
     */
    $target = '',

    /**
     * Define the message to display if no result are found for the list
     *
     * @var string
     */
    $emptyMessage,


    /**
     * The whole list (list + navigation bar) view filename
     *
     * @var string
     */
    $tpl,

    /**
     * The navigation bar view filename
     *
     * @var string
     */
    $navigationBarTpl,

    /**
     * The list view filename
     *
     * @var string
     */
    $listTpl,

    /**
     * The result view filename
     *
     * @var string
     */
    $resultTpl,


    /**
     * Define if lines can be selectable (with a checkbox).
     * If set to true, the a check box will be display in each line, and a global checkbox to select / unselect each line
     *
     * @var boolean
     */
    $selectableLines = false,


    /**
     * If not empty, defines the list can be customized, and contains the customization parameters
     * @var array
     */
    $customize = array(),

    /**
     * Define if the list is reloading
     *
     * @var boolean
     */
    $rebuild = false,

    /**
     * The list filters
     * @var array
     */
    $filterWidget = array();


    /**
     * The DB instance used to make the database queries to get the list results
     *
     * @var DB
     */
    private $dbo,

    /**
     * Define if the list refreshing or displayed for the first time
     *
     * @var boolean
     */
    $refresh = false;



    /**
     * Constructor
     *
     * @param arary $params The parameter of the list
     */
    public function __construct($params) {
        // Default values
        $this->emptyMessage = Lang::get('main.list-no-result');
        $this->action = str_replace('?' . getenv('QUERY_STRING'), '', getenv('REQUEST_URI'));
        $this->refresh = !!App::request()->getParams('refresh');
        $this->rebuild = !!App::request()->getParams('rebuild');

        // Get the values from the parameters array
        $this->map($params);

        if($this->data === null) {
            if(!class_exists($this->model)) {
                $trace       = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                $reflection  = new \ReflectionClass($trace[1]['class']);
                $this->model = $reflection->getNamespaceName().'\\'.$this->model;
            }

            if($this->model == self::DEFAULT_MODEL) {
                if(!isset($this->table)) {
                    throw new \Exception('ItemList contructor expects property "table" or "model" to be set');
                }

                if(!isset($this->reference)) {
                    $this->reference = 'id';
                }
            }
            else {
                $model = $this->model;

                if(!isset($this->reference)) {
                    $this->reference = $model::getPrimaryColumn();
                }

                if(!isset($this->table)) {
                    $this->table = $model::getTable();
                }

                $this->dbname = $model::getDbName();
            }

            $this->refAlias = is_array($this->reference) ? reset($this->reference) : $this->reference;
            $this->refField = is_array($this->reference) ? reset(array_keys($this->reference)) : $this->reference;

            $this->dbo = DB::get($this->dbname, 'slave');
        }

        // initialize controls
        foreach($this->controls as &$button) {
            if(!empty($button['template'])) {
                switch($button['template']) {
                    case 'refresh' :
                        $button = array(
                            'icon'    => 'refresh',
                            'type'    => 'button',
                            'onclick' => 'app.lists["'.$this->id.'"].refresh();',
                        );
                        break;

                    case 'print' :
                        $button = array(
                            'icon'    => 'print',
                            'type'    => 'button',
                            'onclick' => 'app.lists["' . $this->id . '"].print();',
                        );
                        break;
                }
            }
        }

        // Get the filters sent by POST or registered in COOKIES
        $parameters = array(
            'searches',
            'sorts',
            'lines',
            'page',
            'displayedFields'
        );

        $this->userParam = json_decode(App::session()->getUser()->getOptions('main.list-'.$this->id), true);

        if($this->searches && empty($this->userParam['searches'])) {
            $this->userParam['searches'] = $this->searches;
        }

        if($this->sorts && empty($this->userParam['sorts'])) {
            $this->userParam['sorts'] = $this->sorts;
        }

        $sentParam = json_decode(App::request()->getHeaders('X-List-Data-'.$this->id), true);

        foreach($parameters as $paramName) {
            if(isset($sentParam[$paramName])) {
                $this->userParam[$paramName] = $sentParam[$paramName];
            }
        }

        App::session()->getUser()->setOption('main.list-'.$this->id, json_encode($this->userParam));

        foreach($parameters as $name) {
            if(!empty($this->userParam[$name])) {
                $this->$name = $this->userParam[$name];
            }
        }

        if(!$this->navigation) {
            $this->lines = self::ALL_LINES;
        }

        // initialize fields default values
        foreach($this->fields as $name => &$field) {
            if(is_array($field)) {
                $field = new ItemListField($name, $field, $this);
                if(isset($this->searches[$name])) {
                    $field->searchValue = $this->searches[$name];
                }

                if(!empty($this->sorts[$name])) {
                    $field->sortValue = $this->sorts[$name];
                }
            }
            else{
                unset($this->fields[$name]);
            }
        }

        App::getInstance()->trigger('list.instanciated', array(
            'list' => $this
        ));

        App::getInstance()->trigger('list.' . $this->id . '.instanciated', array(
            'list' => $this
        ));
    }


    /**
     * Get the data to display
     *
     * @return array The data to display
     */
    public function get() {
        if(isset($this->data) && is_array($this->data)) {
            return $this->getFromArray($this->data);
        }
        else if($this->model && $this->table) {
            return $this->getFromDatabase();
        }

    }


    /**
     * Get the data from the database
     *
     * @return array The data taken from the databases
     */
    private function getFromDatabase() {
        $fields = array();

        $where = array();

        if(!empty($this->filterForm)) {
            $filters = $this->getFilterFormValue();

            foreach($this->filterForm as $name => $field) {
                if(isset($filters[$name]) && $filters[$name] !== '') {
                    switch($field['type']) {
                        case 'checkbox' :
                            $values = array_filter($filters[$name]);

                            if(!empty($values)) {
                                $where[] = DBExample::make(array(
                                    $name => array(
                                        '$in' => array_keys($values)
                                    )
                                ), $this->binds);
                            }
                            break;

                        default :
                            $where[] = DBExample::make(array(
                                $name => $filters[$name]
                            ), $this->binds);
                            break;
                    }
                }
            }
        }


        if(!empty($this->filter)) {
            if($this->filter instanceof DBExample) {
                $where[] = $this->filter->parse($this->binds);
            }
            else if(is_array($this->filter)) {
                $where[]     = $this->filter[0];
                $this->binds = $this->filter[1];
            }
            else{
                $where[] = $this->filter;
            }
        }

        // insert the reference if not present in the fields
        if(!isset($this->fields[$this->refAlias])) {
            $this->fields[$this->refAlias] = new ItemListField(
                $this->refAlias,
                array(
                    'field'  => $this->refField,
                    'hidden' => true,
                ),
                $this
            );
        }

        // Prepare the fields to research
        $searches = array();
        foreach($this->fields as $name => &$field){
            if(!$field->independant) {
                $fields[$this->dbo->formatField($field->field)] = $this->dbo->formatField($name);

                // Get the pattern condition
                $sql = $field->getSearchCondition($this->binds);
                if($sql) {
                    $where[] = $sql;
                }
            }
        }

        try{
            $where = implode(' AND ', $where);

            // Utils::debug($where);

            $model = $this->model;
            $this->recordNumber = $this->dbo->count($this->table, $where, $this->binds, $this->refField, $this->group);

            // Get the number of the page
            if($this->lines == self::ALL_LINES) {
                $this->lines = $this->recordNumber ? $this->recordNumber : 1;
            }

            if($this->page > 1 && $this->page > ceil($this->recordNumber / $this->lines)) {
                $this->page = (ceil($this->recordNumber / $this->lines) > 0) ? ceil($this->recordNumber / $this->lines) : 1;
            }

            $this->start = ($this->page - 1) * $this->lines;

            // Get the data from the database
            $request = array(
                'fields'  => $fields,
                'from'    => $this->table,
                'where'   => $where,
                'binds'   => $this->binds,
                'orderby' => $this->sorts,
                'group'   => $this->group,
                'start'   => $this->start,
                'limit'   => $this->lines,
                'index'   => $this->refAlias,
                'return'  => $this->model,
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
     *
     * @param array $data The array where to take the data to display
     */
    private function getFromArray($data) {
        foreach($this->fields as $name => &$field){
            $pattern = isset($this->searches[$name]) ? $this->searches[$name] : null;
            if($pattern) {
                $data = array_filter(
                    $data,
                    function ($line) use ($pattern, $name) {
                        return stripos($line->$name, $pattern) !== false;
                    }
                );
            }
        }

        if(!empty($this->sorts)) {
            usort(
                $data,
                function ($a, $b) {
                    foreach($this->sorts as $name => $sort) {

                        if(!$sort) {
                            continue;
                        }
                        if(is_array($a) && is_array($b)) {
                            if($a[$name] == $b[$name]) {
                                continue;
                            }
                        }
                        else {
                            if($a->$name == $b->$name) {
                                continue;
                            }
                        }

                        if($sort === DB::SORT_ASC) {
                            return (is_array($a) ? $a[$name] < $b[$name] : $a->$name < $b->$name) ? -1 : 1;
                        }
                        else{
                            return (is_array($a) ? $b[$name] < $a[$name] : $b->$name < $a->$name) ? -1 : 1;
                        }
                    }
                }
            );
        }

        $this->recordNumber = count($data);

        if($this->lines == self::ALL_LINES) {
            $this->lines = $this->recordNumber ? $this->recordNumber : 1;
        }

        if($this->page > ceil($this->recordNumber / $this->lines) && $this->page > 1) {
            $this->page = (ceil($this->recordNumber / $this->lines) > 0) ? ceil($this->recordNumber / $this->lines) : 1;
        }

        $this->start   = (($this->page - 1) * $this->lines);
        $this->results = array_slice(
            array_map(
                function ($line) {
                    return (object) $line;
                },
                $data
            ),
            $this->start,
            $this->lines
        );

        return $this->results;

    }


    /**
     * Set the list data
     *
     * @param array $data The data to set
     */
    public function set($data) {
        $this->getFromArray($data);

    }


    /**
     * Get the list views files
     */
    private function getViews() {
        if(empty($this->tpl)) {
            $this->tpl = Theme::getSelected()->getView('item-list/container.tpl');
        }

        if(empty($this->navigationBarTpl)) {
            $this->navigationBarTpl = Theme::getSelected()->getView('item-list/navigation-bar.tpl');
        }

        if(empty($this->listTpl)) {
            $this->listTpl = Theme::getSelected()->getView('item-list/list.tpl');
        }

        if(empty($this->resultTpl)) {
            $this->resultTpl = Theme::getSelected()->getView('item-list/result.tpl');
        }

    }


    /**
     * Display the list (alias)
     *
     * @return string The HTML result of displaying
     */
    public function __toString() {
        return $this->display();
    }


    /**
     * Display the list
     *
     * @return string The HTML result of displaying
     */
    public function display() {
        try {
            $pages = 1;
            $data = array();
            $param = array();
            $this->recordNumber = 0;

            // Get the data to display
            $this->get();

            // Get the total number of pages
            $pages = (ceil($this->recordNumber / $this->lines) > 0) ? ceil($this->recordNumber / $this->lines) : 1;

            if(!empty($this->customize)) {
                $displayedFields = !empty($this->userParam['displayedFields']) ?
                    $this->userParam['displayedFields'] :
                    $this->customize['default'];

                $controlFields = array();
                foreach($this->fields as $name => $field) {
                    if(!$field->hidden) {
                        $controlFields[] = array(
                            'name' => $name,
                            'label' => $field->label ? $field->label : $name
                        );
                    }
                }

                // Add control button
                $this->controls[] = array(
                    'icon' => 'cogs',
                    'class' => 'btn-default',
                    'href' => App::router()->getUri(
                        'customize-list',
                        array(
                            'id' => $this->id
                        ),
                        array(
                            'fields' => json_encode($controlFields),
                            'displayed' => json_encode($displayedFields),
                            'immutable' => json_encode(
                                isset($this->customize['immutable']) ? $this->customize['immutable'] : array()
                            )
                        )
                    ),
                    'target' => 'dialog'
                );

                $tmp = $this->fields;
                $this->fields = array();

                foreach($displayedFields as $field) {
                    if(isset($tmp[$field])) {
                        $this->fields[$field] = $tmp[$field];
                    }
                }
            }


            // At least one result to display
            $data  = array();
            $param = array();
            if(is_array($this->results)) {
                foreach($this->results as $id => $line){
                    $data[$id]  = array();
                    $param[$id] = array('class' => '');

                    if($this->lineClass) {
                        $function = $this->lineClass;
                        $param[$id]['class'] .= $function($line);
                    }

                    foreach($this->fields as $name => $field){
                        $data[$id][$name] = $field->displayCell($id);
                    }
                }
            }

            // Get the list views files
            $this->getViews();

            $content = View::make($this->resultTpl, array(
                'list' => $this,
                'data' => $data,
                'linesParameters' => $param,
                'pages' => $pages,
            ));

            $content = str_replace(array("\r", "\n"), '', $content);

            if($this->refresh && !$this->rebuild) {
                App::response()->setContentType('json');

                return array(
                    'htmlResult' => $content,
                    'maxPages' => $pages,
                    'recordNumber' => $this->recordNumber
                );
            }

            $result = View::make($this->tpl, array(
                'list' => $this,
                'data' => $data,
                'linesParameters' => $param,
                'pages' => $pages,
            )) .
            View::make(Plugin::get('main')->getView('list.js.tpl'), array(
                'list' => $this,
                'pages' => $pages,
                'htmlResult' => addslashes($content),
                'maxPages' => $pages
            ));

            if($this->rebuild) {
                App::response()->end($result);
            }
            else {
                return $result;
            }
        }
        catch(\Exception $e){
            App::errorHandler()->exception($e);
        }
    }


    /**
     * Check if the list is refreshing or displayed for the first time
     */
    public function isRefreshing() {
        return $this->refresh;
    }

    /**
     * Export the list data as CSV file
     * @param  string $format The exporation format (csv, json, xml)
     * @param  bool $pretty Prettify the output (available for json and xml formats)
     * @return string Th CSV content
     */
    public function export($format, $pretty = false) {
        $outFilename = $this->id . '.' . $format;

        $response = App::response();
        $response->setContentType('application/octet-stream; charset=utf-8');
        $response->header('Content-Transfer-Encoding', 'Binary');
        $response->header('Content-Disposition', 'attachment; filename="' . $outFilename . '"');

        $this->lines = self::ALL_LINES;

        $this->get();

        if(!empty($this->customize)) {
            $displayedFields = !empty($this->userParam['displayedFields']) ?
                $this->userParam['displayedFields'] :
                $this->customize['default'];

            $tmp = $this->fields;
            $this->fields = array();

            foreach($displayedFields as $field) {
                if(isset($tmp[$field])) {
                    $this->fields[$field] = $tmp[$field];
                }
            }
        }

        $output = fopen('php://output', 'w');
        switch($format) {
            case 'csv' :

                // Write the first line with the columns names
                fputcsv(
                    $output,
                    array_map(function($field) {
                        return $field->label;
                    }, $this->fields),
                    ';'
                );


                if(is_array($this->results)) {
                    foreach($this->results as $id => $line){
                        $line = array();

                        foreach($this->fields as $name => $field) {
                            $line[] = trim(strip_tags($field->displayCell($id)));
                        }

                        fputcsv($output, $line, ';');
                    }
                }
                break;


            case 'json' :
                $data = array();

                if(is_array($this->results)) {
                    foreach($this->results as $id => $line){
                        $line = array();

                        foreach($this->fields as $name => $field) {
                            $line[$name] = trim(strip_tags($field->displayCell($id)));
                        }

                        $data[] = $line;
                    }
                }

                $flag = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

                if($pretty) {
                    $flag = $flag | JSON_PRETTY_PRINT;
                }

                fputs($output, json_encode($data, $flag));
                break;

            case 'xml' :
                $list = new \SimpleXMLElement('<list />');
                $list->addAttribute('id', $this->id);

                if(is_array($this->results)) {
                    foreach($this->results as $id => $line){
                        $line = $list->addChild('element');

                        foreach($this->fields as $name => $field) {
                            $line->addChild($name, trim(strip_tags($field->displayCell($id))));
                        }
                    }
                }

                if($pretty) {
                    $dom = dom_import_simplexml($list)->ownerDocument;
                    $dom->formatOutput = true;

                    $content = $dom->saveXml();
                }
                else {
                    $content = $list->asXML();
                }

                fputs($output, $content);
                break;


            default :
                break;
        }


        $response->end();
    }


    /**
     * Get the filter form values
     * @returns Object The filter form data
     */
    private function getFilterFormValue() {
        if(App::request()->getHeaders('X-List-Filter-' . $this->id)) {
            App::session()->getUser()->setOption('main.list-filter-' . $this->id, App::request()->getHeaders('X-List-Filter-' . $this->id));

            $result = json_decode(App::request()->getHeaders('X-List-Filter-' . $this->id), true);
        }
        elseif(App::session()->getUser()->getOptions('main.list-filter-' . $this->id)) {
            $result = json_decode(App::session()->getUser()->getOptions('main.list-filter-' . $this->id), true);
        }
        else {
            $result = array();
        }

        return $result;
    }


    /**
     * Get the filter form
     * @returns Form The form to display to filter the item list
     */
    public function getFilterForm() {
        $param = array(
            'id' => $this->id . '-filter-form',
            'attributes' => array(
                'onchange'  => 'app.lists["' . $this->id . '"].setFilter(app.forms["' . $this->id . '-filter-form"].valueOf())'
            ),
            'fieldsets' => array()
        );

        $filters = $this->getFilterFormValue();

        foreach($this->filterForm as $name => $field) {
            $param['fieldsets'][$name] = array();

            if(isset($field['legend'])) {
                $param['fieldsets'][$name]['legend'] = $field['legend'];
            }

            switch($field['type']) {
                case 'checkbox' :
                    foreach($field['options'] as $value => $label) {
                        $param['fieldsets'][$name][] = new CheckboxInput(array(
                            'name' => $name.'[' . $value . ']',
                            'value' => !empty($filters[$name][$value]),
                            'label' => $label,
                            'beforeLabel' => true,
                            'labelWidth'  => 'auto',
                        ));
                    }
                    break;

                case 'radio' :
                    $param['fieldsets'][$name][] = new RadioInput(array(
                        'name' => $name,
                        'value' => isset($filters[$name]) ? $filters[$name] : '',
                        'options' => $field['options'],
                        'layout' => isset($field['layout']) ? $field['layout'] : 'vertical'
                    ));
                    break;

                case 'select' :
                    $param['fieldsets'][$name][] = new SelectInput(array(
                        'name' => $name,
                        'value' => isset($filters[$name]) ? $filters[$name] : '',
                        'options' => $field['options'],
                        'invitation' => isset($field['invitation']) ? $field['invitation'] : null
                    ));
                    break;

                default :
                    break;
            }
        }

        return new Form($param);
    }
}
