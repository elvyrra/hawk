<?php
/**
 * MenuItem.php
 */
namespace Hawk;


/**
 * This class describes the MenuItem model
 */
class MenuItem extends Model{
	/**
	 * The associated table
	 */
	public static $tablename = "MenuItem";

	/**
	 * The id of the user menu
	 */
	const USER_ITEM_ID = 1;

	/**
	 * The id of the admin menu
	 */
	const ADMIN_ITEM_ID = 2;

	/**
	 * Registered items
	 */
	private static $instances = array();

	/**
	 * Constructor
	 * @param array $data The data to set on the instance properties	 
	 */
	public function __construct($data = array()){
		parent::__construct($data);

		$this->visibleItems = array();

		if(!empty($this->labelKey)){
			$this->label = Lang::get($this->labelKey);
		}

		if(!empty($this->action)){
			$params = !empty($this->actionParameters) ? json_decode($this->actionParameters, true) : array();
			$this->url = App::router()->getUri($this->action, $params);

			if($this->url == Router::INVALID_URL){
				$this->url = $this->action;
			}
		}

		self::$instances[$this->plugin . '.' . $this->name] = $this;
	}
	

	/**
	 * Get the items available for a specific user
	 * @param User $user The user. If not set, the current session user is set
	 * @return array The list of items
	 */
	public static function getAvailableItems($user = null){
		if($user == null){
			$user = App::session()->getUser();
		}

		// Get all items
		$items = self::getAll(self::$primaryColumn, array(), array('parentId' => 'ASC', 'order' => 'ASC'));

		// Filter unavailable items (that are not active or not accessible)
		$items = array_filter($items, function($item) use($user){
			return $item->active && (!$item->permissionId || $user->isAllowed($item->permissionId));
		});

		// Put the sub items under their parent item
		foreach($items as $item){
			if($item->parentId){
				$items[$item->parentId]->visibleItems[$item->order] = $item;
				unset($items[$item->id]);
			}
		}

		return $items;
	}


	/**
	 * Add a new item in the database
	 * @param array $data The item data to insert
	 * @return MenuItem The created item
	 */
	public static function add($data){
		if(empty($data['parentId'])){
			$data['parentId'] = 0;
		}

		$data['order'] = App::db()->select(array(
			'fields' => array('COALESCE(MAX(`order`), 0) + 1' => 'newOrder'),
			'from' => self::getTable(),
			'where' => new DBExample(array('parentId' => $data['parentId'])),
			'one' => true,
			'return' => DB::RETURN_OBJECT
		))->newOrder;

		// Insert the menu item
		$item = parent::add($data);	

		$event = new Event('menuitem.added', array('item' => $item));
		$event->trigger();
		
		return $item;
	}

	/**
	 * Find a menu item by it name, formatted as "<plugin>.<name>"
	 * @param string $name The item name
	 * @return MenuItem The found item
	 */
	public static function getByName($name){
		if(isset(self::$instances[$name])){
			return self::$instances[$name];
		}
		else{
			list($plugin, $name) = explode('.', $name, 2);

			return self::getByExample(new DBExample(
				array(
					'plugin' => $plugin,
					'name' => $name
				)
			));
		}
	}

	/**
	 * Delete the menu item
	 */
	public function delete(){
		App::db()->update(
			self::getTable(), 
			new DBExample(array('parentId' => $this->id)), 
			array('parentId' => 0)
		);

		parent::delete();

		// Send an event to compute actions on menu item deletion
		$event = new Event('menuitem.deleted', array('item' => $this));
		$event->trigger();
	}
}