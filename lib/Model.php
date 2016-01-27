<?php
/**
 * Model.php
 */

namespace Hawk;

/**
 * This class describes the data models behavior. each model defined in plugin must inherits this class
 * @package Core
 */
class Model{
    use Utils;

    /**
     * The table name containing the data in database
     * @var string
     */
    protected static $tablename;

    /**
     * The primary column of the elements in the table (default 'id')
     * @var string
     */
    protected static $primaryColumn = 'id';

    /**
     * The DB instance name to get data in database default MAINDB
     * @var string
     */
    protected static $dbname = MAINDB;


    /**
     * Constructor : Instanciate a new Model object
     * @param array $data The initial data to set.
     */
	public function __construct($data = array()){
        $this->map($data);
    }


    /**
     * Get all the elements of the table
     * @param string $index The field that will be used to affect the result array keys. If not set, the method will return a non associative array
     * @param array $fields The fields to set in the instances
     * @param array $order The order to get the results. Each key of this array must be a column name in the table, and the associated value is the order value ('ASC' or 'DESC')
     * @return array An array of all Model instances
     */
    public static function getAll($index = null, $fields = array(), $order = array()){
        return self::getListByExample(null, $index, $fields, $order);
    }


    /**
     * Get a model instance by it primary column
     * @param int $id The id of the instance to get
     * @param array $fields The fields to set in the instance
     * @return Model The found Model instance
     */
    public static function getById($id, $fields = array()){
        $example = new DBExample(array(static::$primaryColumn => $id));
		return self::getByExample($example, $fields);
	}


	/**
	 * Get a model instance by an example
	 * @param DBExample $example The example to find the data line
	 * @param array $fields The fields to set in the model instance
	 * @return Model The found Model instance
	 */
	public static function getByExample(DBExample $example = null, $fields = array()){
        return self::getDbInstance()->select(array(
            'fields' => $fields,
            'from' => static::getTable(),
            'where' => $example,
            'return' => get_called_class(),
            'one' => true,
        ));
	}


	/**
	 * Get a list of model instances by an example
	 * @param DBExample $example The example to find the data lines
	 * @param string $index The field that will be used as array key in the result. If not set, the result will be a non associative array
	 * @param array $fields The fields to set in the instances
	 * @param array $order The order to get the results. Each key of this array must be a column name in the table, and the associated value is the order value ('ASC' or 'DESC')
	 * @return Model[] The array containing found models
	 */
    public static function getListByExample(DBExample $example = null, $index = null, $fields = array(), $order = array()){
		return self::getDbInstance()->select(array(
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
	 * @param string $where The SQL condition
	 * @param array $binds The binded values
	 * @param array $fields The fields to set in the instance
	 * @return Model The found Model instance
	 */
	public static function getBySQL($where = null, $binds = array(), $fields = array()){
		return self::getDbInstance()->select(array(
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
	 * @param string $where The SQL condition to find the elements
	 * @param array $binds The binded values
	 * @param string $index The field that will be used as array key in the result. If not set, the result will be a non associative array
	 * @param array $fields The fields to set in the instances
	 * @param array $order The order to get the results. Each key of this array must be a column name in the table, and the associated value is the order value ('ASC' or 'DESC')
	 * @return Model[] the array containing found models instances
	 */
    public static function getListBySQL($where = null, $binds = array(), $index = null, $fields = array(), $order = array()){
		return self::getDbInstance()->select(array(
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
	 * @param DBExample $example The example to find the lines to delete
     * @return int the number of deleted elements in the database
	 */
	public static function deleteByExample(DBExample $example = null){
		return self::getDbInstance()->delete(static::getTable(), $example);
	}

	/**
	 * Delete data in the database from a SQL condition
	 * @param string $where The SQL condition to find the lines to delete
	 * @param array $binds - The binded values
     * @return int The number of deleted elements in the database
	 */
	public static function deleteBySQL($where = null, $binds = array()){
		return self::getDbInstance()->delete(static::getTable(), $where, $binds);
	}


	/**
	 * Count the number of elements filtered by an example
	 * @param DBExample $example The example to find the lines to delete
	 * @param array $group - The fields used to group the results
     * @return int The number of found elements in the database
	 */
    public static function countElementsByExample(DBExample $example = null, $group = array()){
		return self::getDbInstance()->count(static::getTable(), $example, array(), self::$primaryColumn, $group);
	}


    /**
	 * Count the number of elements filtered by a SQL condition
	 * @param string $where The SQL condition
	 * @param array $binds - the binded values
	 * @param array $group - The fields used to group the results
     * @return int The number of found elements in the database
	 */
    public static function countElementsBySQL($where = null, $binds = array(),  $group = array()){
		return self::getDbInstance()->count(static::getTable(), $where, $binds, self::$primaryColumn, $group);
	}


    /**
     * prepare the data to save in the database
     * @return array The data to be inserted for method save or update
     */
    private function prepareDatabaseData(){
        $fields = self::getDbInstance()->query('SHOW COLUMNS FROM ' . self::getTable(), array(), array('index' => 'Field', 'return' => DB::RETURN_ARRAY));

        $insert = array();
		foreach(get_object_vars($this) as $key => $value){
            if(isset($fields[$key])){
                $insert[$key] = $value;
            }
		}

        return $insert;
    }


    /**
     * This method save a new Model in the database or update it if it exists. It is based on INSERT ... ON DUPLICATE KEY.
     * if a new element is saved, then the created id is set to the instance
     */
	public function save(){
		$insert = $this->prepareDatabaseData();
        $duplicateUpdates = array_map(function($key){
			if($key == static::$primaryColumn){
				return "`$key`=LAST_INSERT_ID(`$key`)";
			}
			else{
            	return "`$key` = VALUES(`$key`)";
			}
        }, array_keys($insert));

		if(!isset($insert[static::$primaryColumn])){
			$key = static::$primaryColumn;
            $duplicateUpdates[] = "`$key`=LAST_INSERT_ID(`$key`)";
		}
        $onduplicate = implode(', ', $duplicateUpdates);

        $lastid = self::getDbInstance()->insert(static::getTable(), $insert, '', $onduplicate);
        if($lastid){
            $id = static::$primaryColumn;
            $this->$id = $lastid;
        }
	}


    /**
     * Create a new element in the database
     * @static
     * @param array $data The data to insert in the database
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
		$insert = $this->prepareDatabaseData();

        $lastid = self::getDbInstance()->insert(static::getTable(), $insert, 'IGNORE');
        if($lastid){
            $this->$id = $lastid;
        }
    }


    /**
     * Update the model data in the database
     */
    public function update(){
        $update = $this->prepareDatabaseData();
        $id = static::$primaryColumn;
        self::getDbInstance()->update(static::getTable(), new DBExample(array($id => $this->$id)), $update);
    }


    /**
     * Delete the model data from the database
     * @return true if the data has been sucessfully removed from the database, false in other cases
     */
	public function delete(){
		$class = get_called_class();
		$id = static::$primaryColumn;
		$deleted = self::getDbInstance()->delete(static::getTable(), new DBExample(array($id => $this->$id)));

        (new Event(strtolower($class).'.deleted', array('object' => $this)))->trigger();

        return (bool) $deleted;
	}


    /**
     * Get the model data, only the data present in the database.
     * @return array The object properties with their value
     */
	public function getData(){
		return get_object_vars($this);
	}



    /**
     * Set a property value to the object. You can use this method to set only one property, or an array of properties (where key are the names of the properties to set, and values their values)
     * @param string|array $field If a string is set, then it is the name of the property. If an array is set, then set multiple properties will be set
     * @param mixed $value The value to set to the property, only if $field is a string
     */
    public function set($field, $value = null){
        if(is_array($field) && $value === null){
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
     * @return string the table name of the model
     */
    public static function getTable(){
        return (static::$dbname == MAINDB ? App::conf()->get('db.prefix') : '') . static::$tablename;
    }


    /**
     * Get the primary column of the model
     * @return string The primary column of the model
     */
    public static function getPrimaryColumn(){
        return static::$primaryColumn;
    }


    /**
     * Get the DB instance name of the model
     * @return string The name of the DB instance for the model
     */
    public static function getDbName(){
        return static::$dbname;
    }


    /**
     * Get the DB instance of the model
     * @return DB The DB instance of the model
     */
    public static function getDbInstance(){
        return DB::get(static::$dbname);
    }


    /**
     * Set the table name of the model
     * @param string $table The table name to set
     */
    public static function setTable($table){
        static::$tablename = $table;
    }

    /**
     * Set the primary column of the model
     * @param string $primaryColumn The column to set as primary column
     */
    public static function setPrimaryColumn($primaryColumn){
        static::$primaryColumn = $primaryColumn;
    }

    /**
     * Set the DB instance name of the model
     * @param string $name The instance name to set
     */
    public static function setDbName($name){
        static::$dbname = $name;
    }
}