<?php
/**********************************************************************
 *    						DB.class.php
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
class DB{
	// Static properties
	private static $servers = array();
	private static $instances = array();
	
	// instance properties
	private $connection; // PDO Object representing the connection to the server    
	private $host, $username, $password, $dbname;
	public $sql, $binds;
	
	const RETURN_STATUS = 0;
	const RETURN_ARRAY = 1;	
	const RETURN_OBJECT = 3;
	const RETURN_LAST_INSERT_ID = 5;
	const RETURN_AFFECTED_ROWS = 6;
	const RETURN_QUERY = 7;
	const RETURN_CURSOR = 8;
	
	const SORT_ASC = 'ASC';
	const SORT_DESC = 'DESC';
	
	/**
	 * @constructs
	 * @param {array} $data - the connection properties
	 */
	public function __construct($data) {        
        $this->charset = 'utf8';
		$this->sql = "";
		$this->binds = array();
		
		foreach($data as $key => $value){
			$this->$key = $value;
		}
		if(strpos($this->host, ':') !== false){
			list($this->host, $this->port) = explode(':', $this->host, 2);
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
	
	
	/*
	 * Description : Add a configuration for a database connection
	 */
	public static function add($name, $params){
		self::$servers[$name] = $params;
	}
	
	/*
	 * Description : Get the open connection, or open it if not already open.
     * This method manages master / slaves connections
     * @param string name, the name of the instance
	 */
	public static function get( $name){
		if(isset(self::$instances[$name]))
			return self::$instances[$name];
		
		$servers = self::$servers[$name];
		foreach($servers as $i => $server){
            try{
                self::$instances[$name] = new self($server);
                
                // The connection succeed
                break;
            }
            catch(DBException $e){
                // The connection failed, try to connect to the next slave
                if(!isset($servers[$i+1])){
                    // the last slave connection failed
                    throw $e;
                }
            }            
        }
		
		return self::$instances[$name];
	}
	
	/*_____________________________________________________________________________
	
		Send a query to the database and get the result of the query
	_____________________________________________________________________________*/
	public function query($sql, $binds = array(), $options = array()){
		$default = array(
			'return' => self::RETURN_STATUS,
			'onerow' => false,
			'index' => null,
			'args' => array()
		);
		
		extract($default);
		extract($options);
		
        try {
			$stmt = $this->connection->prepare($sql);			
			foreach($binds as $key => $bind){
				$stmt->bindValue(":$key", $bind);
			}
			$stmt->execute();
			
			if(is_int($return)){
				switch($return){
					case self::RETURN_STATUS:
						return true;
					break;
				
					case self::RETURN_ARRAY:
						if($onerow){
							return $stmt->fetch(PDO::FETCH_ASSOC);
						}
						else{
							$data = array();
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
								if($index){
									$data[$row[$index]] = $row;
								}
								else{
									$data[]= $row;
								}
							}
							return $data;
						}
					break;
				
					case self::RETURN_OBJECT :
						if($onerow){
							return $stmt->fetch(PDO::FETCH_OBJ);
						}
						else{
							$data = array();
							while($row = $stmt->fetch(PDO::FETCH_OBJ)){
								if($index){
									$data[$row->$index]= $row;
								}
								else{
									$data[] = $row;
								}
							}
							return $data;
						}
					break;
				
					case self::RETURN_LAST_INSERT_ID :
						return $this->connection->lastInsertId();
					break;
				
					case self::RETURN_AFFECTED_ROWS :
						return $stmt->rowCount();
					break;
				
					case self::RETURN_CURSOR :
						return $stmt;
					break;				
				}
			}
			else{
				if($onerow){
					$object = $stmt->fetchObject($return, $args);
					return $object !== false ? $object : null;
				}
				else{
					$data = array();
					while($row = $stmt->fetchObject($return, $args)){
						if($index){
							$data[$row->$index] = $row;
						}
						else{
							$data[] = $row;
						}
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
    public function selectDb($dbname){
        return $this->query('use ' . $dbname);
    }
	
	/*_____________________________________________________________________________
	
		Search for records corresponding to the query 
	_____________________________________________________________________________*/
    public function select($query=array()) {
		$query = (object) $query;
		
		if(!isset($query->from)){
			throw new DBException("'from' parameter is mandatory in select method", DBException::QUERY_ERROR, '' );
		}
					
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
		
		if(isset($query->where) && $query->where instanceof DBExample){
			$query->where = $query->where->parse($query->binds);
		}
		$where = !empty($query->where) ? "WHERE $query->where" : '';
		
		$group = !empty($query->group) ? "GROUP BY ".implode(",", array_map(array(self, 'formatField'), $query->group)) : "";

		if(!empty($query->orderby)){
			$orders = array();			
			foreach($query->orderby as $field => $value){
				$orders[] = self::formatField($field) . " ".$value;
			}
			$orderby = "ORDER BY " . implode(",", $orders);
		}
		else{
		    $orderby = "";
		}
		
		$limit = "";
		if(!empty($query->one)){
			$limit = "LIMIT 1";
		}
		elseif(!empty($query->limit)){
			$limit = " LIMIT ".($query->start ? "$query->start, " : ""). "$query->limit";       
		}
		
        $sql = "SELECT $query->fields FROM $query->from $where $group $orderby $limit";
		
		if(empty($query->binds)){
		   $query->binds = array();
		}
		   
		if(empty($query->return)){
			$query->return = DB::RETURN_ARRAY;
		}
		
		return $this->query($sql, $query->binds, array(
			'return' => $query->return,
			'onerow' => !empty($query->one),
			'index' => empty($query->index) ? '' : $query->index
		));		
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
			$keys[] = self::formatField($key);
			$binds[$uniq] = $value;	
		}
		
		$keys = implode(',', $keys);
		$values = implode(',', $values);
				
		$sql="INSERT $flag INTO ".$table." (".$keys.") VALUES (".$values.") " . ($onduplicatekey ? "ON DUPLICATE KEY UPDATE $onduplicatekey" : "");
		
		return $this->query($sql, $binds, array('return' => DB::RETURN_LAST_INSERT_ID));
	}
	
	/*_____________________________________________________________________________
	
		replace data in the database
	_____________________________________________________________________________*/
    public function replace($table, $insert = array()){
		$keys = array();
		$values = array();
		$binds = array();
		
		foreach($insert as $key => $value){
			$uniq = uniqid();
			$values[] = ':' . $uniq;
			$keys[] = self::formatField($key);
			$binds[$uniq] = $value;	
		}
		
		$keys = implode(',',$keys);
		$values = implode(' , ',$values);
						
		$sql="REPLACE INTO ".$table." (".$keys.") VALUES (".$values.")";
		return $this->query($sql, $binds, array('return' => DB::RETURN_LAST_INSERT_ID));
	}
	
	/*_____________________________________________________________________________
	
		Update records in the database
	_____________________________________________________________________________*/
	public function update($table, $condition = null, $update = array(), $binds = array()) {
		if(!empty($condition)){
			if($condition instanceof DBExample){
				$condition = $condition->parse($binds);
			}
			$condition = " WHERE $condition";
		}
       
        $updates = array();		
        foreach($update as $key => $value ){
            $bind = uniqid();
			$updates[] = self::formatField($key) . " = :$bind";
			$binds[$bind] = $value;
        }
        
		$sql = "UPDATE $table SET ". implode(',',$updates) . $condition;
        
		return $this->query($sql, $binds, array('return' => DB::RETURN_AFFECTED_ROWS));
	}
	
	/*_____________________________________________________________________________
	
		delete records
	_____________________________________________________________________________*/
	public function delete($table, $condition = null, $binds = array()) {
        if(!empty($condition)){
			if($condition instanceof DBExample){
				$condition = $condition->parse($binds);
			}
			$condition = "WHERE $condition";
		}
		
        $sql="DELETE FROM $table $condition";
        
		return $this->query($sql, $binds, array('return' => DB::RETURN_AFFECTED_ROWS));
    }
	
	/*_____________________________________________________________________________
	
		Get the number of records corresponding to the condition
	_____________________________________________________________________________*/
    public function count($table , $condition = null, $binds = array(), $field = null, $group = array()) {		
        if($condition instanceof DBExample){
			$condition = $condition->parse($binds);
		}
		if (!empty ($condition)) {
			$condition = "WHERE $condition";
        }
		
		if(!empty($group)){
			$group = "GROUP BY ".implode(",", array_map(array(self, 'formatField'), $group));
		}
			
        if(empty($field)) {
			$field = '*'; 
		}
		else{
			$field = $field;
		}
			
        $sql = "SELECT COUNT($field) as counter FROM $table $condition";
		
		return $this->query($sql, $binds, array('return' => DB::RETURN_OBJECT, 'onerow' => true))->counter;
    }
	
	public static function formatField($str){
		return preg_replace_callback('/(\w+)(\.(\w+))?/', function($m){ return "`{$m[1]}`".(isset($m[2]) && isset($m[3]) ? ".`{$m[3]}`" : "");}, $str);
	}
	
	public static function escape($str){		
		return reset(self::$instances)->connection->quote($str);
	}
	
}

class DBException extends Exception{
	const CONNECTION_ERROR = 1;
	const QUERY_ERROR = 2;	

	public function __construct($message, $code, $value, $previous = null){
		switch($code){
			case self::CONNECTION_ERROR :
				$message = "Impossible to connect to Database Server : $value, $message";
			break;
			
			case self::QUERY_ERROR:
				$message = "An error was detected : $message in the Database Query : $value";
			break;
			
		}
		
		parent::__construct($message,$code, $previous);
	}
}