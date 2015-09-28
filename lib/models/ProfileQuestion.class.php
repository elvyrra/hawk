<?php
namespace Hawk;

class ProfileQuestion extends Model{
	protected static $tablename = "ProfileQuestion";
	protected static $primaryColumn = 'name';

	public static $allowedTypes = array('text', 'textarea', 'checkbox', 'radio', 'select', 'datetime', 'file');

	public static function getByName($name){
		return self::getById($name);
	}

	public static function getRegisterQuestions(){
		$example = array(
			'displayInRegister' => 1
		);
		return self::getListByExample(new DBExample($example), self::$primaryColumn, array(), array('order' => DB::SORT_ASC));
	}
	
	public static function getDisplayProfileQuestions(){
		$example = array(
			'displayInProfile' => 1
		);
		return self::getListByExample(new DBExample($example), self::$primaryColumn, array(), array('order' => DB::SORT_ASC));
	}


}