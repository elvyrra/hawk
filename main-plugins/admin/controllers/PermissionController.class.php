<?php

class PermissionController extends Controller{

	/**
	 * Display the main page of the permission settings
	 */
	public function index(){
		$permissionGroups = Permission::getAllGroupByPlugin();

		$example = isset($this->roleId) ? array('roleId' => $this->roleId) : array();
		$data = RolePermission::getListByExample(new DBExample($example));
		$values = array();
		foreach($data as $value){
			$values[$value->permissionId][$value->roleId] = $value->value;
		}
		$roles = isset($this->roleId) ? array(Role::getById($this->roleId)) : Role::getAll(null, array(), array(), Option::get('main.allow-guest') ? true : false);

		$param = array(
			'id' => 'permissions-form',
			'fieldsets' => array(
				'form' => array(),
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button')
					))
				)
			),			
		);

		foreach($roles as $role){
			foreach($permissionGroups as $group => $permissions){
				foreach($permissions as $permission){
					if($role->id != Role::GUEST_ROLE_ID || $permission->availableForGuests){
						$param['fieldsets']['form'][] = new CheckboxInput(array(
							'name' => "permission-{$permission->id}-{$role->id}",
							'disabled' => $role->id == Role::ADMIN_ROLE_ID,
							'default' => $role->id == Role::ADMIN_ROLE_ID ? 1 : $values[$permission->id][$role->id],
							'class' => $permission->id == Permission::ALL_PRIVILEGES_ID ? 'select-all' : '',
						));
					}
				}
			}
		}
		$form = new Form($param);

		if(!$form->submitted()){
			$page = View::make(Plugin::current()->getView("permissions.tpl"), array(
				'permissions' => $permissionGroups,
				'fields' => $form->fields,
				'roles' => $roles,
			));
			return NoSidebarTab::make(array(
				'icon' => 'unlock-alt',
				'title' => Lang::get('permissions.page-title'),
				'page' => $form->wrap($page)
			));
		}
		else{
			try{
				foreach($form->fields as $name => $field){
					if(preg_match('/^permission\-(\d+)\-(\d+)$/', $name, $match)){
						$permissionId = $match[1];
						$roleId = $match[2];
						$value = isset($_POST[$name]) ? 1 : 0;
						
						if($roleId != Role::ADMIN_ROLE_ID){
							$permission = new RolePermission();
							$permission->set(array(
								'roleId' => $roleId,
								'permissionId' => $permissionId,
								'value' => $value
							));
							$permission->save();
						}
					}
				}
				
				Log::info('Permissions were succesfully updated');
				$form->response(Form::STATUS_SUCCESS, Lang::get("roles.permissions-update-success"));
				
			}
			catch(Exception $e){
				Log::error('An error occured while updating permissions');
				$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get("roles.permissions-update-error"));
			}
		}
	}
}