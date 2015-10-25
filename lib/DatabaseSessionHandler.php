<?php
/**
 * DatabaseSessionHandler.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class implements SessionHandlerInterface to define a session engine base on database
 * @package SessionHandlers
 */
class DatabaseSessionHandler implements \SessionHandlerInterface{
    /**
     * The DB instance used to get and set data
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
     * @param string $sessionId The session id, corresponding to the session cookie
     */
    public function destroy($sessionId){
        return $this->db->delete($this->table, 'id = :id', array('id' => $sessionId)) ? true : false;

        // Clean expired sessions
        $this->gc(0);
    }
    
    /**
     * Clean expired sessions
     * @param int $maxlifetime The session lifetime (not used)
     */
    public function gc($maxlifetime){
        return $this->db->delete($this->table, ':lifetime AND mtime + :lifetime < UNIX_TIMESTAMP()', array('lifetime' => Conf::get('session.lifetime'))) ? true : false;
    }
    

    /**
     * Open a new session
     * @param string $savePath Not used
     * @param string $name Not used
     */
    public function open($savePath, $name){
        $this->db = DB::get(MAINDB);   
        $this->table = DB::getFullTablename('Session');

        // Update the session mtime
        if(Request::getCookies($name)){
            $this->db->update($this->table, new DBExample(array('id' => Request::getCookies($name))), array('mtime' => time()));
        }

        // Clean expired sessions
        $this->gc(0);
    }
    

    /**
     * Read data of a session
     * @param string $sessionId The session id, corresponding to the session cookie
     * @return string The session data, serialized
     */
    public function read($sessionId){
        $line = $this->db->select(array(
            'from' => $this->table,
            'where' => 'id = :id',
            'binds' => array('id' => $sessionId),
            'one' => true
        ));
        
        return $line['data'];
    }
    

    /**
     * Write data on the session
     * @param string $sessionId The session id, corresponding to the session cookie
     * @param string $data The data session to write, serialized    
     */
    public function write($sessionId, $data){	
        $sql = 'REPLACE INTO ' . $this->table . ' (id, data, mtime) VALUES (:id, :data, UNIX_TIMESTAMP())';
        return $this->db->query($sql, array(
            'id' => $sessionId,
            'data' => $data,
        ));
    }
}