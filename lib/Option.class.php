<?php

class Option{
	private static $options = array();
	
	public static function get($name){
		list($plugin, $key) = explode('.', $name);
		if(! isset(self::$options[$plugin][$key])){
			$option = DB::get(MAINDB)->select(array(
				'table' => 'Option',
				'condition' => 'plugin = :plugin AND `key`= :key',
				'binds' => array('plugin' => $plugin, 'key' => $key),
				'one' => true
			));
			self::$options[$plugin][$key] = $option['value'];
		}
		
		return self::$options[$plugin][$key];
	}

	public static function getPluginOptions($plugin){
		$options = DB::get(MAINDB)->select(array(
			'table' => 'Option',
			'condition' => 'plugin = :plugin',
			'binds' => array('plugin' => $plugin),			
		));
		foreach($options as $options){
			self::$options[$plugin][$option['key']] = $option['value'];
		}
		return self::$options[$plugin];
	}
	
	public function set($name, $value){
		list($plugin, $key) = explode('.', $name);
		self::$options[$plugin][$key] = $value;
		
		DB::get(MAINDB)->update('Option', 'plugin = :plugin AND `key`= :key', array('value' => $value), array('plugin' => $plugin, 'key' => $key));
	}
}