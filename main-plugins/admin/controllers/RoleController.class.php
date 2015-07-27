<?php

class RoleController extends Controller{
	/**
	 *  List all the roles
	 */
	public function listRoles(){
		if(!empty($_GET['setdefault'])){
			Option::set('roles.default-role', $_GET['setdefault']);
			$defaultRole = $_GET['setdefault'];
		}
		else{
			$defaultRole = Option::get('roles.default-role');
		}
		
		$param = array(
			'id' => 'roles-list',
			'action' => Router::getUri('list-roles'),			
			'model' => 'Role',
			'controls' => array(
				array(
					'icon' => 'plus',
					'label' => Lang::get('roles.add-role-btn'),
					'href' => Router::getUri('edit-role', array('roleId' => -1)),
					'target' => 'dialog',
					'class' => 'btn-success'
				),
				
				array(
					'icon' => 'unlock-alt',
					'label' => Lang::get('roles.edit-permissions-btn'),
					'href' => Router::getUri('permissions'),
					'target' => 'newtab',
				)
			),
			'fields' => array(
				'removable' => array(
					'field' => 'removable',
					'hidden' => true,
				),
				
				'color' => array(
					'field' => 'color',
					'hidden' => true,
				),				
				
				'actions' => array(
					'independant' => true,
					'display' => function($value, $field, $line){
						return 	"<span class='fa fa-pencil text-info' href='" . Router::getUri('edit-role', array('roleId' => $line->id)) . "' target='dialog'></span>" .
								"<span class='fa fa-unlock-alt text-success' href='" . Router::getUri('role-permissions', array('roleId' => $line->id)). "' target='newtab'></span>" .
								($line->isRemovable() ? "<i class='fa fa-close text-danger delete-role' data-role='{$line->id}'></i>" : "");
					},
					'search' => false,
					'sort' => false,
				),
				
				'name' => array(
					'independant' => true,
					'label' => Lang::get('roles.list-name-label'),
					'display' => function($value, $field, $line){
						return "<span style='color:{$line->color}'>" . Lang::get("roles.role-{$line->id}-label") . "</span>";
					}
				),
				
				'default' => array(
					'independant' => true,
					'label' => Lang::get('roles.list-default-label'),
					'display' => function($value,$field, $line) use($defaultRole){
						if($line->id != 0){
							return "<input type='checkbox' class='set-default-role' value='{$line->id}' " .($defaultRole == $line->id ? "checked disabled" : "") . " />";
						}
					}
				)				
			)
		);
		
		Lang::addKeysToJavaScript("roles.delete-role-confirmation");
		
		return View::make(Plugin::current()->getView("roles-list.tpl"), array(
			'list' => new ItemList($param)
		));
	}
	




	/**
	 * Edit a role 
	 */
	public function edit(){		
		$param = array(
			'id' => 'edit-role-form',			
			'model' => 'Role',
			'reference' => array('id' => $this->roleId),
			'fieldsets' => array(
				'form' => array(
					'nofieldset' => true,
					
					new HiddenInput(array(
						'field' => 'removable',
						'default' => 1,
						'readonly' => true
					)),
					
					new TextInput(array(
						'field' => 'name',
						'maxlength' => 32,
						'label' => Lang::get('roles.form-name-label'),
						'required' => true,
					)),
					
					new ColorInput(array(
						'field' => 'color',
						'label' => Lang::get('roles.form-color-label'),
						'default' => '#000'
					)),
				),
				
				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button'),						
					)),
					
					new DeleteInput(array(
						'name' => 'delete',
						'value' => Lang::get('main.delete-button'),
						'notDisplayed' => $this->roleId == -1
					)),
					
					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
						'onclick' => 'app.dialog("close")'
					)),
				),			
			),			
			'onsuccess' => 'app.dialog("close"); app.load(app.getUri("list-roles"), {selector : "#admin-roles-tab"});'
		);
		

		foreach(Language::getAll() as $language){
			$param['fieldsets']['form'][] = new TextInput(array(
				'name' => "translation[{$language->tag}]",
				"independant" => true,
				'required' => $language->tag == LANGUAGE,
				"label" => Lang::get("roles.role-label-label", array('lang' => $language->tag)),
				"default" => Lang::exists("roles.role-" . $this->roleId . "-label") ? Lang::get("roles.role-" . $this->roleId . "-label", array(), 0, $language->tag) : ''
			));
		}
		
		$form = new Form($param);		
		if(!$form->submitted()){
			return View::make($this->theme->getView("dialogbox.tpl"), array(
				'icon' => 'user',
				'title' => Lang::get('roles.form-title'),
				'page' => $form
			));
		}
		else{			
			if($form->submitted() == "delete"){
				$form->delete(Form::NO_EXIT);
				
				if($key){
					$key->delete();
				}
				$form->response(Form::STATUS_SUCCESS);
			}
			else{
				if($form->check()){
					try{
						$roleId = $form->register(Form::NO_EXIT);

						// Create the language key for the translations of the role name	
						foreach($_POST['translation'] as $tag => $translation){
							Language::getByTag($tag)->saveTranslations(array(
								'roles' => array(
									"role-$roleId-label" => $translation
								)
							));
						}
						
						$form->response(Form::STATUS_SUCCESS);
					}
					catch(Exception $e){
						$form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : "");
					}
				}
			}			
		}

	}
	


	/**
	 * Remove a role 
	 */
	public function remove(){
		$role = Role::getById($this->roleId);
		if($role && $role->isRemovable()){			
			User::getDbInstance()->update(User::getTable(), new DBExample(array('roleId' => $role->id)), array('roleId' => Option::get('roles.default-role')));

			$role->delete();

		}
	}
}
