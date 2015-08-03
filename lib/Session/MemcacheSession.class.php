<?php


class MemcacheSession implements SessionHandlerInterface{    
    private $client;
    
    public function close(){
        return $this->client->close();
    }
    
    public function destroy($session_id){
        return $this->client->delete($session_id);
    }
    
    public function gc($maxlifetime){
        
    }
    
    public function open($save_path, $name){
        $this->client = new Memcache();
        return $this->client->connect(Conf::get('session.host'), Conf::get('session.port'));
    }
    
    public function read($session_id){
        return $this->client->get($session_id);
    }
    
    public function write($session_id, $session_data){
        return $this->client->set($session_id, $session_data, MEMCACHE_COMPRESSED, time() + Option::get('main.session-lifetime'));
    }
    
}