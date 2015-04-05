<?php

class LanguageKey extends Model{
	protected static $tablename = "LanguageKey";	
	protected static $primaryColumn = 'id';
	
	public function getTranslations(){
		return LanguageTranslation::getListByExample(new DBExample(array('keyId' => $this->id)), 'languageTag');
	}

	public static function getByKey($key){
		list($plugin, $key) = explode('.', $key);
		return self::getByExample(new DBExample(array(
			'plugin' => $plugin,
			'key' => $key
		)));
	}

	public static function create($key, $transations = array()){
		$languageKey = new self();				
		list($plugin, $key) = explode('.', $key);	
		$languageKey->set(array(
			'plugin' => $plugin,
			'key' => $key
		));
		$languageKey->save();

		foreach($transations as $tag => $translation){
			if(!empty($translation)){
				$tr = new LanguageTranslation();
				$tr->set('keyId', $languageKey->id);
				$tr->set('languageTag', $tag);
				$tr->set('translation', $translation);
				$tr->save();

				Language::getByTag($tag)->generateCacheFiles();
			}
		}
	}
}