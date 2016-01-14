<?php

namespace Hawk\Plugins\StaffManager;


class StaffAbsence extends Model{
	protected static $tablename = "StaffAbsence";	
	protected static $primaryColumn = "id";


	public function __construct($data = array()){
		parent::__construct($data);
	}
}