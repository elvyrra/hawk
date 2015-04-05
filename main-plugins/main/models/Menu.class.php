
<?php

class Menu extends Model{
	protected static $tablename = "Menu";
	protected static $primaryColumn = "id";
		
	public static function getVisibleMenus($user = null){
		if($user == null)
			$user = Session::getUser();
		
		$sql = 'SELECT M.*
				FROM ' . self::$tablename . ' M RIGHT JOIN MenuVisibility V ON V.menuId = M.id
				WHERE V.roleId = :roleId AND V.visible = 1
				ORDER BY `order` ASC';
		
		return DB::get(self::DBNAME)->query($sql, array('roleId' => $user->roleId), array('return' => __CLASS__));
	}
	
	public function getVisibleItems($user = null){
		if($user == null)
			$user = Session::getUser();
			
		if(!isset($this->visibleItems)){
			
			$sql = 'SELECT M.*
					FROM ' . MenuItem::$tablename . ' M RIGHT JOIN MenuItemVisibility V ON V.menuId = M.id
					WHERE 	M.menuId = :id AND
							V.roleId = :roleId AND
							V.visible = 1
					ORDER BY `order` ASC';
			$this->visibleItems = DB::get(self::DBNAME)->query($sql, array('id' => $this->id, 'roleId' => $user->roleId), array('return' => 'MenuItem'));
		}
		return $this->visibleItems;
	}
	
	public function getItems(){
		if(! isset($this->items)){
			$this->items = MenuItem::getListByExample(new DBExample(array('menuId' => $this->id)), 'id');
		}
		return $this->items;
	}
}
