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
		

	/**
	 * Get the list of menus that are aavailable for the given user
	 * @param User $user The user to look for the available menus
	 * @param string $index The column to use as key in the result array
	 * @return array The list of available menus for the user
	 */
	public static function getAvailableMenus($user = null, $index = null){
		if($user === null){
			// Get the current user
			$user = Session::getUser();
		}
		if($index === null){
			// Defaulty get the primary column as index
			$index = self::$primaryColumn;
		}
		
		$menus = self::getAll($index, array(), array('order' => 'ASC'));
		$menus = array_filter($menus, function($menu) use($user){
			return ! $menu->permissionId || $user->isAllowed($menu->permissionId);
		});

		// Get the items to populate the found menus
		$menuItems = MenuItem::getAvailableItems();
		foreach($menuItems as $item){
			if(!empty($menus[$item->menuId])){
				$menus[$item->menuId]->visibleItems[] = $item;
			}
		}

		return $menus;
	}


	/**
	 * Get a menu by it name and it plugin	 
	 * @param string $name The menu name
	 * @return Menu The found menu
	 */
	public static function getByName($name){
		list($plugin, $key) = explode('.', $name, 2);
		return self::getByExample(new DBExample(array('plugin' => $plugin, 'name' => $key)));
	}
	

	/**
	 * Add a new menu in the database
	 * @param array $data The menu data to insert
	 * @return Menu The created menu
	 */
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


	/**
	 * Add an item to the menu
	 * @param array $data The item data to insert
	 * @return MenuItem the added menu item
	 */
	public function addItem($data){
		$data['menuId'] = $this->id;

		return MenuItem::add($data);
	}
}
