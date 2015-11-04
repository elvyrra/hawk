<?php
namespace Hawk\Plugins\Admin;

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
		$roles = isset($this->roleId) ? array(Role::getById($this->roleId)) : Role::getAll(null, array(), array(), true);

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
					$param['fieldsets']['form'][] = new CheckboxInput(array(
						'name' => "permission-{$permission->id}-{$role->id}",
						'disabled' => $role->id == Role::ADMIN_ROLE_ID || ($role->id == Role::GUEST_ROLE_ID && !$permission->availableForGuests),
						'default' => $role->id == Role::ADMIN_ROLE_ID ? 1 : (isset($values[$permission->id][$role->id]) ? $values[$permission->id][$role->id] : 0),
						'class' => $permission->id == Permission::ALL_PRIVILEGES_ID ? 'select-all' : '',
						'nl' => false,
					));					
				}
			}
		}
		$form = new Form($param);

		if(!$form->submitted()){
			$page = View::make(Plugin::current()->getView("permissions.tpl"), array(
				'permissions' => $permissionGroups,
				'fields' => $form->inputs,
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
				foreach($form->inputs as $name => $field){
					if(preg_match('/^permission\-(\d+)\-(\d+)$/', $name, $match)){
						$permissionId = $match[1];
						$roleId = $match[2];
						$value = Request::getBody($name) ? 1 : 0;
						
						if($roleId != Role::ADMIN_ROLE_ID && !($roleId == Role::GUEST_ROLE_ID && !$permission->availableForGuests)){
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
				return $form->response(Form::STATUS_SUCCESS, Lang::get("roles.permissions-update-success"));
				
			}
			catch(Exception $e){
				Log::error('An error occured while updating permissions');
				return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get("roles.permissions-update-error"));
			}
		}
	}
}