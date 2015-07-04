<?php
/**
 * DB.class.php
 * @author Elvyrra SAS
 */

/**
 * This class is a manager for MySQL connection and queries
 */
class DB{
	/**
	 * List of servers to connect
	 */
	private static $servers = array();

	/**
	 * List of connections instances
	 */
	public static $instances = array();
	
	/**
	 * PDO Object representing the connection to the server    
	 */
	private $connection;

	/**
	 * The hosname of the connection
	 */
	private $host, 

	/**
	 * the user connected to the database
	 */
	$username, 

	/**
	 * The password of the user
	 */
	$password, 

	/**
	 * The selected database
	 */
	$dbname,


	/**
	 * The charset
	 */
	$charset = 'utf8',

	/**
	 * The logged queries
	 */
	$log = array();


	// Status constants
	const RETURN_STATUS = 0; // Returns the status of the query execution
	const RETURN_ARRAY = 1;	// Return each line of the query result in an array
	const RETURN_OBJECT = 3; // Return each line of the query result in an object
	const RETURN_LAST_INSERT_ID = 5; // Return The last inserted id
	const RETURN_AFFECTED_ROWS = 6; // Return the rows affected by the query
	const RETURN_QUERY = 7; // Return the query it self
	const RETURN_CURSOR = 8; // Return a cursor
	
	// Sort constants
	const SORT_ASC = 'ASC'; // Sort ascending 
	const SORT_DESC = 'DESC'; // Sort descending


	
	/**
	 * @constructs
	 * @param {array} $data - the connection properties
	 */
	public function __construct($data) {        
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
	 * @param array $params The connection parameters. Each element of this array is an array itself, containing the connections data : 'host', 'dbname', 'username', 'password'. 
	 	When openning the connection, if the first connection fails, a connection will be tried on the second element, and then. This is usefull for master / slave connections
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
		if(isset(self::$instances[$name])){
			return self::$instances[$name];
		}
		
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
		
		foreach($default as $name => $value){
			if(!isset($options[$name])){
				$options[$name] = $value;
			}
		}
		
        try {
			// Prepare the query
			$stmt = $this->connection->prepare($sql);			
			
			// Prepare query logging
			$log = $sql;

			// Bind values to the query
			foreach($binds as $key => $bind){
				$stmt->bindValue(":$key", $bind);
				$log = str_replace(":$key", $this->connection->quote($bind), $log);
			}
			
			// Execute the query
			$start = microtime(true);
			$stmt->execute();
			$end = microtime(true);

			// Get the result
			$result;
			if(is_int($options['return'])){
				switch($options['return']){
					case self::RETURN_STATUS:
						$result =  true;
					break;
				
					case self::RETURN_ARRAY:
						if($options['onerow']){
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
						}
						else{
							$data = array();
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
								if($options['index']){
									$data[$row[$options['index']]] = $row;
								}
								else{
									$data[]= $row;
								}
							}
							$result = $data;
						}
					break;
				
					case self::RETURN_OBJECT :
						if($options['onerow']){
							$result = $stmt->fetch(PDO::FETCH_OBJ);
						}
						else{
							$data = array();
							while($row = $stmt->fetch(PDO::FETCH_OBJ)){
								if($options['index']){
									$index = $options['index'];
									$data[$row->$index]= $row;
								}
								else{
									$data[] = $row;
								}
							}
							$result = $data;
						}
					break;
				
					case self::RETURN_LAST_INSERT_ID :
						$result = $this->connection->lastInsertId();
					break;
				
					case self::RETURN_AFFECTED_ROWS :
						$result = $stmt->rowCount();
					break;
				
					case self::RETURN_CURSOR :
						$result = $stmt;
					break;				
				}
			}
			else{
				if($options['onerow']){
					$object = $stmt->fetchObject($options['return'], $options['args']);
					$result = $object !== false ? $object : null;
				}
				else{
					$data = array();
					while($row = $stmt->fetchObject($options['return'], $options['args'])){
						if($options['index']){
							$index = $options['index'];
							$data[$row->$index] = $row;
						}
						else{
							$data[] = $row;
						}
					}
					$result = $data;
				}
			}

			$this->addLog($log, $result, $start, $end);
			return $result;
        }
        catch(PDOException $e) {
        	$this->addLog($log, 'query failed', $start, microtime(true));
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

		$having = !empty($query->having) ? "HAVING $query->having" : '';

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
			$limit = " LIMIT ".(!empty($query->start) ? "$query->start, " : ""). "$query->limit";       
		}
		
        $sql = "SELECT $query->fields FROM $query->from $where $group $having $orderby $limit";
		
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
		return preg_replace_callback('/^(\w+)(\.(\w+))?$/', function($m){ return "`{$m[1]}`".(isset($m[2]) && isset($m[3]) ? ".`{$m[3]}`" : "");}, $str);
	}
	
	public static function escape($str){		
		return reset(self::$instances)->connection->quote($str);
	}


	/**
	 * Add a query in log
	 */
	public function addLog($log, $result, $start, $end){
		$this->logs[] = array(
			'query' => $log, 
			'result' => $result,
			'start' => $start - SCRIPT_START_TIME,
			'end' => $end - SCRIPT_START_TIME,
			'time' => $end - $start
		);
	}


	/**
	 * Get the DB logs
	 */
	
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