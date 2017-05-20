<?php
/**
 * Permission.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the permission model
 *
 * @package BaseModels
 */
class Permission extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    protected static $tablename = "Permission";

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
            'type' => 'VARCHAR(32)'
        ),
        'key' => array(
            'type' => 'VARCHAR(64)'
        ),
        'availableForGuests' => array(
            'type' => 'TINYINT(1)',
            'default' => '0'
        )
    );

    /**
     * The model constraints
     *
     * @var array
     */
    protected static $constraints = array(
        'plugin' => array(
            'type' => 'unique',
            'fields' => array(
                'plugin',
                'key'
            )
        )
    );

    /**
     * The id of the permission giving rights on all permissions
     */
    const ALL_PRIVILEGES_ID = 1;

    /**
     * The name of the permission giving rights on all permissions
     */
    const ALL_PRIVILEGES_NAME = 'admin.all';


    /**
     * Get all permissions, grouped by plugin name
     *
     * @return array The list of permissions, indexed by the plugins names, where each element is an array contaning the plugin permissions
     */
    public static function getAllGroupByPlugin(){
        $permissions = self::getAll();
        $groups = array();
        foreach($permissions as $permission){
            if(!isset($groups[$permission->plugin])) {
                $groups[$permission->plugin] = array();
            }

            $groups[$permission->plugin][] = $permission;
        }

        return $groups;
    }


    /**
     * Get all the permissions for a given plugin
     *
     * @param string $plugin The plugin name
     *
     * @return array The list of found permissions
     */
    public static function getPluginPermissions($plugin){
        return self::getListByExample(new DBExample(array('plugin' => $plugin)));
    }


    /**
     * Get a permission by it name, formatted as <plugin>.<permissionName>
     *
     * @param string $name The permission name
     *
     * @return Permission The found permission
     */
    public static function getByName($name){
        list($plugin, $key) = explode('.', $name);

        return self::getByExample(new DBExample(array('plugin' => $plugin, 'key' => $key)));
    }


    /**
     * Add a new permission in the database
     *
     * @param string $name               The permission name, formatted as "<plugin>.<key>"
     * @param int    $default            The default value for this permission
     * @param int    $availableForGuests Defines if the permission can be set to true for guest users
     *
     * @return Permission The created permission
     */
    public static function add($name, $default = 1, $availableForGuests = 0){
        list($plugin, $key) = explode('.', $name);
        $permission = parent::add(array(
            'plugin' => $plugin,
            'key' => $key,
            'availableForGuests' => $availableForGuests
        ));

        $roles = Role::getAll();
        foreach($roles as $role){
            $value = $role->id == Role::GUEST_ROLE_ID ? ($availableForGuests ? $default : 0) : $default;
            RolePermission::add(array(
                'roleId' => $role->id,
                'permissionId' => $permission->id,
                'value' => $value
            ));
        }

        return $permission;
    }
}