<?php

class UserModel extends Model{
	public static $tablename = "User";
	private $role, $permissions, $username;
	
	public function __construct(){
		parent::__construct();
		
		$this->role = RoleModel::getById($this->roleId);	
	}
	
	public function getPermissions($option = ""){
		if(!isset($this->permissions)){
			$sql = 'SELECT P.plugin, P.key, RP.value
					FROM RolePermission RP 
						INNER JOIN Permission P ON RP.permissionId = P.id						
						INNER JOIN User U ON U.roleId = RP.roleId
					WHERE U.id = :id';
			$permissions = DB::get(self::DBNAME)->query($sql, array('id' => $this->id), DB::RETURN_OBJECT);
			$this->permissions = array();			
			foreach($permissions as $permission){
				$this->permissions[$permission->plugin.'.'.$permission->key] = $permission->value;
			}
		}
		return $option ? $this->permissions[$option] : $this->permissions;
	}	
	
	public function canDo($action){
		return !empty($this->getPermissions($action));
	}
	
	public function getUsername(){
		return $this->id ? $this->username : Lang::get('main.guest-username');
	}
	
	public function isConnected(){
		return $this->id && $_SESSION['user']['id'] == $this->id && $this->active;
	}
	
}