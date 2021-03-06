<?php
/**
 * Session.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This trait is used to manage the user's sessions. It's declared as trait
 * to be used in custom classes developer could want to develop to extend
 * the session management (manage licences, number of simultaneous connections, ...)
 *
 * @package Core
 */
class Session extends Singleton{
    /**
     * Static variable that registers the logged state of the current user
     *
     * @var boolean
     */
    protected $logged,

    /**
     * Static variable that registers the current user of the session
     *
     * @var User
     */
    $user,


    /**
     * The session data, stored in $_SESSION
     *
     * @var array
     */
    $data = array();


    /**
     * The session instance
     *
     * @var array
     */
    protected static $instance;


    /**
     * Initialize the session user
     */
    public function init() {
        $lifetime = App::conf()->get('session.lifetime') ? (int)App::conf()->get('session.lifetime') : 0;
        $path =  App::conf()->get('session.path') ? App::conf()->get('session.path') : '/';
        $domain =  App::conf()->get('session.domain') ? App::conf()->get('session.domain') : '';
        $secure =  App::conf()->get('session.secure') ? App::conf()->get('session.secure') : false;
        $httpOnly =  App::conf()->get('session.httponly') ? App::conf()->get('session.httponly') : false;

        session_set_cookie_params(
            $lifetime,
            $path,
            $domain,
            $secure,
            $httpOnly
        );
        session_start();

        if (!$this->getData('user.id')) {
            $this->setData('user.id', 0);
        }

        if (App::conf()->has('db')) {
            // Get the user from the database
            $this->user = User::getById($this->getData('user.id'));
            $this->logged = $this->user->isLogged();
        }
        else {
            // The database does not exists yet. Create a 'fake' guest user
            $this->user = new User(array(
                'id' => User::GUEST_USER_ID,
                'username' => 'guest',
                'active' => 0
            ));

            $this->logged = false;
        }


        if($lifetime) {
            setcookie(
                session_name(),
                session_id(),
                $lifetime ? time() + $lifetime : 0,
                $path,
                $domain,
                $secure,
                $httpOnly
            );
        }
    }

    /**
     * Get the user of the current session
     *
     * @return User the user of the current session
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Returns if the current user is logged or not
     *
     * @return bool true if the user is logged, else returns false
     */
    public function isLogged() {
        return $this->logged;
    }


    /**
     * Check if the user is allowed to make action
     *
     * @param string $action The name of the permission to check
     *
     * @return boolean True if the user is allowed to perform this action, False in other case
     */
    public function isAllowed($action) {
        return $this->getUser()->isAllowed($action);
    }


    /**
     * Set data in session
     *
     * @param array $name  The variable name to set in the session.
     *                     To set a parameter in a sub array, type 'index1.index2'.
     *                     For example, to set the parameter $data['user']['name'], type 'user.name'
     * @param mixed $value The value to set
     */
    public function setData($name, $value) {
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
     *
     * @param string $name The variable name to get in session. For a multidimentionnal data, write 'level1.level2'
     */
    public function getData($name = null) {
        $this->data = isset($_SESSION) ? $_SESSION : array();

        if (!isset($this->data)) {
            return null;
        }

        if (!$name) {
            return $this->data;
        }
        else {
            $fields = explode('.', $name);
            $tmp = $this->data;
            foreach($fields as $field){
                if (isset($tmp[$field])) {
                    $tmp = $tmp[$field];
                }
                else {
                    return null;
                }
            }
            return $tmp;
        }
    }

    /**
     * Remove session data
     *
     * @param string $name The name of the session variable to remove
     */
    public function removeData($name = '') {
        $this->getData();

        if($name) {
            // remove the variable $name from the session
            $fields = explode('.', $name);
            $tmp = &$this->data;
            foreach(array_slice($fields, 0, -1) as $field) {
                $tmp = &$tmp[$field];
            }
            unset($tmp[end($fields)]);
        }
        else {
            $this->data = array();
        }

        $_SESSION = $this->data;
    }

}

