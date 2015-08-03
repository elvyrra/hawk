<?php


class Model{
	// The name of the table containing the data of the model
	protected static $tablename;
	protected static $primaryColumn;
    protected static $dbname = MAINDB;
    	
	const DBNAME = MAINDB;
	protected $dbvars = array();
    
	public function __construct($data = array()){
        foreach($data as $key => $value){
            $this->$key = $value;
        }
        
		foreach(get_object_vars($this) as $key => $value){
			if($key != 'dbvars'){
				$this->dbvars[] = $key;
			}
		}
    }
    

    /**
     * Get all the elements of the table 
     * @return {array} - A list of Models
     */
    public static function getAll($index = null, $fields = array(), $order = array()){        
        return self::getListByExample(new DBExample(array()), $index, $fields, $order);
    }
    

    /**
     * Get a model instance by Id
     * @param {int} $id - The id of the instance to get
     * @param {array} $fields - The fields to set in the instance
     * @return {Model} - The model with the id
     */
    public static function getById($id, $fields = array()){
        $example = new DBExample(array(static::$primaryColumn => $id));        
		return self::getByExample($example, $fields);
	}
    

	/**
	 * Get a model instance by an example
	 * @param {DBExample} $example - The example to find the data line
	 * @param {array} $fields - The fields to get out
	 * @return {Model} - the model found
	 */
	public static function getByExample(DBExample $example, $fields = array()){
        return self::getDbInstance()->select(array(
            'fields' => $fields,
            'from' => static::$tablename,
            'where' => $example,
            'return' => get_called_class(),
            'one' => true,
        ));        
	}
	
	
	/**
	 * Get a list of model instances by an example
	 * @param {DBExample} $example - The example to find the data lines
	 * @param {string} $index - The field that will be used as array key in the result. If not set, the result will be a non associative array
	 * @param {array} $fields - The fields to get out
	 * @param {array} $order - The order of the results
	 * @return {array[Model]} - the array containing found models
	 */
    public static function getListByExample(DBExample $example, $index = null, $fields = array(), $order = array()){
		return self::getDbInstance()->select(array(
            'fields' => $fields,
			'from' => static::$tablename,
			'where' => $example,
            'index' => $index,
			'return' => get_called_class(),
            'orderby' => $order
		));        
	}
	
	
	/**
	 * Get a model instance by sql condition
	 * @param {string} $where - The SQL condition
	 * @param {array} $binds - The binded values
	 * @param {array} $fields - The fields to get out
	 * @return {Model} - the model found
	 */
	public static function getBySQL($where, $binds = array(), $fields = array()){
		return self::getDbInstance()->select(array(
            'fields' => $fields,
            'from' => static::$tablename,
            'where' => $where,
			'binds' => $binds,
            'return' => get_called_class(),
            'one' => true,
        ));
	}
	
	
	/**
	 * Get a list of model instances by a SQL condition
	 * @param {string} $where - The example to find the data lines
	 * @param {array} $binds - The binded values
	 * @param {string} $index - The field that will be used as array key in the result. If not set, the result will be a non associative array
	 * @param {array} $fields - The fields to get out
	 * @param {array} $order - The order of the results
	 * @return {array[Model]} - the array containing found models
	 */
    public static function getListBySQL($where, $binds = array(), $index = null, $fields = array(), $order = array()){
		return self::getDbInstance()->select(array(
            'fields' => $fields,
			'from' => static::$tablename,
			'where' => $where,
			'binds' => $binds,
            'index' => $index,
			'return' => get_called_class(),
            'orderby' => $order
		));        
	}
	
	
	
	/**
	 * Delete data in the database from an example
	 * @param {DBExample} $example - The example to find the lines to delete
	 */
	public static function deleteByExample(DBExample $example){
		return self::getDbInstance()->delete(static::$tablename, $example);
	}
	
	/**
	 * Delete data in the database from a SQL condition
	 * @param {string} $where - The SQL condition to find the lines to delete
	 * @param {array} $binds - The binded values
	 */
	public static function deleteBySQL($where, $binds = array()){
		return self::getDbInstance()->delete(static::$tablename, $where, $binds);
	}
	
	
	/**
	 * Count the number of elements filtered by an example
	 * @param {DBExample} $example - The example to find the lines to delete
	 * @param {array} $group - The fields used to group the results
	 */
    public static function countElementsByExample(DBExample $example, $group = array()){
		return self::getDbInstance()->count(static::$tablename, $example, array(), self::$primaryColumn, $group);
	}
    
	
	
    /**
	 * Count the number of elements filtered by a SQL condition
	 * @param {string} $where - The SQL condition
	 * @param {array} $binds - the binded values
	 * @param {array} $group - The fields used to group the results
	 */
    public static function countElementsBySQL($where, $binds,  $group = array()){
		return self::getDbInstance()->count(static::$tablename, $where, $binds, self::$primaryColumn, $group);
	}



    private function prepareDatabaseData(){
        $insert = array();
		foreach($this->dbvars as $key){
            $insert[$key] = $this->$key;    
		}
        
        return $insert;
    }
    
    


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
        
        $lastid = self::getDbInstance()->insert(static::$tablename, $insert, '', $onduplicate);
        if($lastid){
            $id = static::$primaryColumn;
            $this->$id = $lastid;
        }
	}
    

    public static function add($data){
        $class = get_called_class();
        $instance = new $class($data);
        $instance->save();

        EventManager::trigger(new Event(strtolower($class).'.added', array('data' => $data)));

        return $instance;
    }


    public function addIfNotExists(){
        $id = static::$primaryColumn;
		$insert = $this->prepareDatabaseData();
        
        $lastid = self::getDbInstance()->insert(static::$tablename, $insert, 'IGNORE');
        if($lastid){
            $this->$id = $lastid;
        }
    }



    public function update(){
        $update = $this->prepareDatabaseData();
        $id = static::$primaryColumn;
        self::getDbInstance()->update(static::$tablename, new DBExample(array($id => $this->$id)), $update);
    }
	


	public function delete(){
		$class = get_called_class();
		$id = static::$primaryColumn;
		$deleted = self::getDbInstance()->delete(static::$tablename, new DBExample(array($id => $this->$id)));

        EventManager::trigger(new Event(strtolower($class).'.deleted', array('object' => $this)));

        return $deleted;
	}
	


	public function getData(){
		$data = array();
		foreach($this->dbvars as $key){
			$data[$key] = $this->$key;
		}
		return $data;
	}
    



    public function set($field, $value = null){
        if(is_array($field) && $value === null){
            foreach($field as $key => $value){
                $this->set($key, $value);
            }
        }
        else{
            $this->$field = $value;
            $this->dbvars[] = $field;
        }
    }
    


    public static function getTable(){
        return static::$tablename;
    }
    


    public static function getPrimaryColumn(){
        return static::$primaryColumn;
    }


    public static function getDbName(){
        return static::$dbname;
    }


    public static function getDbInstance(){
        return DB::get(static::$dbname);
    }

    public function getPrimaryValues(){
        $cols = array();
        $result = array();
        if(is_array(static::getPrimaryColumn())){
            $cols = static::$primaryColumn;
        }
        else{
            $cols = array(static::$primaryColumn);
        }
        
        foreach($cols as $col){
            $result[$col] = $this->$col;
        }
        
        return $result;
    }

    public static function setTable($table){
        static::$tablename = $table;
    }
    
    public static function setPrimaryColumn($primaryColumn){
        static::$primaryColumn = $primaryColumn;
    }

    public static function setDbName($name){
        static::$dbname = $name;
    }
}