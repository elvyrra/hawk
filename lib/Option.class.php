<?php

class Option{
	private static $options = array();
	
	public static function get($name){
		list($plugin, $key) = explode('.', $name);
		if(! isset(self::$options[$plugin][$key])){
			$option = DB::get(MAINDB)->select(array(
				'from' => 'Option',
				'where' => new DBExample(array('plugin' => $plugin, 'key' => $key)),				
				'one' => true
			));
			self::$options[$plugin][$key] = $option['value'];
		}
		
		return self::$options[$plugin][$key];
	}

	public static function getPluginOptions($plugin){
		$options = DB::get(MAINDB)->select(array(
			'from' => 'Option',
			'where' => new DBExample(array('plugin' => $plugin))			
		));
		foreach($options as $option){
			self::$options[$plugin][$option['key']] = $option['value'];
		}
		return isset(self::$options[$plugin]) ? self::$options[$plugin] : false;
	}
	
	public function set($name, $value){
		list($plugin, $key) = explode('.', $name);
		self::$options[$plugin][$key] = $value;
		
		DB::get(MAINDB)->replace('Option', array(
			'plugin' => $plugin,
			'key' => $key,
			'value' => $value
		));
	}
}