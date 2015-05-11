
<?php

class Menu extends Model{
	protected static $tablename = "Menu";
	protected static $primaryColumn = "id";

	public function __construct($data = array()){
		parent::__construct($data);

		$this->label = Lang::get($this->labelKey);
	}
		
	public static function getAvailableMenus($user = null){
		if($user == null){
			$user = Session::getUser();
		}
		
		$menuItems = MenuItem::getAvailableItems();

		$menus = self::getAll(self::$primaryColumn, array(), array('order' => DB::SORT_ASC));

		foreach($menuItems as $item){
			$menus[$item->menuId]->visibleItems[] = $item;
		}
		
		$menus = array_filter($menus, function($menu){
			return count($menu->visibleItems) > 0;
		});

		return $menus;
	}

	public static function getByName($name){
		return self::getByExample(new DBExample(array('name' => $name)));
	}
	
	public static function add($name, $labelKey, $order = -1){
		if($order === -1){
			$order = DB::get(self::$dbname)->select(array(
				'fields' => array('COALESCE(MAX(`order`), 0) + 1' => 'newOrder'),
				'from' => self::$tablename,				
				'one' => true,
				'return' => DB::RETURN_OBJECT
			))->newOrder;
		}
		else{
			// First update the menus which order is greater than the one you want to include
			$sql = 'UPDATE ' . self::$tablename . ' SET order=order + 1  WHERE order >= :order';
			DB::get(self::$dbname)->query($sql, array('order' => $order));
		}

		// Insert the menu
		$menu = parent::add(array(
			'name' => $name, 
			'labelKey' => $labelKey,
			'order' => $order
		));	

		EventManager::trigger(new Event('menu.added', array('name' => $name, 'title' => Lang::get($labelKey))));

		return $menu;
	}

	public function addItem($data){
		$data['menuId'] = $this->id;

		MenuItem::add($data);
	}
}
