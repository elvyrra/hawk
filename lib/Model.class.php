<?php

class Model{
	// The name of the table containing the data of the model
	protected static $tablename;
	protected static $primaryColumn;
    	
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
        
        $this->dbo = DB::get(static::DBNAME);
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
    


	public static function getByExample(DBExample $example, $fields = array()){
        return DB::get(static::DBNAME)->select(array(
            'fields' => $fields,
            'from' => static::$tablename,
            'where' => $example,
            'return' => get_called_class(),
            'one' => true,
        ));        
	}
	
	

    public static function getListByExample(DBExample $example, $index = null, $fields = array(), $order = array()){
		return DB::get(static::DBNAME)->select(array(
            'fields' => $fields,
			'from' => static::$tablename,
			'where' => $example,
            'index' => $index,
			'return' => get_called_class(),
            'orderby' => $order
		));        
	}
	
	
	
    public static function countElementsByExample(DBExample $example, $group = array()){
		return DB::get(static::DBNAME)->count(static::$tablename, $example, array(), self::$primaryColumn, $group);
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
        $onduplicate = implode(', ', array_map(function($key){
			if($key == static::$primaryColumn){
				return "`$key`=LAST_INSERT_ID(`$key`)";
			}
			else{
            	return "`$key` = VALUES(`$key`)";
			}
        }, array_keys($insert)));
        
		if(!isset($insert[static::$primaryColumn])){
			$key = static::$primaryColumn;
			$onduplicate .= ", `$key`=LAST_INSERT_ID(`$key`)";
		}
        
        $lastid = $this->dbo->insert(static::$tablename, $insert, '', $onduplicate);
        if($lastid){
            $id = static::$primaryColumn;
            $this->$id = $lastid;
        }
	}
    

    public static function add($data){
        $class = get_called_class();
        $instance = new $class($data);
        $instance->save();

        return $instance;
    }


    public function addIfNotExists(){
        $id = static::$primaryColumn;
		$insert = $this->prepareDatabaseData();
        
        $lastid = $this->dbo->insert(static::$tablename, $insert, 'IGNORE');
        if($lastid){
            $this->$id = $lastid;
        }
    }



    public function update(){
        $update = $this->prepareDatabaseData();
        $id = static::$primaryColumn;
        $this->dbo->update(static::$tablename, new DBExample(array($id => $this->$id)), $update);
    }
	


	public function delete(){
		$id = static::$primaryColumn;
		return $this->dbo->delete(static::$tablename, new DBExample(array($id => $this->$id)));
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
}