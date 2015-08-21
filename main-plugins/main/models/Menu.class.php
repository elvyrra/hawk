<?php


class Menu extends Model{
	protected static $tablename = "Menu";
	protected static $primaryColumn = "id";

	/**
	 * Constructor
	 * @param array $data the menu data	 
	 */
	public function __construct($data = array()){
		parent::__construct($data);

		$this->visibleItems = array();

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
		

	public static function getAvailableMenus($user = null, $index = null){
		if($user === null){
			$user = Session::getUser();
		}
		if($index === null){
			$index = self::$primaryColumn;
		}
		
		$menuItems = MenuItem::getAvailableItems();

		$menus = self::getAll(self::$primaryColumn, array(), array('order' => DB::SORT_ASC));

		foreach($menuItems as $item){
			$menus[$item->menuId]->visibleItems[] = $item;
		}

		if($index != self::$primaryColumn){
			$id = self::$primaryColumn;
			foreach($menus as $menu){

				$menus[$menu->$index] = $menu;
				unset($menus[$menu->$id]);
			}
		}
		
		$menus = array_filter($menus, function($menu){
			return $menu->action || count($menu->visibleItems) > 0;
		});

		return $menus;
	}

	public static function getByName($plugin, $name){
		return self::getByExample(new DBExample(array('plugin' => $plugin, 'name' => $name)));
	}
	
	public static function add($data){
		if(empty($data['plugin']) || empty($data['name'])){
			throw new Exception("To add a new menu, you must specify at least the plugin and the name of the menu");
		}

		if(!isset($data['order']) || $data['order'] === -1){
			$data['order'] = DB::get(self::$dbname)->select(array(
				'fields' => array('COALESCE(MAX(`order`), 0) + 1' => 'newOrder'),
				'from' => self::$tablename,
				'one' => true,
				'return' => DB::RETURN_OBJECT
			))->newOrder;
		}
		else{
			// First update the menus which order is greater than the one you want to include
			$sql = 'UPDATE ' . self::$tablename . ' SET `order` = `order` + 1  WHERE order >= :order';
			DB::get(self::$dbname)->query($sql, array('order' => $data['order']));
		}

		// Insert the menu item
		$menu = parent::add($data);	

		return $menu;
	}

	public function addItem($data){
		$data['menuId'] = $this->id;

		MenuItem::add($data);
	}
}
