<?php


class User extends Model{
	protected static $tablename = "User";	
	protected static $primaryColumn = "id";

	private $profile;

	const GUEST_USER_ID = 0;
	const ROOT_USER_ID = 1;
	
	public function __construct($data = array()){
		parent::__construct($data);
		if(!empty($this->roleId)){
			$this->role = Role::getById($this->roleId);		
		}
	}

	public static function getAll($index = null, $fields = array(), $order = array()){
		$example = array('id' => array('$ne' => self::GUEST_USER_ID));
		return self::getListByExample(new DBExample($example), $index, $fields, $orders);
	}
	
	public static function getByUsername($username){
		return self::getByExample(new DBExample(array('username' => $username)));
	}
	
	private function getPermissions(){
		if(!isset($this->permissions)){
			$sql = 'SELECT P.plugin, P.key
					FROM RolePermission RP 
						INNER JOIN Permission P ON RP.permissionId = P.id						
						INNER JOIN User U ON U.roleId = RP.roleId
					WHERE U.id = :id AND RP.value=1';
			$permissions = DB::get(self::$dbname)->query($sql, array('id' => $this->id), array('return' => DB::RETURN_OBJECT));
			$this->permissions = array();			
			foreach($permissions as $permission){
				$this->permissions[$permission->plugin][$permission->key] = 1;
			}
		}		
	}	

	public function getProfileData($prop = ""){
		if(!isset($this->profile)){
			$sql = 'SELECT Q.name, V.value 
					FROM ' . ProfileQuestionValue::getTable()  . ' V INNER JOIN ' . ProfileQuestion::getTable() . ' Q ON V.question = Q.name
					WHERE V.userId = :id';

			$data = DB::get(self::$dbname)->query($sql, array('id' => $this->id), array('return' => DB::RETURN_ARRAY, 'index' => 'name'));			
			$this->profile = array_map(function($v){ return $v['value']; }, $data);
		}		
		return $prop ? (isset($this->profile[$prop]) ? $this->profile[$prop] : null) : $this->profile;
	}

	public function setProfileData($prop, $value){
		$this->profile[$prop] = $value;
	}

	public function saveProfile(){
		foreach($this->profile as $prop => $value){
			$questionValue = new ProfileQuestionValue(array(
				'question' => $prop,
				'userId' => $this->id,
				'value' => $value
			));
			$questionValue->save();
		}
	}
	
	public function isAllowed($action){
		if($this->roleId == Role::ADMIN_ROLE_ID){
			return true;
		}
		
		$this->getPermissions();
		if(strpos($action, '.') !== false){
			list($plugin, $command) = explode('.', $action);
		}
		else{
			$plugin = $action;
		}
		
		if(empty($command)){
			// Return if the user has at least one authorization in the plugin
			return !empty($this->permissions[$plugin]);
		}
		else{
			// Return if the user can perform the action
			return !empty($this->permissions[$plugin][$command]);
		}
	}	
		
	public function getUsername(){
		return $this->id ? $this->username : Lang::get('main.guest-username');
	}
	
	public function getDisplayName(){
		return Option::get('user.display-realname') && $this->getProfileData('realname') ? $this->getProfileData('realname') : $this->getUsername();
	}
	
	public function isConnected(){
		return $this->id && $_SESSION['user']['id'] == $this->id && $this->active;
	}
	
	public function canAccessApplication(){
		return $this->isConnected() || Option::get('main.allow-guest');
	}

	public function isRemovable(){
		return $this->id != Session::getUser()->id && $this->id != self::ROOT_USER_ID && $this->id != self::GUEST_USER_ID;
	}
}