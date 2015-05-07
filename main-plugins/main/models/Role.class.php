<?php

class Role extends Model{
	public static $tablename = "Role";
	protected static $primaryColumn = "id";
	
	const GUEST_ROLE_ID = 0;
	const ADMIN_ROLE_ID = 1;

	public static function getAll($index = null, $fields = array(), $order = array(), $includeGuest= false){
		if($includeGuest){
			return parent::getAll($index, $fields, $order);
		}
		else{
			$example = array('id' => array('$ne' => self::GUEST_ROLE_ID));
			return self::getListByExample(new DBExample($example), $index, $fields, $orders);
		}
	}

	public function isRemovable(){
		return $this->removable && Option::get('roles.default-role') != $this->id;
	}
	
	public function getLabel(){
		return Lang::get('roles.role-' . $this->id . '-label');
	}
}