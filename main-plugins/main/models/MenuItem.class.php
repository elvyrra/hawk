<?php


class MenuItem extends Model{
	public static $tablename = "MenuItem";
	protected static $primaryColumn = "id";

	const USER_ITEM_ID = 1;
	const ADMIN_ITEM_ID = 2;

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
	
	public static function getAvailableItems($user = null){
		if($user == null){
			$user = Session::getUser();
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

	public static function add($data){
		$data['order'] = DB::get(self::$dbname)->select(array(
			'fields' => array('COALESCE(MAX(`order`), 0) + 1' => 'newOrder'),
			'from' => self::$tablename,
			'where' => new DBExample(array('parentId' => $data['parentId'])),
			'one' => true,
			'return' => DB::RETURN_OBJECT
		))->newOrder;

		// Insert the menu item
		$item = parent::add($data);	

		EventManager::trigger(new Event('menu-item.added', array('item' => $item)));

		return $item;
	}
}