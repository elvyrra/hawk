<?php
/**********************************************************************
 *    						HTTP.class.php
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
 
class HTTP{
	
	public static function request($url, $method, $data){
		$data = http_build_query($data);
		$opts = array('http' =>
			array(
				'method'  => strtoupper($method),
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $data
			)
		);

		$context  = stream_context_create($opts);
		$result = @file_get_contents($url, false, $context);
		return $result;	
	}
	
	public static function post($url, $data){
		return self::request($url, 'POST', $data);
	}
	
	public static function get($url, $data){
		return self::request($url, 'GET', $data);
	}
 
}