<?php
/**
 * DatabaseSessionHandler.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class implements SessionHandlerInterface to define a session engine base on database
 *
 * @package SessionHandlers
 */
class DatabaseSessionHandler implements \SessionHandlerInterface{
    /**
     * The DB instance used to get and set data
     *
     * @var DB
     */
    private $db,

    /**
     * The name of the table containing the sessions
     */
    $table;

    /**
     * Close the session
     */
    public function close(){
        return true;
    }

    /**
     * Destroy the session
     *
     * @param string $sessionId The session id, corresponding to the session cookie
     */
    public function destroy($sessionId){
        // Clean expired sessions
        $this->gc(0);

        $_SESSION = array();

        SessionModel::deleteByExample(new DBExample(array(
            'id' => $sessionId
        )));
    }

    /**
     * Clean expired sessions
     *
     * @param int $maxlifetime The session lifetime (not used)
     */
    public function gc($maxlifetime){
        if(!$maxlifetime) {
            $maxlifetime = max(App::conf()->get('session.lifetime'), ini_get('session.gc_maxlifetime'));
        }

        return (bool) SessionModel::deleteBySQL(':lifetime AND mtime + :lifetime < UNIX_TIMESTAMP()', array(
            'lifetime' => $maxlifetime
        ));
    }


    /**
     * Open a new session
     *
     * @param string $savePath Not used
     * @param string $name     The session name (defaulty 'PHPSESSID')
     */
    public function open($savePath, $name){
        $this->db = App::db();
        $this->table = DB::getFullTablename('Session');

        // Update the session mtime
        if(App::request()->getCookies($name)) {
            SessionModel::getDbInstance()->update(
                SessionModel::getTable(),
                new DBExample(array(
                    'id' => App::request()->getCookies($name)
                )),
                array(
                    'mtime' => time()
                )
            );
        }

        // Clean expired sessions
        $this->gc(0);
    }


    /**
     * Read data of a session
     *
     * @param string $sessionId The session id, corresponding to the session cookie
     *
     * @return string The session data, serialized
     */
    public function read($sessionId){
        $session = SessionModel::getById($sessionId);

        return $session ? $session->data : array();
    }


    /**
     * Write data on the session
     *
     * @param string $sessionId The session id, corresponding to the session cookie
     * @param string $data      The data session to write, serialized
     */
    public function write($sessionId, $data) {
        SessionModel::getDbInstance()->replace (
            SessionModel::getTable(),
            array(
                'id' => $sessionId,
                'data' => $data,
                'mtime' => '\\UNIX_TIMESTAMP()'
            )
        );
    }
}