<?php
/**
 * Session.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This trait is used to manage the user's sessions. It's declared as trait
 * to be used in custom classes developer could want to develop to extend
 * the session management (manage licences, number of simultaneous connections, ...)
 * @package Core
 */
class Session extends Singleton{
	/**
	 * Static variable that registers the logged state of the current user
	 * @var boolean
	 */
	private $logged,

	/**
	 * Static variable that registers the current user of the session
	 * @var User
	 */
	$user,


	/**
	 * The session data, stored in $_SESSION
	 */
	$data = array();


	/**
	 * The session instance
	 */
	protected static $instance;


	/**
	 * Initialize the session user
	 */
	public function init(){
		if(!$this->getData('user.id')) {
			$this->setData('user.id', 0);
		}

		if(App::conf()->has('db')){
			// Get the user from the database
			$this->user = User::getById($this->getData('user.id'));
		}
		else{
			// The database does not exists yet. Create a 'fake' guest user
			$this->user = new User(array(
				'id' => User::GUEST_USER_ID,
				'username' => 'guest',
				'active' => 0,
			));

			$this->logged = false;
		}

		$this->logged = $this->user->isLogged();
	}

	/**
	 * Get the user of the current session
	 * @return User the user of the current session
	 */
	public function getUser(){
		return $this->user;
	}

	/**
	 * Returns if the current user is logged or not
	 * @return bool true if the user is logged, else returns false
	 */
	public function isLogged(){
		return $this->logged;
	}


	/**
	 * Check if the user is allowed to make action
	 * @param string $action The name of the permission to check
	 * @return boolean True if the user is allowed to perform this action, False in other case
	 */
	public function isAllowed($action){
		return $this->getUser()->isAllowed($action);
	}


	/**
	 * Set data in session
	 * @param array $name The variable name to set in the session. To set a parameter in a sub array, type 'index1.index2'. For example, to set the parameter $data['user']['name'], type 'user.name'
	 * @param mixed $value The value to set
	 */
	public function setData($name, $value){
		$fields = explode('.', $name);
		$tmp = &$this->data;
		foreach($fields as $field){
			$tmp = &$tmp[$field];
		}
		$tmp = $value;

		$_SESSION = $this->data;
	}

	/**
	 * Get session data
	 * @param string $name The variable name to get in session. For a multidimentionnal data, write 'level1.level2'
	 */
	public function getData($name = null){
		$this->data = $_SESSION;

		if(!isset($this->data)){
			return null;
		}

		if(!$name){
			return $this->data;
		}
		else{
			$fields = explode('.', $name);
			$tmp = $this->data;
			foreach($fields as $field){
				if(isset($tmp[$field])){
					$tmp = $tmp[$field];
				}
				else{
					return null;
				}
			}
			return $tmp;
		}
	}
}

