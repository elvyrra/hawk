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
	
	public static function getAvailableItems($user = null){
		if($user == null){
			$user = Session::getUser();
		}

		$items = self::getAll(null, array(), array('menuId' => 'ASC', 'order' => 'ASC'));
		return array_filter($items, function($item) use($user){
			return !$item->permissionId || $user->isAllowed($item->permissionId);
		});
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