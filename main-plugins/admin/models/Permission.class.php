<?php

class Permission extends Model{
	protected static $tablename = "Permission";
	protected static $primeryColumn = "id";
	
	const ALL_PRIVILEGES_ID = 1;

	public static function getAllGroupByPlugin(){
		$permissions = self::getAll();
		$groups = array();
		foreach($permissions as $permission){
			if(!isset($groups[$permission->plugin])){
				$groups[$permission->plugin] = array();
			}

			$groups[$permission->plugin][] = $permission;
		}

		return $groups;
	}
}