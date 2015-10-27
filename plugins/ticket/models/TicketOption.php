<?php

namespace Hawk\Plugins\Ticket;


class TicketOption extends Model{
	protected static $tablename = "Option";	
	protected static $primaryColumn = "plugin";


	public function __construct($data = array()){
		parent::__construct($data);
	}
}