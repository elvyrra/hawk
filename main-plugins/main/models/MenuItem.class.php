<?php

class MenuItem extends Model{
	public static $tablename = "MenuItem";
	protected static $primaryColumn = "id";
	
	public static function getVisibleMenuItems($user){
		$user = Session::getUser();		
		$sql = 'SELECT M.*
				FROM MenuItem M RIGHT JOIN MenuItemVisibility V ON V.menuId = M.id
				WHERE V.roleId IN ( ' . implode(',', array_keys($user->getRoles())) . ' ) AND V.visible = 1';
		
		return DB::get(self::DBNAME)->query($sql, array(), array('return' => __CLASS__,'index' => 'id'));		
	}
}