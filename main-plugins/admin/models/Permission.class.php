<?php


class Permission extends Model{
	protected static $tablename = "Permission";
	protected static $primaryColumn = "id";
	
	const ALL_PRIVILEGES_ID = 1;

	public static function getAllGroupByPlugin(){
		$permissions = self::getAll();
		$groups = array();
		foreach($permissions as $permission){
			if(!isset($groups[$permission->plugin])){
				$groups[$permission->plugin] = array();
			}

			$groups[$permission->plugin][] = $permission;
		}

		return $groups;
	}

	public static function getPluginPermissions($plugin){
		return self::getListByExample(new DBExample(array('plugin' => $plugin)));
	}

	public static function getByName($name){
		list($plugin, $key) = explode('.', $name);

		return self::getByExample(new DBExample(array('plugin' => $plugin, 'key' => $key)));
	}

	public static function add($name, $default = 1, $availableForGuest = 0){
		list($plugin, $key) = explode('.', $name);
		$permission = parent::add(array(
			'plugin' => $plugin,
			'key' => $key,
			'availableForGuests' => $availableForGuest
		));

		$roles = Role::getAll();
		foreach($roles as $role){
			$value = $role->id == Role::GUEST_ROLE_ID ? ($availableForGuest ? $default : 0) : $default;
			RolePermission::add(array(
				'roleId' => $role->id,
				'permissionId' => $permission->id,
				'value' => $value
			));
		}

		return $permission;
	}
}