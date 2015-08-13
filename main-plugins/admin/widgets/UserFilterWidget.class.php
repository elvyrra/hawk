<?php
/**
 * UserFilterWidget.class.php
 * @author Elvyrra SAS
 */



/**
 * This Widget is used to filter the users list by status or role
 */
class UserFilterWidget extends Widget{
	public static $filters = array('status', 'roleId');

	public function getFilters(){
		$result = !empty($_COOKIE['user-filter']) ? json_decode($_COOKIE['user-filter'], true) : array();

		foreach(self::$filters as $name){
			if(isset($_GET[$name])){
				$result[$name] = $_GET[$name];
			}

			if(empty($result[$name])){
				$result[$name] = '0';
			}
		}
		setcookie('user-filter', json_encode($result));
		
		return $result;
	}

	public function display(){
		$filters = $this->getFilters();
		$roles = array(
			0 => ' - '
		);
		foreach(Role::getAll() as $role){
			$roles[$role->id] = $role->getLabel();
		}

		$form = new Form(array(
			'id' => 'user-filter-form',
			'fieldsets' => array(
				'form' => array(
					new RadioInput(array(
						'name' => 'status',
						'labelWidth' => '100%',
						'label' => Lang::get('admin.user-filter-status-label'),
						'layout' => 'vertical',
						'value' => $filters['status'],
						'options' => array(
							'0' => Lang::get('admin.user-filter-status-all'),
							'active' => Lang::get('admin.user-filter-status-active'),
							'inactive' => Lang::get('admin.user-filter-status-inactive')
						),
					))					
				)
			)

		));

		return View::make(ThemeManager::getSelected()->getView("box.tpl"), array(
			'content' => $form,
			'title' => Lang::get('admin.user-filter-legend'),
			'icon' => 'filter',
		));
	}
	
}