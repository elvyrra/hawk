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
	private static $servers = array();
	private static $instances = array();
	
	
	const RETURN_STATUS = 0;
	const RETURN_ARRAY = 1;	
	const RETURN_OBJECT = 3;
	const RETURN_LAST_INSERT_ID = 5;
	const RETURN_AFFECTED_ROWS = 6;
	const RETURN_QUERY = 7;
	const RETURN_CURSOR = 8;
	
	const SORT_ASC = 1;
	const SORT_DESC = -1;
	
	/*
	 * Description : Add a configuration for a database connection
	 */
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
                self::$instances[$name] = new MySQLClient($server);
                
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