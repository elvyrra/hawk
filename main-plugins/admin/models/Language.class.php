<?php

class Language extends Model{
	protected static $tablename = "Language";
	protected static $primaryColumn = 'tag';
	private static $instances = array();
	
	public function setDefault(){		
		$this->dbo->query('UPDATE '. self::getTable() . ' SET isDefault = CASE WHEN `tag` = :tag THEN 1 ELSE 0 END', array('tag' => $this->tag));
	}
	
	public static function getByTag($tag){
		if(!isset(self::$instances[$tag])){
			self::$instances[$tag] = self::getById($tag);
		}

		return self::$instances[$tag];
	}

	public static function current(){
		return self::getByTag(LANGUAGE);
	}
	
	public function saveTranslations($translations){
		foreach($translations as $plugin => $translations){
			$currentData = Lang::getUserTranslations($plugin, $this->tag);
			$currentData = array_merge($currentData, $translations);
			Lang::saveUserTranslations($plugin, $this->tag, $currentData);
		}
	}

	public function removeTranslations($translations){
		foreach ($translations as $plugin => $keys) {
			$currentData = Lang::getUserTranslations($plugin, $this->tag);
			foreach($keys as $key){
				unset($currentData[$key]);				
			}
			Lang::saveUserTranslations($plugin, $this->tag, $currentData);
		}
	}
}