<?php
/**********************************************************************
 *    						MySQLClient.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
class MySQLClient{
	
	public $connection; // PDO Object representing the connection to the server    
	private $host, $username, $password, $dbname;
	public $sql, $binds;
	
	/*_____________________________________________________________________________
	
		Constructor : Open a connection with the server and select the db
	_____________________________________________________________________________*/
    public function __construct($data) {        
        $this->charset = 'utf8';
		$this->sql = "";
		$this->binds = array();
		
		foreach($data as $key => $value){
			$this->$key = $value;
		}
		try	{
			$dns = "mysql:host=$this->host";
			if(!empty($this->dbname)){
				$dns .= ";dbname=$this->dbname";
			}
			if(!empty($this->port)){
				$dns .= ";port=$this->port";
			}
			
			$this->connection = new PDO($dns, $this->username, $this->password);			
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);			
			$this->connection->query('SET NAMES "'.$this->charset.'"');
        }
        catch(PDOException $e) {
            throw new DBException($e->getMessage(), DBException::CONNECTION_ERROR, $host);
        }
    }
	
	/*_____________________________________________________________________________
	
		Parse an array condition into the right language
		@param :
			o string $condition : The condition, written in Javascript
		@return :
			o mixed $result : The condition in the right context
	_____________________________________________________________________________*/
	public function parse($condition, &$binds, $op = 'AND', $upperkey = ''){
		if(is_string($condition)){
			return $condition;
		}
		elseif(is_array($condition)){
			$sql = "";
			$elements = array();
			foreach($condition as $key => $value){
				$bindKey = uniqid();
				switch($key){
					case '$not':
						// NOT (...)
						$elements[] = 'NOT ('.$this->parse($value, $binds) .')';
						break;
					
					case '$lte':
						// key <= value
						$binds[$bindKey] = $value;						
						$elements[] = "$upperkey <= :$bindKey";	
						break;
					
					case '$lt':
						// key < value
						$binds[$bindKey] = $value;						
						$elements[] = "$upperkey < :$bindKey";	
						break;
					
					case '$gte':
						// key >= value
						$binds[$bindKey] = $value;						
						$elements[] = "$upperkey >= :$bindKey";	
						break;
					
					case '$gt':
						// key > value
						$binds[$bindKey] = $value;						
						$elements[] = "$upperkey > :$bindKey";						
						break;
					
					case '$ne' :
						// key <> value
						$binds[$bindKey] = $value;						
						$elements[] = "$upperkey <> :$bindKey";		
						break;
					
					case '$or' :
						// ... OR ...
						$elements[] = '(' . $this->parse($value, $binds, 'OR') . ')';
						break;

					case '$and' :
						// ... AND ...
						$elements[] = '(' . $this->parse($value, $binds, 'AND') . ')';
						break;

					case '$in' :
					case '$nin' :
						// key [NOT] IN (...)
						$keys = array();
						foreach($value as $val){
							$bindKey = uniqid();
							$binds[$bindKey] = $val;							
							$keys[] = $bindKey;
						}
						$op = $key == '$nin' ? 'NOT IN' : 'IN';
						$elements[] = "$upperkey $op (" . implode(',', $keys) . ")";
						break;
											
					default :
						if(is_scalar($value)){
							$binds[$bindKey] = $value;
							$elements[] = "$key = :$bindKey";
						}
						elseif(is_array($value)){
							$elements[] = $this->parse($value, $binds, 'AND', $key);	
						}
						break;
				}				
			}
			if($upperkey)
				return '(' . implode(" $op ", $elements) . ')';
			else	
				return implode(" $op ", $elements);
		}
	}
	
	/*
	 *Add bind value
	 *@param string $key, the key to bind
	 *@param string $value, the corresponding value
	 *@return string $key, the final name of the key (in case of multiple use of the same column)
	 */
	private function bind($key, $value){
		/*** Detect the type of the value to insert ***/
		switch(true){
			case is_bool($value) :
				$type = PDO::PARAM_BOOL;
			break;
		
			case is_int($value) || (is_string($value) && ctype_digit($value)) :
				$type = PDO::PARAM_INT;
			break;
		
			case $value === null:
				$type = PDO::PARAM_NULL;
			break;
		
			default :
				$type = PDO::PARAM_STR;
			break;
		}
		
		$i = 0;
		while(isset($this->binds[$key])){
			$key = str_replace($i, '', $key). ++$i;
		}
		$this->binds[$key] = array('value' => $value, 'type' => $type);
		return $key;
	}

	/*_____________________________________________________________________________
	
		Send a query to the database and get the result of the query
	_____________________________________________________________________________*/
	public function query($sql, $binds = array(), $return = DB::RETURN_STATUS, $onerow = false, $index = ''){	
        try {
			// reinitialize binds
			$this->binds = array();
			foreach($binds as $key => $value)
				$this->bind($key, $value);
			
			$stmt = $this->connection->prepare($sql);			
			foreach($this->binds as $key => $bind)
				$stmt->bindValue(":$key", $bind['value'], $bind['type']);
			
			$stmt->execute();
			if(is_int($return)){
				switch($return){
					case DB::RETURN_STATUS:
						return true;
					break;
				
					case DB::RETURN_ARRAY:
						if($onerow)
							return $stmt->fetch(PDO::FETCH_ASSOC);
						else{
							$data = array();
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
								if($index)
									$data[$row[$index]] = $row;
								else
									$data[]= $row;
							}
							return $data;
						}
					break;
				
					case DB::RETURN_OBJECT :
						if($onerow)
							return $stmt->fetch(PDO::FETCH_OBJ);
						else{
							$data = array();
							while($row = $stmt->fetch(PDO::FETCH_OBJ)){
								if($index)
									$data[$row->$index]= $row;
								else
									$data[] = $row;
							}
							return $data;
						}
					break;
				
					case DB::RETURN_LAST_INSERT_ID :
						return $this->connection->lastInsertId();
					break;
				
					case DB::RETURN_AFFECTED_ROWS :
						return $stmt->rowCount();
					break;
				
					case DB::RETURN_CURSOR :
						return $stmt;
					break;				
				}
			}
			else{
				if($onerow)
					return $stmt->fetchObject($return);
				else{
					$data = array();
					while($row = $stmt->fetchObject($return)){
						if($index)
							$data[$row->$index] = $row;
						else
							$data[] = $row;
					}
					return $data;
				}
			}
        }
        catch(PDOException $e) {
            throw new DBException($e->getMessage(), DBException::QUERY_ERROR, $sql);
        }  	
	}
	
	
	/*_____________________________________________________________________________
	
		Select a database
	_____________________________________________________________________________*/
    public function select_db($dbname){
        $this->query('use :dbname', array('dbname' => $dbname));
    }
	
	/*_____________________________________________________________________________
	
		Search for records corresponding to the query 
	_____________________________________________________________________________*/
    public function select($query=array()) {
		$query = (object) $query;
		
		if(!isset($query->table))
			throw new DBException("table parameter is mandatory in select method", DBException::QUERY_ERROR, '' );
					
		/*** Treat the paramters ***/
		if(empty($query->fields)){                
			$query->fields = "*";
		}
		elseif(is_array($query->fields)){				
			$tmp = array();
			foreach($query->fields as $name => $alias){
				if(is_numeric($name))
					$tmp[] = $alias;
				else
					$tmp[] = "$name as $alias";
			}
			$query->fields = implode(",", $tmp);
		}
		
		
		if(!empty($query->condition))			
			$query->condition = "WHERE $query->condition";

		if(!empty($query->group))
			$query->group = "GROUP BY ".implode(",", $query->group);

		if(!empty($query->sort)){
			$sorts = array();			
			foreach($query->sort as $field => $value){
				$sorts[] = "$field ".($value > 0 ? "ASC" : "DESC");				
			}
			$query->sort = "ORDER BY ".implode(",", $sorts);
		}
		else
		    $query->sort = "";
		
		if($query->one)
			$query->limit = "LIMIT 1";
		elseif(!empty($query->limit))
			$query->limit = " LIMIT ".($query->start ? "$query->start, " : ""). "$query->limit";       
        
        $sql = "SELECT $query->fields FROM $query->table $query->condition $query->group $query->sort $query->limit";
		if(empty($query->binds))
		   $query->binds = array();
		   
		if(!$query->return){
			$query->return = DB::RETURN_ARRAY;
		}
		
		return $this->query($sql, $query->binds, $query->return, $query->one, $query->index);		
    }
	
	
	/*_____________________________________________________________________________
	
		Insert data in the database
	_____________________________________________________________________________*/
    public function insert($table, $insert = array(), $flag = '', $onduplicatekey = ''){
		$keys = array();
		$values = array();
		$binds = array();
		foreach($insert as $key => $value){
			$uniq = uniqid();
			$values[] = ':'.$uniq;
			$keys[] = $key;
			$binds[$uniq] = $value;	
		}
		$keys = implode(',',$keys);
		$values = implode(',',$values);
				
		$sql="INSERT $flag INTO ".$table." (".$keys.") VALUES (".$values.") " . ($onduplicatekey ? "ON DUPLICATE KEY UPDATE $onduplicatekey" : "");
		
		return $this->query($sql, $binds, DB::RETURN_LAST_INSERT_ID);
	}
	
	/*_____________________________________________________________________________
	
		replace data in the database
	_____________________________________________________________________________*/
    public function replace($table, $insert = array()){
		$keys = array();
		$values = array();
		$binds = array();
		foreach($insert as $key => $value){
			$uniq = uniqid(':');
			$values[] = $uniq;
			$keys[] = $key;
			$binds[$uniq] = $value;	
		}
		$keys = implode(',',$keys);
		$values = implode(',',$values);
						
		$sql="REPLACE INTO ".$table." (".$keys.") VALUES (".$values.")";
		
		return $this->query($sql, $binds, DB::RETURN_LAST_INSERT_ID);
	}
	
	/*_____________________________________________________________________________
	
		Update records in the database
	_____________________________________________________________________________*/
	public function update($table, $condition='', $update=array(), $binds = array()) {
		if(!empty($condition))
			$condition = " WHERE $condition";
       
        $updates = array();		
        foreach($update as $key => $value ){				
            $bind = uniqid();
			$updates[] = "$key = :$bind";
			$binds[$bind] = $value;
        }
        
		$sql = "UPDATE $table SET ". implode(',',$updates) . $condition;
        
		return $this->query($sql, $binds, DB::RETURN_AFFECTED_ROWS);        
	}
	
	/*_____________________________________________________________________________
	
		delete records
	_____________________________________________________________________________*/
	public function delete($table, $condition="", $binds = array()) {
        if(!empty($condition))
			$condition = "WHERE $condition";
		
        $sql="DELETE FROM $table $condition";
        return $this->query($sql, $binds, DB::RETURN_AFFECTED_ROWS);		
    }
	
	/*_____________________________________________________________________________
	
		Get the number of records corresponding to the condition
	_____________________________________________________________________________*/
    public function count($table , $condition=null, $binds = array(), $field=null, $group = array()) {		
        if (!empty ($condition)) {
			$condition = "WHERE $condition";
        }
		if(!empty($group))
			$group = "GROUP BY ".implode(",", $group);
			
        if(empty($field)) 
			$field='*'; 
			
        $sql = "SELECT COUNT($field) as counter FROM $table $condition";
		
		return $this->query($sql, $binds, DB::RETURN_OBJECT, true)->counter;
    }	
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/