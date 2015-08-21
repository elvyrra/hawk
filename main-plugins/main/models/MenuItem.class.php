<?php


class MenuItem extends Model{
	public static $tablename = "MenuItem";
	protected static $primaryColumn = "id";

	public function __construct($data = array()){
		parent::__construct($data);

		if(!empty($this->labelKey)){
			$this->label = Lang::get($this->labelKey);
		}

		if(!empty($this->action)){
			$params = !empty($this->actionParameters) ? json_decode($this->actionParameters, true) : array();
			$this->url = Router::getUri($this->action, $params);

			if($this->url == Router::INVALID_URL){
				$this->url = $this->action;
			}
		}
	}
	
	public static function getByName($name, $menuName){
		return DB::get(self::$dbname)->select(array(
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
		
		return DB::get(self::$dbname)->query($sql, array('userId' => $user->id), array('return' => __CLASS__));	
	}

	public static function add($data){
		if(!isset($data['menuId'])){
			throw new Exception("To add a new menu item, you must give the menuId in the item parameters");
		}

		if(!isset($data['order']) || $data['order'] === -1){
			$data['order'] = DB::get(self::$dbname)->select(array(
				'fields' => array('COALESCE(MAX(`order`), 0) + 1' => 'newOrder'),
				'from' => self::$tablename,
				'where' => new DBExample(array('menuId' => $data['menuId'])),				
				'one' => true,
				'return' => DB::RETURN_OBJECT
			))->newOrder;
		}
		else{
			// First update the items which order is greater than the one you want to include
			$sql = 'UPDATE ' . self::$tablename . ' SET  `order` = `order` + 1  WHERE menuId = :menuId AND `order` >= :order';
			DB::get(self::$dbname)->query($sql, array('order' => $data['order'], 'menuId' => $data['menuId']));
		}

		// Insert the menu item
		$item = parent::add($data);	

		EventManager::trigger(new Event('menu.added', array('item' => $item)));

		return $item;
	}
}