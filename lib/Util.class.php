<?php
/**********************************************************************
 *    						Util.class.php
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
class Util{
    private static $LOG_DIR;
    const LOG_MAXSIZE = 500000;
    const DEFAULT_LANGUAGE = 'fr';
    
    /*____________________________________________________________________
                  
                  Log information in logs files 
    ____________________________________________________________________*/
    public static function log($message, $level = 'info', $args = array()){
		unset($args['']);
		DB::get('os')->insert('Logs', array(
			'type' => $level,
			'message' => self::vprintf(Lang::get($message, $level, 'log'), $args),
			'ip' => self::ip(),			
		)); 		
    }   
	
	/*____________________________________________________________________
                  
                        Fill variable in messages
    ____________________________________________________________________*/
	public static function vprintf($string, $args= array()){        
		foreach($args as $key => $value){	
			if(is_scalar($value))
				$string = preg_replace("/\%".preg_quote($key, '/') . '\b/', $value, $string);
        }
        return $string;
    }  
    
      
    /*____________________________________________________________________
                  
            Redirect the user to the connection page if not connected
    ____________________________________________________________________*/
    public static function getout(){
        self::log('not-connected', 'security', array('ip' => self::ip()));
	    echo "<script type='text/javascript'> location='".WEBROOT."/'; </script>";		
    }
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/