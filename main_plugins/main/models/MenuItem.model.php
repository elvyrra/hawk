<?php

class MenuItemModel extends Model{
	public static $tablename = "MenuItem";
	
	public static function getVisibleMenuItems($user){
		$user = Session::getUser();		
		$sql = 'SELECT M.*
				FROM MenuItem M RIGHT JOIN MenuItemVisibility V ON V.menuId = M.id
				WHERE V.roleId IN ( ' . implode(',', array_keys($user->getRoles())) . ' ) AND V.visible = 1';
		
		return DB::get(self::DBNAME)->query($sql, array(), __CLASS__, false, 'id');		
	}
}