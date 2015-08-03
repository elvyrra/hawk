<?php


class DatabaseSession implements SessionHandlerInterface{
    private $db, $lifetime;
    private static $data;
    
	const TABLE = 'Session';
	
    public function close(){
        return true;
    }
    
    public function destroy($session_id){
        return $this->db->delete(self::TABLE, 'id = :id', array('id' => $session_id)) ? true : false;

        // Clean expired sessions
        $this->gc(0);
    }
    
    public function gc($maxlifetime){
        return $this->db->delete(self::TABLE, ':lifetime AND mtime + :lifetime < UNIX_TIMESTAMP()', array('lifetime' => $this->lifetime)) ? true : false;
    }
    
    public function open($savePath, $name){
        $this->db = DB::get(MAINDB);        
        $this->lifetime = Option::get('main.session-lifetime');

        // Clean expired sessions
        $this->gc(0);
    }
    
    public function read($session_id){
        $line = $this->db->select(array(
            'from' => self::TABLE,
            'where' => 'id = :id',
            'binds' => array('id' => $session_id),
            'one' => true
        ));
        
        return $line['data'];
    }
    
    public function write($sessionId, $data){	
        $sql = 'REPLACE INTO ' . self::TABLE . ' (id, data, mtime) VALUES (:id, :data, UNIX_TIMESTAMP())';
        return $this->db->query($sql, array(
            'id' => $sessionId,
            'data' => $data,
        ));
    }
}