<?php
/**
 * MenuItem.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk;


/**
 * This class describes the MenuItem model
 *
 * @package BaseModels
 */
class MenuItem extends Model{
    /**
     * The associated table
     *
     * @var sting
     */
    public static $tablename = "MenuItem";

    /**
     * The primary column
     *
     * @var string
     */
    protected static $primaryColumn = 'id';

    /**
     * The DB instance name to get data in database default MAINDB
     *
     * @var string
     */
    protected static $dbname = MAINDB;

    /**
     * The model fields
     *
     * @var array
     */
    protected static $fields = array(
        'id' => array(
            'type' => 'INT(11)',
            'auto_increment' => true
        ),
        'plugin' => array(
            'type' => 'VARCHAR(32)',
            'default' => ''
        ),
        'name' => array(
            'type' => 'VARCHAR(64)'
        ),
        'parentId' => array(
            'type' => 'INT(11)',
            'default' => 0
        ),
        'labelKey' => array(
            'type' => 'VARCHAR(128)'
        ),
        'action' => array(
            'type' => 'VARCHAR(128)'
        ),
        'actionParameters' => array(
            'type' => 'VARCHAR(1024)'
        ),
        'target' => array(
            'type' => 'VARCHAR(64)'
        ),
        'order' => array(
            'type' => 'INT(2)'
        ),
        'active' => array(
            'type' => 'TINYINT(1)'
        ),
        'icon' => array(
            'type' => 'varchar(64)'
        )
    );

    protected static $constraints = array(
        'index2' => array(
            'type' => 'unique',
            'fields' => array(
                'plugin',
                'name'
            )
        )
    );

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
     *
     * @var array
     */
    private static $instances = array();

    /**
     * The menu item label
     *
     * @var string
     */
    public $label = '';


    /**
     * Constructor
     *
     * @param array $data The data to set on the instance properties
     */
    public function __construct($data = array()){
        parent::__construct($data);

        $this->visibleItems = array();

        if(empty($this->label) && !empty($this->labelKey)) {
            $this->label = Lang::get($this->labelKey);
        }

        if(!empty($this->action)) {
            $params = !empty($this->actionParameters) ? json_decode($this->actionParameters, true) : array();
            $this->url = App::router()->getUri($this->action, $params);

            if($this->url == Router::INVALID_URL) {
                $this->url = $this->action;
            }
        }

        self::$instances[$this->plugin . '.' . $this->name] = $this;
    }


    /**
     * Get the items available for a specific user
     *
     * @param User $user The user. If not set, the current session user is set
     *
     * @return array The list of items
     */
    public static function getAvailableItems($user = null){
        if($user == null) {
            $user = App::session()->getUser();
        }

        // Get all items
        $items = self::getListByExample(
            new DBExample(array(
                'active' => 1
            )),
            self::$primaryColumn,
            array(),
            array(
                'parentId' => 'ASC',
                'order' => 'ASC'
            )
        );

        // Filter unavailable items (that are not active or not accessible)
        $items = array_filter($items, function ($item) use ($user) {
            return $item->isVisible($user);
        });

        // Put the sub items under their parent item
        foreach($items as $item){
            if($item->parentId) {
                $items[$item->parentId]->visibleItems[$item->order] = $item;
                unset($items[$item->id]);
            }
        }

        $items = array_values($items);
        foreach($items as $item) {
            $item->visibleItems = array_values($item->visibleItems);
        }

        return $items;
    }


    /**
     * Add a new item in the database
     *
     * @param array $data The item data to insert
     *
     * @return MenuItem The created item
     */
    public static function add($data){
        if(empty($data['parentId'])) {
            $data['parentId'] = 0;
        }

        $data['order'] = App::db()->select(array(
            'fields' => array('COALESCE(MAX(`order`), 0) + 1' => 'newOrder'),
            'from' => self::getTable(),
            'where' => new DBExample(array('parentId' => $data['parentId'])),
            'one' => true,
            'return' => DB::RETURN_OBJECT
        ))->newOrder;

        if(!empty($data['actionParameters']) && is_array($data['actionParameters'])) {
            $data['actionParameters'] = json_encode($data['actionParameters']);
        }

        if(!isset($data['active'])) {
            $data['active'] = 1;
        }

        // Insert the menu item
        $item = parent::add($data);

        $event = new Event('menuitem.added', array('item' => $item));
        $event->trigger();

        return $item;
    }

    /**
     * Find a menu item by it name, formatted as "<plugin>.<name>"
     *
     * @param string $name The item name
     *
     * @return MenuItem The found item
     */
    public static function getByName($name){
        if(isset(self::$instances[$name])) {
            return self::$instances[$name];
        }
        else{
            list($plugin, $name) = explode('.', $name, 2);

            return self::getByExample(
                new DBExample(
                    array(
                    'plugin' => $plugin,
                    'name' => $name
                    )
                )
            );
        }
    }


    /**
     * Get all the menu items of a plugin
     *
     * @param string $plugin The plugin to get
     *
     * @return array
     */
    public static function getPluginMenuItems($plugin){
        return self::getListByExample(new DBExample(array(
            'plugin' => $plugin
        )));
    }


    /**
     * Delete the menu item
     */
    public function delete(){
        App::db()->update(
            self::getTable(),
            new DBExample(array(
                'parentId' => $this->id
            )),
            array('parentId' => 0)
        );

        parent::delete();

        // Send an event to compute actions on menu item deletion
        $event = new Event('menuitem.deleted', array('item' => $this));
        $event->trigger();
    }

    /**
     * Check if a menu is accessible for a given user
     * @param  User $user The user to check the menu is visible for
     *
     * @return boolean     True if the user can access the route defined by this menu, else False
     */
    public function isVisible($user) {
        $route = App::router()->getRouteByName($this->action);

        if($this->actionParameters) {
            $data = json_decode($this->actionParameters, true);

            foreach($data as $key => $value) {
                $route->setData($key, $value);
            }
        }

        if($route) {
            return $route->isAccessible();
        }

        return true;
    }
}