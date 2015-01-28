<?php

abstract class Model{
	// The name of the table containing the data of the model
	protected static $tablename;
	protected static $primaryColumn = 'id';
	
	const DBNAME = MAINDB;
	protected $dbvars = array();
	
	public function __construct(){
		foreach(get_object_vars($this) as $key => $value){
			if($key != 'dbvars'){
				$this->dbvars[] = $key;
			}
		}
	}
    
    public static function getAll(){
        return DB::get(static::DBNAME)->select(array(
			'table' => static::$tablename,
            'return' => get_called_class(),
        ));
    }
    
    public static function getById($id){
		return self::getByArray(array(static::$primaryColumn => $id));
	}
	
	public static function getByCondition($condition, $binds){
		return DB::get(static::DBNAME)->select(array(
			'table' => static::$tablename,
			'condition' => $condition,
			'binds' => $binds,
			'one' => true,
			'return' => get_called_class()
		));	
	}
	
	public static function getListByCondition($condition, $binds, $index = ''){		
		return DB::get(static::DBNAME)->select(array(
			'table' => static::$tablename,
			'condition' => $condition,
			'binds' => $binds,
			'return' => get_called_class(),
            'index' => $index,
		));
	}
	
	public static function getByArray($array){
		$condition = DB::get(static::DBNAME)->parse($array, $binds);
		return self::getByCondition($condition, $binds);
	}
	
	public static function getListByArray($array, $index = ''){
		$condition = DB::get(static::DBNAME)->parse($array, $binds);
		return self::getListByArray($condition, $binds, $index);		
	}
	
	public function save(){
		$id = static::$primaryColumn;
		$insert = array();
		foreach($this->dbvars as $key){
			$insert[$key] = $this->$key;
		}
		if(! isset($this->$id)){
			$this->$id = DB::get(static::DBNAME)->insert(static::$tablename, $insert);
		}
		else{
			DB::get(static::DBNAME)->update(static::$tablename, "$id = :id", $insert, array('id' => $this->$id));
		}
	}
	
	public function delete(){
		$id = static::$primaryColumn;
		return DB::get(static::DBNAME)->delete(static::$tablename, "$id = :id", array($id => $this->$id));
	}
	
	public function getData(){
		$data = array();
		foreach($this->dbvars as $key){
			$data[$key] = $this->$key;
		}
		return $data;
	}
}