<?php
/**
 * Session.class.php
 * @author Elvyrra SAS
 */
/**
 * This trait is used to manage the user's sessions. It's declared as trait 
 * to be used in custom classes developer could want to develop to extend
 * the session management (manage licences, number of simultaneous connections, ...)
 */
trait Session{
	static $connected, $admin, $root, $user;
	
	public static function getUser(){
		if(isset(self::$user)){
			return self::$user;
		}
		if(empty($_SESSION['user']['id'])){
			$_SESSION['user']['id'] = 0;
		}
		self::$user = User::getById($_SESSION['user']['id']);
		return self::$user;
	}
	
	/**
	 * Returns if the current user is connected or not
	 * @return bool true if the user is connected, else returns false
	 */
	public static function isConnected(){
		if(isset(self::$connected)){
			return self::$connected;
		}
		
		/*** Test the session ***/		
		if(empty($_SESSION['user'])){	
			self::$connected = false;
		}
		elseif(isset($_SESSION['user']['ip']) && $_SESSION['user']['ip'] != Request::clientIp()){
			return false;
		}
		else{		
			/*** The session is not empty . Check the coherency between the user id and
				company Id, and with the rights ***/				
			self::$connected = self::getUser() && self::getUser()->isConnected();
		}
		return self::$connected;
	}

	public static function isAllowed($action){
		return self::getUser()->isAllowed($action);
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/