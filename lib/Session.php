<?php
/**
 * Session.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This trait is used to manage the user's sessions. It's declared as trait 
 * to be used in custom classes developer could want to develop to extend
 * the session management (manage licences, number of simultaneous connections, ...)
 * @package Core
 */
class Session{
	/**
	 * Static variable that registers the connected state of the current user
	 * @static
	 * @var boolean
	 */
	static $connected, 

	/**
	 * Static variable that registers the current user of the session
	 * @static
	 * @var User
	 */
	$user;
	
	/**
	 * Get the user of the current session
	 * @return User the user of the current session
	 */
	public static function getUser(){
		if(isset(self::$user)){
			return self::$user;
		}
		if(empty($_SESSION['user']['id'])){
			$_SESSION['user']['id'] = 0;
		}

		if(Conf::has('db')){
			// Get the user from the database
			self::$user = User::getById($_SESSION['user']['id']);
		}
		else{
			// The database does not exists yet. Create a 'fake' guest user
			self::$user = new User(array(
				'id' => User::GUEST_USER_ID,
				'username' => 'guest',
				'active' => 0,
				'ip' => Request::clientIp()
			));
		}
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
			self::$connected = false;
		}
		else{		
			/*** The session is not empty . Check the coherency between the user id and
				company Id, and with the rights ***/
			self::$connected = self::getUser() && self::getUser()->isConnected();
		}
		return self::$connected;
	}


	/**
	 * Check if the user is allowed to make action
	 * @param string $action The name of the permission to check
	 * @return boolean True if the user is allowed to perform this action, False in other case
	 */
	public static function isAllowed($action){
		return self::getUser()->isAllowed($action);
	}
}

