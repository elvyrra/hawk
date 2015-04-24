<?php

class Language extends Model{
	protected static $tablename = "Language";
	protected static $primaryColumn = 'tag';
	
	public function setDefault(){		
		$this->dbo->query('UPDATE '. self::getTable() . ' SET isDefault = CASE WHEN `tag` = :tag THEN 1 ELSE 0 END', array('tag' => $this->tag));
	}
	
	public static function getByTag($tag){
		return self::getById($tag);
	}
	
	public function generateCacheFiles(){
		$keys = $this->dbo->select(array(
			'fields' => array('K.plugin', 'K.key', 'T.translation'),
			'from' => LanguageTranslation::getTable() . ' T INNER JOIN ' . LanguageKey::getTable() . ' K ON T.keyId = K.id',
			'where' => new DBExample(array('T.languageTag' => $this->tag)),
			'orderby' => array('plugin' => DB::SORT_ASC),
			'return' => 'LanguageTranslation',
		));
		
		// Write temporary ini string
		$currentPlugin = '';
		$tmp = "";
		foreach($keys as $key){
			if($key->plugin !== $currentPlugin){
				$tmp .= "[$key->plugin]" . PHP_EOL;
				$currentPlugin = $key->plugin;
			}
			
			$tmp .= $key->key . ' = "' . addcslashes($key->translation, '"')  . '"' . PHP_EOL;			
		}
		
		
		$ini = parse_ini_string($tmp, true);
		if(!is_dir(CACHE_DIR . 'lang')){
			mkdir(CACHE_DIR . 'lang', 0755);
		}
		foreach($ini as $plugin => $translations){
			file_put_contents(CACHE_DIR . 'lang/' . $plugin . '.' . $this->tag . '.php', '<?php return ' . var_export($translations, true) . ';');
		}
	}
	
	public function countMissingTranslations(){
		return $this->dbo->count( LanguageKey::getTable() ) - $this->dbo->count(LanguageTranslation::getTable(), 'languageTag = :tag', array('tag' => $this->tag));
	}
	
	/**
	 * Import language file into the database
	 **/
	public static function importFile($filename){
		// Get the plugin name and the language
		$fileContent = file_get_contents($filename);
		
		if(preg_match('/\;\s*plugin\s*\=\s*"([\w\-]+)"/m', $fileContent, $m)){
			$plugin = $m[1];
		}
		else{
			throw new Exception("No plugin declared in the language file : $filename");
		}
		
		if(preg_match('/\;\s*language\s*\=\s*"(\w{2})"/m', $fileContent, $m)){
			$language = $m[1];
		}
		else{
			throw new Exception("No language declared in the language file : $filename");
		}
		
		// Create the language if not exists
		$lang = new self();
		$lang->set('tag', $language);
		$lang->addIfNotExists();
		
		
		$keys = parse_ini_string($fileContent);
		$insertKeys = array();
		$insertTranslations = array();
		foreach($keys as $key => $translation){
			
			if(is_array($translation)){
				foreach($translation as $k => $tr){
					self::importKey($plugin, "{$key}[{$k}]", $language, $tr);
				}
			}
			else{
				self::importKey($plugin, $key, $language, $translation);
			}
		}		
	}
	
	private static function importKey($plugin, $key, $language, $translation){
		$languageKey = LanguageKey::getByExample(new DBExample(array('plugin' => $plugin, 'key' => $key)));
			
		if(! $languageKey){
			$languageKey = new LanguageKey();
			$languageKey->set(array(
				'plugin' => $plugin,
				'key' => $key
			));
			$languageKey->save();
		}

		// Insert the translation
		$languageTranslation = new LanguageTranslation();
		$languageTranslation->set(array(
			'keyId' =>  $languageKey->id,
			'languageTag' => $language,
			'translation' => $translation
		));			
		$languageTranslation->addIfNotExists();	
	}
}