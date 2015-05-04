
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
			$order = DB::get(self::DBNAME)->select(array(
				'fields' => array('MAX(`order`) + 1' => 'newOrder'),
				'from' => self::$tablename,				
				'one' => true,
			))->newOrder;
		}
		else{
			// First update the menus which order is greater than the one you want to include
			$sql = 'UPDATE ' . self::$tablename . ' SET order=order + 1  WHERE order >= :order';
			DB::get(self::DBNAME)->query($sql, array('order' => $order));
		}

		// Insert the menu
		$menu = parent::add(array(
			'name' => $name, 
			'labelKey' => $labelKey,
			'order' => $order
		));	

		return $menu;
	}

	public function addItem($data){
		$data['menuId'] = $this->id;

		MenuItem::add($data);
	}
}
