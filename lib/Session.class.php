<?php
/**********************************************************************
 *    						Session.class.php
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
 
class Session{
	static $logged, $admin, $root, $user;
	
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
	
	/*_____________________________________________________________
		
		Function that returns if the current user is correctly 
		logged and has right to be logged
	_____________________________________________________________*/
	public static function logged(){
		if(isset(self::$logged))
			return self::$logged;
		
		/*** Test the session ***/		
		if(empty($_SESSION['user'])){	
			self::$logged = false;
		}
		elseif($_SESSION['user']['ip'] != Request::clientIp()){
			return false;
		}
		else{		
			/*** The session is not empty . Check the coherency between the user id and
				company Id, and with the rights ***/				
			self::$logged = self::getUser() && self::getUser()->isConnected();
		}
		return self::$logged;
	}

	public static function isAllowed($action){
		return self::getUser()->isAllowed($action);
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/