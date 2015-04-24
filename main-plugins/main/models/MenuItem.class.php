<?php

class MenuItem extends Model{
	public static $tablename = "MenuItem";
	protected static $primaryColumn = "id";
	
	public function __construct($data = array()){
		parent::__construct($data);

		$this->label = Lang::get($this->labelKey);
	}
	
	public static function getByName($name, $menuName){
		return DB::get(self::DBNAME)->select(array(
			'fields' => array('M.*'),
			'from' => self::$tablename . ' MI INNER JOIN ' . Menu::getTable() . ' M ON MI.menuId = M.id',
			'where' => new DBExample(array('M.name' => $menuName, 'MI.name' => $name)),
			'return' => __CLASS__,
			'one' => true,
		));		
	}

	public static function getAvailableItems($user = null){
		if($user == null){
			$user = Session::getUser();
		}
		
		$sql = 'SELECT I.*
				FROM ' . self::$tablename . ' I
					LEFT JOIN ' . RolePermission::getTable() . ' RP ON RP.permissionId = I.permissionId
					LEFT JOIN ' . User::getTable() . ' U ON RP.roleId = U.roleId					
				WHERE (U.id = :userId AND RP.value = 1) OR I.permissionId = 0
				ORDER BY menuId ASC, `order` ASC';
		
		return DB::get(self::DBNAME)->query($sql, array('userId' => $user->id), array('return' => __CLASS__));	
	}

	public static function add($data){
		if(!isset($data['order']) || $data['order'] === -1){
			$data['order'] = DB::get(self::DBNAME)->select(array(
				'from' => self::$tablename,
				'where' => new DBExample(array('menuId' => $data['menuId'])),
				'orderby' => array('order' => DB::SORT_DESC),
				'one' => true,
			))->order + 1;
		}
		else{
			// First update the items which order is greater than the one you want to include
			$sql = 'UPDATE ' . self::$tablename . ' SET  `order` = `order` + 1  WHERE menuId = :menuId AND `order` >= :order';
			DB::get(self::DBNAME)->query($sql, array('order' => $order, 'menuId' => $data['menuId']));
		}

		// Insert the menu
		$menu = parent::add($data);	

		return $menu;
	}
}