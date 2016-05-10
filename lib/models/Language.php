<?php
/**
 * Language.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk;


/**
 * This class the language model
 *
 * @package BaseModels
 */
class Language extends Model{
    /**
     * The associated table
     */
    protected static $tablename = "Language";

    /**
     * The primary key in the table
     */
    protected static $primaryColumn = 'tag';


    /**
     * The class instances
     */
    private static $instances = array();

    /**
     * The model fields
     */
    protected static $fields = array(
        'tag' => array(
            'type' => 'VARCHAR(2)',
        ),

        'label' => array(
            'type' => 'VARCHAR(64)'
        ),

        'isDefault' => array(
            'type' => 'TINYINT(1)'
        ),

        'active' => array(
            'type' => 'TINYINT(1)',
            'default' => '1'
        )
    );


    /**
     * Find a language by it tag
     *
     * @param string $tag The language tag to find
     *
     * @return Language the language instance
     */
    public static function getByTag($tag){
        if(!isset(self::$instances[$tag])) {
            self::$instances[$tag] = self::getById($tag);
        }

        return self::$instances[$tag];
    }

    /**
     * Get all active languages
     *
     * @return array The list of language instances
     */
    public static function getAllActive(){
        return self::getListByExample(
            new DBExample(
                array(
                'active' => 1
                )
            )
        );
    }

    /**
     * Get the current language
     *
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
     *
     * @param array $translations The translations to save
     */
    public function saveTranslations($translations){
        foreach($translations as $plugin => $trs){
            $currentData = Lang::getUserTranslations($plugin, $this->tag);

            if(empty($currentData)) {
                $data = $trs;
            }
            else{
                $data = array_merge($currentData, $trs);
            }
            Lang::saveUserTranslations($plugin, $this->tag, $data);
        }
    }

    /**
     * Remove translations for the language
     *
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