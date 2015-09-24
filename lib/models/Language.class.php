<?php
namespace Hawk;

class Language extends Model{
	protected static $tablename = "Language";
	protected static $primaryColumn = 'tag';
	
	private static $instances = array();
	

	
	/**
	 * Find a language by it tag
	 * @param string $tag The language tag to find
	 * @return Language the language instance
	 */
	public static function getByTag($tag){
		if(!isset(self::$instances[$tag])){
			self::$instances[$tag] = self::getById($tag);
		}

		return self::$instances[$tag];
	}

	/**
	 * Get all active languages 
	 * @return array The list of language instances
	 */
	public static function getAllActive(){
		return self::getListByExample(new DBExample(array(
			'active' => 1
		)));
	}

	/**
	 * Get the current language 
	 * @return Language The current language instance	
	 */
	public static function current(){
		return self::getByTag(LANGUAGE);
	}

	/**
	 * Set the language as the default one for the application	 
	 */
	public function setDefault(){		
		$this->dbo->query('UPDATE '. self::getTable() . ' SET isDefault = CASE WHEN `tag` = :tag THEN 1 ELSE 0 END', array('tag' => $this->tag));
	}
	

	/**
	 * Save a set of translations in the language
	 * @param array $translations The translations to save
	 */
	public function saveTranslations($translations){
		foreach($translations as $plugin => $translations){
			$currentData = Lang::getUserTranslations($plugin, $this->tag);
			$currentData = array_merge($currentData, $translations);
			Lang::saveUserTranslations($plugin, $this->tag, $currentData);
		}
	}

	/**
	 * Remove translations for the language
	 * @param array $translations The keys to remove
	 */
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