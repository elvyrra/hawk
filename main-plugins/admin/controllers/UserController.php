<?php
namespace Hawk\Plugins\Admin;

class UserController extends Controller{
	

	/**
	 * Display the tabs of the users page 
	 */
	public function index(){
		$tabs = array(
			'users' => $this->compute('listUsers'),
			'roles' => RoleController::getInstance()->compute('listRoles'),
			'questions' => QuestionController::getInstance()->compute('listQuestions')
		);

		$this->addCss(Plugin::current()->getCssUrl('users.less') );
		$this->addJavaScript(Plugin::current()->getJsUrl('users.js'));
		
		$page = View::make(Plugin::current()->getViewsDir() . 'users.tpl', array(
			'tabs' => $tabs,
		));
		
		return NoSidebarTab::make(array(
			'page' => $page,
			'icon' => 'users',
			'title' => 'Utilisateurs'			
		));
	}
	



	/** 
	 * Display the list of the users 
	 */
	public function listUsers(){
		$example = array('id' => array('$ne' => User::GUEST_USER_ID));
		$filters = (new UserFilterWidget())->getFilters();
		switch($filters['status']){
			case 'inactive' :
				$example['active'] = 0;
				break;

			case 'active' :
				$example['active'] = 1;
				break;
		}
		if(!empty($filters['roleId'])){
			$example['roleId'] = $filters['roleId'];
		}

		$param = array(
			'id' => 'admin-users-list',
			'model' => 'User',
			'action' => Router::getUri('list-users'),
			'reference' => 'id',
			'filter' => new DBExample($example),			
			'controls' => array(
				array(
					'icon' => 'plus',
					'label' => Lang::get('admin.new-user-btn'),
					'class' => 'btn-success',
					'href' => Router::getUri("edit-user", array('username' => '_new')),
					'target' => 'dialog',
				),
			),
			'fields' => array(
				'actions' => array(
					'independant' => true,
					'display' => function($value, $field, $user){
						$return = "<i class='icon icon-pencil text-primary' href='". Router::getUri('edit-user', array('username' => $user->username)) . "' target='dialog'></i>";
						if($user->isRemovable()){
							$return .= "<i class='icon icon-close text-danger delete-user' data-user='{$user->username}'></i>";

							$return .= $user->active ? 
									"<i class='icon icon-lock text-warning lock-user' data-user='{$user->username}'></i>":
									"<i class='icon icon-unlock text-success unlock-user' data-user='{$user->username}'></i>";
						}

						return $return;
					},
					'search' => false,
					'sort' => false,
				),
				'username' => array(
					'label' => Lang::get('admin.users-list-username-label'),					
				),

				'email' => array(
					'label' => Lang::get('admin.users-list-email-label'),
				),

				'roleId' => array(
					'label' => Lang::get('admin.users-list-roleId-label'),
					'sort' => false,
					'search' => array(
						'type' => 'select',
						'options' => call_user_func(function(){
							$options = array();
							foreach(Role::getAll('id', array('id')) as $id => $role){
								$options[$id] = Lang::get('roles.role-' . $id . '-label');
							}
							return $options;
						}),
						'invitation' => Lang::get('admin.user-filter-status-all')
					),
					'display' => function($value){
						return Lang::get('roles.role-' . $value . '-label');
					}
				),

				'active' => array(
					'label' => Lang::get('admin.users-list-active-label'),
					'search' => false,
					'sort' => false,
					'class' => function($value){
						return 'bold ' . ($value ? 'text-success' : 'text-danger');
					},
					'display' => function($value){
						return $value ? Lang::get('admin.users-list-active') : Lang::get('admin.users-list-inactive');
					},
				),

				'createTime' => array(	
					'label' => Lang::get('admin.users-list-createTime-label'),
					'search' => false,
					'display' => function($value){
						return date(Lang::get('main.date-format'), $value);
					},
				),
			)
		);
		
		$list = new ItemList($param);

		if(Request::getParams('refresh')) {
			return $list->__toString();	
		}
		else{
			Lang::addKeysToJavaScript("admin.user-delete-confirmation");
			return View::make(Plugin::current()->getView("users-list.tpl"), array(
				'list' => $list,
			));
		}
		
	}
	



	/**
	 * Create or edit an user
	 */
	public function edit(){

		$roles = array_map(function($role){ return $role->getLabel(); }, Role::getAll('id'));
		$user = User::getByUsername($this->username);

		$param = array(
			'id' => 'user-form',
			'upload' => true,			
			'model' => 'User',
			'reference' => array('username' => $this->username),
			'fieldsets' => array(
				'general' => array(
					'nofieldset' => true,
					
					new TextInput(array(
						'name' => 'username',
						'required' => true,
						'unique' => true,
						'readonly' => $user && $user->id !== Session::getUser()->id,
						'insert' => ! $user || $user->id === Session::getUser()->id,
						'label' => Lang::get('admin.user-form-username-label'),
					)),
					
					new EmailInput(array(
						'name' => 'email',
						'required' => true,
						'unique' => true,
						'readonly' => $user && $user->id !== Session::getUser()->id,
						'insert' => ! $user || $user->id !== Session::getUser()->id,
						'label' => Lang::get('admin.user-form-email-label'),
					)),
					
					new CheckboxInput(array(
						'name' => 'active',
						'label' => Lang::get('admin.user-form-active-label'),
					)),
					
					new SelectInput(array(
						'name' => 'roleId',
						'options' => $roles,
						'label' => Lang::get('admin.user-form-roleId-label')
					)),

					$user ? null : 
					new PasswordInput(array(
						'name' => 'password',
						'required' => true,
						'label' => Lang::get('admin.user-form-password-label'),
						'encrypt' => array('Hawk\\Crypto', 'saltHash')
					)),

					$user ? null : 
					new PasswordInput(array(
						'name' => 'passagain',
						'label' => Lang::get('admin.user-form-passagain-label'),
						'required' => true,
						'compare' => 'password',
						'independant' => true,
					)),

					new HiddenInput(array(
						'name' => 'createTime',
						'default' => time(),
					))
				),			
				

				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button')
					)),
					
					new DeleteInput(array(
						'name' => 'delete',
						'value' => Lang::get('main.delete-button'),
						'notDisplayed' => !($user && $user->isRemovable()),
					)),

					new ButtonInput(array(
						'name' => 'cancel',
						'value' => Lang::get('main.cancel-button'),
						'onclick' => 'app.dialog("close")'
					))
				),
			),
			'onsuccess' => 'app.dialog("close"); app.lists["admin-users-list"].refresh();'
		);

		$form = new Form($param);
		
		if(!$form->submitted()){
			return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
				'page' => $form,
				'title' => Lang::get('admin.user-form-title'),
				'icon' => 'user',
			));
		}
		else{
			if($form->submitted() == "delete"){
				$this->compute('remove');
			}
			else{
				if($form->check()){
					return $form->register();
				}
			}
		}
		
	}	

	public function remove(){
		$user = User::getByUsername($this->username);
		if($user && $user->isRemovable()){
			$user->delete();
		}
	}	


	public function activate(){
		$user = User::getByUsername($this->username);
		if($user && $user->isRemovable()){
			$user->set("active", $this->value);
			$user->save();
		}
	}
}
