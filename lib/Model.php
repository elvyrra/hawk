<?php
/**
 * Model.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the data models behavior. each model defined in plugin must inherits this class
 *
 * @package Core
 */
class Model {
    use Utils;

    /**
     * The table name containing the data in database
     *
     * @var string
     */
    protected static $tablename;

    /**
     * The primary column of the elements in the table (default 'id')
     *
     * @var string
     */
    protected static $primaryColumn = 'id';

    /**
     * The DB instance name to get data in database default MAINDB
     *
     * @var string
     */
    protected static $dbname = MAINDB;

    /**
     * The default charset of the table
     */
    const CHARSET = 'utf8';

    /**
     * The table engine
     */
    const ENGINE = 'InnoDB';

    /**
     * The model fields
     *
     * @var array
     */
    protected static $fields = array();

    /**
     * The model constraints
     *
     * @var array
     */
    protected static $constraints = array();


    /**
     * The model instances, by their id
     * @var array
     */
    protected static $instancesById = array();


    /**
     * Defines the timestamp fields
     * @var array
     */
    protected static $timestamps = array(
        'ctime' => '',
        'mtime' => ''
    );


    /**
     * Defines if the model is a new model or an existing one, got from the database
     * @var boolean
     */
    public $new = true;


    /**
     * The data errors
     * @var array
     */
    public $errors = array();


    /**
     * Constructor : Instanciate a new Model object
     *
     * @param array $data The initial data to set.
     */
    public function __construct($data = array()) {
        $this->map($data);
    }


    /**
     * Get all the elements of the table
     *
     * @param string $index  The field that will be used to affect the result array keys.
     *                        If not set, the method will return a non associative array
     * @param array  $fields The fields to set in the instances
     * @param array  $order  The order to get the results. Each key of this array must be a column name in the table,
     *                        and the associated value is the order value ('ASC' or 'DESC')
     *
     * @return array An array of all Model instances
     */
    public static function getAll($index = null, $fields = array(), $order = array()){
        return self::getListByExample(null, $index, $fields, $order);
    }


    /**
     * Get a model instance by it primary column
     *
     * @param int   $id     The id of the instance to get
     * @param array $fields The fields to set in the instance
     *
     * @return Model The found Model instance
     */
    public static function getById($id, $fields = array()) {
        if(!isset(self::$instancesById[get_called_class()][$id])) {
            $example = new DBExample(array(
                static::$primaryColumn => $id
            ));
            self::$instancesById[get_called_class()][$id] = self::getByExample($example, $fields);
        }

        return self::$instancesById[get_called_class()][$id];
    }


    /**
     * Get a model instance by an example
     *
     * @param DBExample $example The example to find the data line
     * @param array     $fields  The fields to set in the model instance
     *
     * @return Model The found Model instance
     */
    public static function getByExample(DBExample $example = null, $fields = array()){
        return self::getDbInstance('slave')->select(array(
            'fields' => $fields,
            'from' => static::getTable(),
            'where' => $example,
            'return' => get_called_class(),
            'one' => true,
        ));
    }


    /**
     * Get a list of model instances by an example
     *
     * @param DBExample $example The example to find the data lines
     * @param string    $index   The field that will be used as array key in the result.
     *                           If not set, the result will be a non associative array
     * @param array     $fields  The fields to set in the instances
     * @param array     $order   The order to get the results. Each key of this array must be a column name in the table,
     *                           and the associated value is the order value ('ASC' or 'DESC')
     *
     * @return Model[] The array containing found models
     */
    public static function getListByExample(DBExample $example = null, $index = null, $fields = array(), $order = array()){
        return self::getDbInstance('slave')->select(array(
            'fields' => $fields,
            'from' => static::getTable(),
            'where' => $example,
            'index' => $index,
            'return' => get_called_class(),
            'orderby' => $order
        ));
    }


    /**
     * Get a model instance by sql condition
     *
     * @param string $where  The SQL condition
     * @param array  $binds  The binded values
     * @param array  $fields The fields to set in the instance
     *
     * @return Model The found Model instance
     */
    public static function getBySQL($where = null, $binds = array(), $fields = array()){
        return self::getDbInstance('slave')->select(array(
            'fields' => $fields,
            'from' => static::getTable(),
            'where' => $where,
            'binds' => $binds,
            'return' => get_called_class(),
            'one' => true,
        ));
    }


    /**
     * Get a list of model instances by a SQL condition
     *
     * @param string $where  The SQL condition to find the elements
     * @param array  $binds  The binded values
     * @param string $index  The field that will be used as array key in the result.
     *                       If not set, the result will be a non associative array
     * @param array  $fields The fields to set in the instances
     * @param array  $order  The order to get the results. Each key of this array must be a column name in the table,
     *                        and the associated value is the order value ('ASC' or 'DESC')
     *
     * @return Model[] the array containing found models instances
     */
    public static function getListBySQL($where = null, $binds = array(), $index = null, $fields = array(), $order = array()){
        return self::getDbInstance('slave')->select(array(
            'fields' => $fields,
            'from' => static::getTable(),
            'where' => $where,
            'binds' => $binds,
            'index' => $index,
            'return' => get_called_class(),
            'orderby' => $order
        ));
    }



    /**
     * Delete data in the database from an example
     *
     * @param DBExample $example The example to find the lines to delete
     *
     * @return int the number of deleted elements in the database
     */
    public static function deleteByExample(DBExample $example = null){
        return self::getDbInstance('master')->delete(static::getTable(), $example);
    }

    /**
     * Delete data in the database from a SQL condition
     *
     * @param string $where The SQL condition to find the lines to delete
     * @param array  $binds - The binded values
     *
     * @return int The number of deleted elements in the database
     */
    public static function deleteBySQL($where = null, $binds = array()){
        return self::getDbInstance('master')->delete(static::getTable(), $where, $binds);
    }


    /**
     * Count the number of elements filtered by an example
     *
     * @param DBExample $example The example to find the lines to delete
     * @param array     $group   - The fields used to group the results
     *
     * @return int The number of found elements in the database
     */
    public static function countElementsByExample(DBExample $example = null, $group = array()){
        return self::getDbInstance('slave')->count(static::getTable(), $example, array(), self::getPrimaryColumn(), $group);
    }


    /**
     * Count the number of elements filtered by a SQL condition
     *
     * @param string $where The SQL condition
     * @param array  $binds - the binded values
     * @param array  $group - The fields used to group the results
     *
     * @return int The number of found elements in the database
     */
    public static function countElementsBySQL($where = null, $binds = array(),  $group = array()){
        return self::getDbInstance('slave')->count(static::getTable(), $where, $binds, self::getPrimaryColumn(), $group);
    }


    /**
     * Get the model fields
     */
    protected static function getFields() {
        if(!static::$fields) {
            static::$fields = self::getDbInstance('slave')->query(
                'SHOW COLUMNS FROM ' . self::getTable(),
                array(),
                array(
                    'index' => 'Field',
                    'return' => DB::RETURN_ARRAY
                )
            );
        }

        return static::$fields;
    }

    /**
     * Prepare the data to save in the database
     *
     * @return array The data to be inserted for method save or update
     */
    protected function prepareDatabaseData() {
        $fields = static::getFields();
        $insert = array();

        foreach(get_object_vars($this) as $key => $value){
            if(isset($fields[$key])) {
                $insert[$key] = $value;
            }
        }

        return $insert;
    }


    /**
     * Set the ctime field value
     */
    private function setCtime($time = null) {
        if($time === null) {
            $time = time();
        }

        if(!empty(static::$timestamps['ctime']) && isset(static::$fields[static::$timestamps['ctime']])) {
            $field = static::$timestamps['ctime'];


            $this->$field = $time;
        }

        $this->setMtime($time);
    }


    /**
     * Set the mtime field value
     */
    private function setMtime($time = null) {
        if($time === null) {
            $time = time();
        }

        if(!empty(static::$timestamps['mtime']) && isset(static::$fields[static::$timestamps['mtime']])) {
            $field = static::$timestamps['mtime'];

            $this->$field = $time;
        }
    }


    /**
     * This method save a new Model in the database or update it if it exists.
     * It is based on INSERT ... ON DUPLICATE KEY.
     * If a new element is saved, then the id (or the value of the primary key) is set on the instance corresponding property
     */
    public function save() {
        if($this->new) {
            $this->setCtime();
            $insert = $this->prepareDatabaseData();
            $lastid = self::getDbInstance('master')->insert(static::getTable(), $insert);

            if($lastid) {
                $id = static::$primaryColumn;
                $this->$id = $lastid;
            }
        }
        else {
            $this->update();
        }
    }


    /**
     * Create a new element in the database
     *
     * @param array $data The data to insert in the database
     *
     * @return Model The added instance
     */
    public static function add($data){
        $class = get_called_class();
        $instance = new $class($data);
        $instance->save();

        (new Event(strtolower($class).'.added', array('data' => $data)))->trigger();

        return $instance;
    }


    /**
     * Add the model in the database only if does not alreay exists
     */
    public function addIfNotExists(){
        $id = static::$primaryColumn;
        $this->setCtime();
        $insert = $this->prepareDatabaseData();

        $lastid = self::getDbInstance('master')->insert(static::getTable(), $insert, 'IGNORE');
        if($lastid) {
            $this->$id = $lastid;
        }
    }


    /**
     * Update the model data in the database
     */
    public function update() {
        $this->setMtime();
        $update = $this->prepareDatabaseData();
        $id = static::$primaryColumn;
        self::getDbInstance('master')->update(static::getTable(), new DBExample(array($id => $this->$id)), $update);
    }


    /**
     * Delete the model data from the database
     *
     * @return true if the data has been sucessfully removed from the database, false in other cases
     */
    public function delete() {
        $class = get_called_class();
        $id = static::$primaryColumn;
        $deleted = self::getDbInstance('master')->delete(static::getTable(), new DBExample(array($id => $this->$id)));

        (new Event(strtolower($class).'.deleted', array('object' => $this)))->trigger();

        return (bool) $deleted;
    }


    /**
     * Get the model data, only the data present in the database.
     *
     * @return array The object properties with their value
     */
    public function getData(){
        return get_object_vars($this);
    }



    /**
     * Set a property value to the object.
     * You can use this method to set only one property, or an array of properties
     *
     * @param string|array $field If a string is set, then it is the name of the property.
     *                            If an array is set, then set multiple properties will be set
     * @param mixed        $value The value to set to the property, only if $field is a string
     */
    public function set($field, $value = null){
        if(is_array($field) && $value === null) {
            foreach($field as $key => $value){
                $this->set($key, $value);
            }
        }
        else{
            $this->$field = $value;
        }
    }


    /**
     * Get the table name of this model
     *
     * @return string the table name of the model
     */
    public static function getTable(){
        return (static::$dbname == MAINDB ? App::conf()->get('db.prefix') : '') . static::$tablename;
    }


    /**
     * Get the primary column of the model
     *
     * @return string The primary column of the model
     */
    public static function getPrimaryColumn() {
        return isset(static::$primaryColumn) ? static::$primaryColumn : self::$primaryColumn;
    }


    /**
     * Get the DB instance name of the model
     *
     * @return string The name of the DB instance for the model
     */
    public static function getDbName() {
        return static::$dbname;
    }


    /**
     * Get the DB instance of the model
     *
     * @param string $replication The replication to get
     * @return DB The DB instance of the model
     */
    public static function getDbInstance($replication = 'master') {
        return DB::get(static::$dbname, $replication);
    }


    /**
     * Set the table name of the model
     *
     * @param string $table The table name to set
     */
    public static function setTable($table){
        static::$tablename = $table;
    }

    /**
     * Set the primary column of the model
     *
     * @param string $primaryColumn The column to set as primary column
     */
    public static function setPrimaryColumn($primaryColumn){
        static::$primaryColumn = $primaryColumn;
    }

    /**
     * Set the DB instance name of the model
     *
     * @param string $name The instance name to set
     */
    public static function setDbName($name){
        static::$dbname = $name;
    }

    /**
     * Create the model corresponding table. This method bases on the static properties
     * $tablename, $fields, $constraints
     */
    public static function createTable() {
        /**
         * Create the table
         */
        $createTableSql = 'CREATE TABLE IF NOT EXISTS ' . self::getTable() . '(';
        $fieldsInstructions = array();

        // Add field definitions
        foreach(static::$fields as $fieldname => $options) {
            if(empty($options['type'])) {
                trigger_error('The field ' . $fieldname . ' must have a defined type');
            }

            $fieldsInstructions[] = self::getFieldDefinition($fieldname, $options);
        }

        // Add primary key definition
        $primaryColumn = static::getPrimaryColumn();
        if($primaryColumn !== null) {
            if(is_array($primaryColumn)) {
                $primaryColumn = implode(
                    ',',
                    array_map(function ($field) {
                        return DB::formatField($field);
                    }, $primaryColumn)
                );
            }
            else {
                $primaryColumn = DB::formatField($primaryColumn);
            }
            $fieldsInstructions[] .= 'PRIMARY KEY (' . $primaryColumn . ')';
        }

        // Add constraints
        if(!empty(static::$constraints)) {
            foreach(static::$constraints as $name => $constraint) {
                $fieldsInstructions[] = self::getConstraintDefinition($name, $constraint);
            }
        }

        $createTableSql .=  implode(',', $fieldsInstructions) .
                            ') ENGINE=' . static::ENGINE . ' DEFAULT CHARSET=' . static::CHARSET;

        self::getDbInstance('master')->query($createTableSql);
    }

    /**
     * Drop the model table
     */
    public static function dropTable() {
        self::getDbInstance('master')->query('DROP TABLE IF EXISTS ' . static::getTable());
    }


    /**
     * Update the model table structure
     */
    public static function updateTable() {
        $instance = self::getDbInstance('master');

        // Get the fields currently in the database
        $dbFields = $instance->query(
            'SHOW COLUMNS FROM ' . static::getTable(),
            array(),
            array(
                'index' => 'Field',
                'return' => DB::RETURN_ARRAY
            )
        );

        $dbFields = array_map(function ($field) {
            return array(
                'type' => $field['Type'],
                'null' => $field['Null'] == 'YES',
                'default' => $field['Default'],
                'auto_increment' => $field['Extra'] === 'auto_increment'
            );
        }, $dbFields);

        $modelFields = static::$fields;

        // Build the instructions to execute to update the table
        $instructions = array();

        // Build the instructions about fields structures
        foreach($dbFields as $fieldname => $dbField) {
            if(!isset($modelFields[$fieldname])) {
                // The field does not exists anymore

                // Try to find if it has been renamed
                $renamed = array_filter($modelFields, function ($field) use ($fieldname) {
                    return !empty($field['oldName']) && $field['oldName'] === $fieldname;
                });

                if (!empty($renamed)) {
                    // The field has been renamed
                    $newName = reset(array_keys($renamed));
                    $renamed = $renamed[$newName];

                    $instruction = 'ALTER TABLE ' . DB::formatField(self::getTable()) . ' CHANGE ' .
                                    DB::formatField($fieldname) . ' ' . self::getFieldDefinition($newName, $renamed);

                    $instructions[] = $instruction;

                    // Remove the field from the model fields, because it has been treated
                    unset($modelFields[$newName]);
                }
                else {
                    // The field has been removed
                    $instructions[] = 'ALTER TABLE ' . DB::formatField(self::getTable()) . ' DROP COLUMN ' . DB::formatField($fieldname);

                    // Remove the field from the model fields, because it has been treated
                    unset($modelFields[$fieldname]);
                }

            }
            else {
                // The field exists, check if it has been modified
                $modelField = $modelFields[$fieldname];

                if(!isset($modelField['null'])) {
                    $modelField['null'] = false;
                }

                if(!isset($modelField['default'])) {
                    $modelField['default'] = null;
                }

                if(!isset($modelField['auto_increment'])) {
                    $modelField['auto_increment'] = false;
                }


                if($modelField != $dbField) {
                    // The field has been modified
                    $instructions[] = 'ALTER TABLE ' . DB::formatField(self::getTable()) . ' MODIFY ' .
                                        self::getFieldDefinition($fieldname, $modelFields[$fieldname]);

                }

                // Remove the field from the model fields, because it has been treated
                unset($modelFields[$fieldname]);
            }
        }

        // Now check if new fields have to be created
        foreach($modelFields as $fieldname => $properties) {
            $instructions[] = 'ALTER TABLE ' . DB::formatField(self::getTable()) . ' ADD COLUMN ' .
                                self::getFieldDefinition($fieldname, $modelFields[$fieldname]);
        }


        // Build instructions about constraints
        // Get constaints currently saved in th database

        // First get indexes
        $dbIndexes = $instance->query('SHOW INDEX FROM ' . self::getTable(), array(), array(
            'return' => DB::RETURN_ARRAY
        ));

        $dbConstraints = array();

        foreach($dbIndexes as $index) {
            $name = $index['Key_name'];
            if($name === 'PRIMARY') {
                // No treat primary keys
                continue;
            }

            if(empty($dbConstraints[$name])) {
                $dbConstraints[$name] = array(
                    'fields' => array()
                );
            }

            $dbConstraints[$name]['type'] = $index['Non_unique'] ? 'index' : 'unique';
            $dbConstraints[$name]['fields'][] = $index['Column_name'];
        }

        // Get foreign keys
        $dbFKeys = $instance->query(
            'SELECT K.COLUMN_NAME, K.CONSTRAINT_NAME, K.REFERENCED_TABLE_NAME, K.REFERENCED_COLUMN_NAME, R.UPDATE_RULE, R.DELETE_RULE
            FROM information_schema.KEY_COLUMN_USAGE K INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS R
                ON K.TABLE_SCHEMA = R.CONSTRAINT_SCHEMA AND K.CONSTRAINT_NAME = R.CONSTRAINT_NAME
            WHERE K.TABLE_SCHEMA = :dbname AND K.TABLE_NAME = :tablename AND K.REFERENCED_TABLE_NAME IS NOT NULL',
            array(
                'dbname' => $instance->dbname,
                'tablename' => self::getTable()
            ),
            array(
                'return' => DB::RETURN_ARRAY
            )
        );

        foreach($dbFKeys as $fkey) {
            $name = $fkey['CONSTRAINT_NAME'];
            if(empty($dbConstraints[$name])) {
                $dbConstraints[$name] = array(
                    'fields' => array()
                );
            }

            $dbConstraints[$name]['type'] = 'foreign';

            if(!in_array($fkey['COLUMN_NAME'], $dbConstraints[$name]['fields'])) {
                $dbConstraints[$name]['fields'][] = $fkey['COLUMN_NAME'];
            }

            if(empty($dbConstraints[$name]['references'])) {
                $dbConstraints[$name]['references'] = array(
                    'table' => $fkey['REFERENCED_TABLE_NAME'],
                    'fields' => array()
                );
            }
            if(!in_array($fkey['REFERENCED_COLUMN_NAME'], $dbConstraints[$name]['references']['fields'])) {
                $dbConstraints[$name]['references']['fields'][] = $fkey['REFERENCED_COLUMN_NAME'];
            }

            $dbConstraints[$name]['on_update'] = $fkey['UPDATE_RULE'];
            $dbConstraints[$name]['on_delete'] = $fkey['DELETE_RULE'];
        }

        $modelConstraints = array();
        foreach(static::$constraints as $name => $constraint) {
            $name = self::getTable() . $name;
            $modelConstraints[$name] = $constraint;

            if(!empty($constraint['references']['model'])) {
                $class = $constraint['references']['model'];

                if(!class_exists($class)) {
                    $reflection  = new \ReflectionClass(get_called_class());
                    $class = $reflection->getNamespaceName() . '\\'. $class;
                }

                $modelConstraints[$name]['references']['table'] = $class::getTable();
                unset($modelConstraints[$name]['references']['model']);
            }
        }

        foreach($dbConstraints as $name => $constraint) {
            $deleteType = $constraint['type'] === 'foreign' ? 'FOREIGN KEY' : 'INDEX';

            if(!isset($modelConstraints[$name])) {
                // The constraint has been removed
                $instructions[] = 'ALTER TABLE ' . DB::formatField(self::getTable()) .
                                    ' DROP ' . $deleteType . ' `' . $name . '`';
            }
            elseif($constraint != $modelConstraints[$name]) {
                // The constraint properties changed
                $instructions[] = 'ALTER TABLE ' . DB::formatField(self::getTable()) .
                                    ' DROP ' . $deleteType . ' `' . $name . '`';

                $instructions[] = 'ALTER TABLE ' . DB::formatField(self::getTable()) . ' ADD ' .
                                    self::getConstraintDefinition($name, $constraint);
            }

            unset($modelConstraints[$name]);
        }

        foreach($modelConstraints as $name => $constraint) {
            // The constaint has been created
            $instructions[] = 'ALTER TABLE ' . DB::formatField(self::getTable()) . ' ADD ' .
                                    self::getConstraintDefinition($name, $constraint);
        }

        // Execute the SQL insctructions
        foreach($instructions as $instruction) {
            $instance->query($instruction);
        }
    }


    /**
     * Generate a SQL expression for a field definition
     *
     * @param string $fieldname  The name of the field
     * @param array  $properties The field properties
     *
     * @return string The SQL expression
     */
    private static function getFieldDefinition($fieldname, $properties) {
        $definition = DB::formatField($fieldname);

        $definition .= ' ' . $properties['type'] ;

        if(!isset($properties['null'])) {
            $properties['null'] = false;
        }
        $definition .= $properties['null'] ? ' NULL' : ' NOT NULL';

        if(isset($properties['default'])) {
            switch($properties['default']) {
                case null :
                    $default = 'NULL';
                    break;

                case 'CURRENT_TIMESTAMP' :
                    $default = 'CURRENT_TIMESTAMP';
                    break;

                default :
                    $default = App::db()->quote($properties['default']);
            }

            $definition .= ' DEFAULT ' . $default;
        }

        if(!empty($properties['auto_increment'])) {
            $definition .= ' AUTO_INCREMENT';
        }

        return $definition;
    }


    /**
     * Generate a SQL expression for a constraint definition
     *
     * @param string $name       The constraint name
     * @param array  $properties The constraint properties. This array can contains the following properties :
     *                           <ul>
     *                              <li>type (string) : The constraint type. Must be 'index', 'unique', 'foreign', 'fulltext' or empty</li>
     *                              <li>fields (array) : The fields the constraints is applied on</li>
     *                              <li>references(array) : For a 'foreign' constraint, this array defines :
     *                                  <ul>
     *                                      <li>model (string) : The model class defining the table the constraints references</li>
     *                                      <li>fields (array) : The fields the constaints references </li>
     *                                  </ul>
     *                              <li>on_update (string) : For 'a foreign' constaints, define the action to perform ON UPDATE</li>
     *                              <li>on_delete (string) : For 'a foreign' constaints, define the action to perform ON DELETE</li>
     *                          </ul>
     *
     * @return string The SQL expression
     */
    private static function getConstraintDefinition($name, $properties) {
        $sql = '';
        $constraintName = self::getTable() . $name;

        if(empty($properties['type'])) {
            $properties['type'] = 'index';
        }

        if($properties['type'] === 'foreign') {
            $referencedModel = $properties['references']['model'];
            if(!class_exists($referencedModel)) {
                $reflection  = new \ReflectionClass(get_called_class());
                $referencedModel = $reflection->getNamespaceName() . '\\'. $referencedModel;
            }

            $constaintName = DB::formatField($constraintName);

            $onFields = implode(',', array_map(function ($field) {
                return DB::formatField($field);
            }, $properties['fields']));

            $referenceTable =   DB::formatField($referencedModel::getDbInstance()->dbname) . '.' .
                                DB::formatField($referencedModel::getTable());

            $referenceFields = implode(',', array_map(function ($field) {
                return DB::formatField($field);
            }, $properties['references']['fields']));

            $onUpdate = isset($properties['on_update']) ? $properties['on_update'] : 'RESTRICT';

            $onDelete = isset($properties['on_delete']) ? $properties['on_delete'] : 'RESTRICT';

            return 'CONSTRAINT ' . DB::formatField($constraintName) . ' ' .
                    'FOREIGN KEY (' . $onFields . ') '.
                    'REFERENCES ' . $referenceTable . ' (' . $referenceFields . ') ' .
                    'ON UPDATE ' . $onUpdate . ' ' .
                    'ON DELETE ' . $onDelete;
        }
        else {
            switch($properties['type']) {
                case 'unique' :
                    $keyword =  'UNIQUE KEY ';
                    break;

                case 'fulltext' :
                    $keyword = 'FULLTEXT ';
                    break;

                default :
                    $keyword = 'KEY ';
                    break;
            }

            $onFields =  implode(',', array_map(function ($field) {
                return DB::formatField($field);
            }, $properties['fields']));

            return  $keyword . DB::formatField($constraintName) . '(' . $onFields . ')';
        }
    }
}