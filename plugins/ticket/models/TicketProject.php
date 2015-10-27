<?php

namespace Hawk\Plugins\Ticket;


class TicketProject extends Model{
	protected static $tablename = "TicketProject";	
	protected static $primaryColumn = "id";


	public function __construct($data = array()){
		parent::__construct($data);
		if(!empty($this->timestamp)){
			$this->timestamp = date('Y-m-d');		
		}
	}
}