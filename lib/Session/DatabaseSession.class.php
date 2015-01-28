<?php

class DatabaseSession implements SessionHandlerInterface{
    private $db, $table;
    private static $data;
    
	
	
    public function close(){
        return true;
    }
    
    public function destroy($session_id){
        return $this->db->delete($this->table, 'id = :id', array('id' => $session_id)) ? true : false;
    }
    
    public function gc($maxlifetime){
        return $this->db->delete($this->table, 'expire < :lifetime', array('lifetime' => time() - $maxlifetime)) ? true : false;
    }
    
    public function open($save_path, $name){
        $this->db = DB::get(MAINDB);        
    }
    
    public function read($session_id){
        $this->table = 'Session';
        $line = $this->db->select(array(
            'table' => $this->table,
            'condition' => 'id = :id',
            'binds' => array('id' => $session_id),
            'one' => true
        ));
        
        return $line['data'];
    }
    
    public function write($session_id, $session_data){		
        return $this->db->insert($this->table, array(
            'id' => $session_id,
            'data' => $session_data,
            'expire' => (time() + ini_get("session.gc_maxlifetime"))
        ), 'IGNORE' , 'data = "'.addcslashes($session_data, '"').'"');
    }
}