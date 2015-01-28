<?php

class MenuModel extends Model{
	public static $tablename = "Menu";
		
	public static function getVisibleMenus($user = null){
		if($user == null)
			$user = Session::getUser();
		
		$sql = 'SELECT M.*
				FROM ' . self::$tablename . ' M RIGHT JOIN MenuVisibility V ON V.menuId = M.id
				WHERE V.roleId = :roleId AND V.visible = 1
				ORDER BY `order` ASC';
		
		return DB::get(self::DBNAME)->query($sql, array('roleId' => $user->roleId), __CLASS__);		
	}
	
	public function getVisibleItems($user = null){
		if($user == null)
			$user = Session::getUser();
			
		if(!isset($this->visibleItems)){
			
			$sql = 'SELECT M.*
					FROM ' . MenuItemModel::$tablename . ' M RIGHT JOIN MenuItemVisibility V ON V.menuId = M.id
					WHERE 	M.menuId = :id AND
							V.roleId = :roleId AND
							V.visible = 1
					ORDER BY `order` ASC';
			$this->visibleItems = DB::get(self::DBNAME)->query($sql, array('id' => $this->id, 'roleId' => $user->roleId), 'MenuItemModel');
		}
		return $this->visibleItems;
	}
	
	public function getItems(){
		if(! isset($this->items)){
			$this->items = MenuItemModel::getByArray(array(
				'menuId' => $this->id,
			), 'id');
		}
		return $this->items;
	}
}
