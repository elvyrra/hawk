<?php
/**
 * User.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;


/**
 * This model describes the user data
 */
class User extends Model{
	/**
	 * The associated table
	 * @var string
	 */
	protected static $tablename = "User";


	/**
	 * The user profile data
	 * @var array
	 */
	private $profile, 

	/**
	 * The user permissions
	 * @var array
	 */
	$permissions;

	/**
	 * The id of guest users
	 */
	const GUEST_USER_ID = 0;

	/**
	 * The id for the first administrator user
	 */
	const ROOT_USER_ID = 1;
	
	/**
	 * Constructor
	 * @param array $data The data to set to the user
	 */
	public function __construct($data = array()){
		parent::__construct($data);
		if(!empty($this->roleId)){
			$this->role = Role::getById($this->roleId);		
		}
	}

	/**
	 * Get all users except guest user
	 * @param string $index The field to use as key in the returned array
	 * @param array $fields The table fields to get
	 * @param array $order The order instruction to get the users
	 * @return array
	 */
	public static function getAll($index = null, $fields = array(), $order = array()){
		$example = array(
			'id' => array(
				'$ne' => self::GUEST_USER_ID
			)
		);
		return self::getListByExample(new DBExample($example), $index, $fields, $order);
	}
	

	/**
	 * Get a user by it username
	 * @param string $username The username to search
	 * @return User
	 */
	public static function getByUsername($username){
		return self::getByExample(new DBExample(array('username' => $username)));
	}
	
	/**
	 * Set all the permissions on the user
	 */
	private function getPermissions(){
		if(!isset($this->permissions)){
			$sql = 'SELECT P.plugin, P.key, P.id
					FROM ' . RolePermission::getTable() . ' RP 
						INNER JOIN ' . Permission::getTable() . ' P ON RP.permissionId = P.id						
						INNER JOIN ' . self::getTable() . ' U ON U.roleId = RP.roleId
					WHERE U.id = :id AND RP.value=1';

			$permissions = DB::get(self::$dbname)->query($sql, array('id' => $this->id), array('return' => DB::RETURN_OBJECT));
			$this->permissions = array();			
			foreach($permissions as $permission){
				// Register the permission by it id
				$this->permissions[$permission->id] = 1;

				// Regoster the permission by it name
				$this->permissions[$permission->plugin][$permission->key] = 1;
			}
		}		
	}	


	/**
	 * Get the user's profile data
	 * @param string $prop The property name to get. If not set, the function will return an array containing all the profile data
	 * @return mixed
	 */
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


	/**
	 * Set the user's profile data. This method does not register the data in database, only set in the user properties
	 * @param string $prop The property name to set
	 * @param string $value The value to set
	 * @see saveProfile	 
	 */
	public function setProfileData($prop, $value){
		$this->profile[$prop] = $value;
	}


	/**
	 * Save the user's profile in the database
	 */
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
	

	/**
	 * 	Check if the user is allowed to perform an action
	 * @param string|int $action This parameter can represent :
	 *								- A specific action, formatted as "<plugin>.<key>"
	 *								- A permission id, when an integer is given
	 *								- A plugin name, so this method will check if at least one action of the plugin is avaialble for the user
	 * @return boolean TRUE if the suer is allowed to perform the action, else FALSE
	 */
	public function isAllowed($action){
		if($this->roleId == Role::ADMIN_ROLE_ID){
			// The admins can perform any action
			return true;
		}
		if($action !== Permission::ALL_PRIVILEGES_ID && $action !== Permission::ALL_PRIVILEGES_NAME){
			if($this->isAllowed(Permission::ALL_PRIVILEGES_ID)){
				// The user has all privileges
				return true;
			}
		}
		
		// Get the user permissions
		$this->getPermissions();

		if(strpos($action, '.') !== false){
			// Check a specific action
			list($plugin, $command) = explode('.', $action);
		}
		else{
			// Check a action by it id or a while plugin
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
	

	/**
	 * Get the user's username
	 * @return string The user's username
	 */	
	public function getUsername(){
		return $this->id ? $this->username : Lang::get('main.guest-username');
	}
	

	/**
	 * Get the user's full name. This method returns the real name if it set in the user's profile, else, it returns his username
	 * @return string
	 */
	public function getDisplayName(){
		return $this->getProfileData('realname') ? $this->getProfileData('realname') : $this->getUsername();
	}
	
	/**
	 * Check if the user is connected or not
	 * @return bool
	 */
	public function isConnected(){
		return $this->id && $_SESSION['user']['id'] == $this->id && $this->active;
	}
	

	/**
	 * Check if the user can access the application
	 * @return bool
	 */
	public function canAccessApplication(){
		return $this->isConnected() || Option::get('main.allow-guest');
	}

	/**
	 * Check of the user is removable. A user is removable if he's not the one executing the current script, and if he's not a guest or the main application administrator
	 * @return bool
	 */
	public function isRemovable(){
		return $this->id != Session::getUser()->id && $this->id != self::ROOT_USER_ID && $this->id != self::GUEST_USER_ID;
	}
}