<?php
/**
 * UserController.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * Users controller
 *
 * @package Plugins\Admin
 */
class UserController extends Controller{


    /**
     * Display the tabs of the users page
     */
    public function index(){
        $tabs = array(
            'users' => $this->listUsers(),
            'roles' => RoleController::getInstance()->listRoles(),
            'questions' => QuestionController::getInstance()->listQuestions()
        );

        $this->addCss($this->getPlugin()->getCssUrl('users.less'));
        $this->addJavaScript($this->getPlugin()->getJsUrl('users.js'));

        $page = View::make($this->getPlugin()->getViewsDir() . 'users.tpl', array(
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
    public function listUsers() {
        $example = array('id' => array('$ne' => User::GUEST_USER_ID));
        $filters = UserFilterWidget::getInstance()->getFilters();

        if(isset($filters['status']) && $filters['status'] != -1) {
            $example['active'] = $filters['status'];
        }

        $param = array(
            'id' => 'admin-users-list',
            'model' => 'User',
            'action' => App::router()->getUri('list-users'),
            'reference' => 'id',
            'filter' => new DBExample($example),
            'controls' => array(
                array(
                    'icon' => 'plus',
                    'label' => Lang::get($this->_plugin . '.new-user-btn'),
                    'class' => 'btn-success',
                    'href' => App::router()->getUri("edit-user", array('username' => '_new')),
                    'target' => 'dialog',
                ),
            ),
            'fields' => array(
                'actions' => array(
                    'independant' => true,
                    'display' => function ($value, $field, $user) {
                        $return = Icon::make(array(
                            'icon' => 'pencil',
                            'class' => 'text-primary',
                            'href' => App::router()->getUri('edit-user', array('username' => $user->username)),
                            'target' => 'dialog'
                        ));
                        if($user->isRemovable()) {
                            $return .= Icon::make(array(
                                'icon' => 'close',
                                'class' => 'text-danger delete-user',
                                'data-user' => $user->username
                            ));

                            $return .= $user->active ?
                                Icon::make(array(
                                    'icon' => 'lock',
                                    'class' => 'text-warning lock-user',
                                    'data-user' => $user->username
                                )) :

                                Icon::make(array(
                                    'icon' => 'unlock',
                                    'class' => 'text-success unlock-user',
                                    'data-user' => $user->username
                                ));
                        }

                        return $return;
                    },
                    'search' => false,
                    'sort' => false,
                ),
                'username' => array(
                    'label' => Lang::get($this->_plugin . '.users-list-username-label'),
                ),

                'email' => array(
                    'label' => Lang::get($this->_plugin . '.users-list-email-label'),
                ),

                'roleId' => array(
                    'label' => Lang::get($this->_plugin . '.users-list-roleId-label'),
                    'sort' => false,
                    'search' => array(
                        'type' => 'select',
                        'options' => call_user_func(function () {
                            $options = array();
                            foreach(Role::getAll('id') as $id => $role){
                                $options[$id] = $role->getLabel();
                            }
                            return $options;
                        }),
                        'invitation' => Lang::get($this->_plugin . '.user-filter-status-all')
                    ),
                    'display' => function ($value) {
                        return Role::getById($value)->getLabel();
                    }
                ),

                'active' => array(
                    'label' => Lang::get($this->_plugin . '.users-list-active-label'),
                    'search' => false,
                    'sort' => false,
                    'class' => function ($value) {
                        return 'bold ' . ($value ? 'text-success' : 'text-danger');
                    },
                    'display' => function ($value) {
                        return $value ? Lang::get($this->_plugin . '.users-list-active') : Lang::get($this->_plugin . '.users-list-inactive');
                    }
                ),

                'createTime' => array(
                    'label' => Lang::get($this->_plugin . '.users-list-createTime-label'),
                    'search' => false,
                    'display' => function ($value) {
                        return date(Lang::get('main.date-format'), $value);
                    },
                ),
            )
        );

        $list = new ItemList($param);

        if(App::request()->getParams('refresh')) {
            return $list->display();
        }
        else{
            $this->addKeysToJavaScript("admin.user-delete-confirmation");
            return View::make($this->getPlugin()->getView("users-list.tpl"), array(
                'list' => $list,
            ));
        }

    }




    /**
     * Create / Edit / Delete an user
     */
    public function edit() {
        $roles = array_map(function ($role) {
            return $role->getLabel();
        }, Role::getAll('id'));
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
                        'readonly' => $user && $user->id !== App::session()->getUser()->id,
                        'insert' => ! $user || $user->id === App::session()->getUser()->id,
                        'label' => Lang::get($this->_plugin . '.user-form-username-label'),
                        'default' => uniqid()
                    )),

                    new EmailInput(array(
                        'name' => 'email',
                        'required' => true,
                        'unique' => true,
                        'readonly' => $user && $user->id !== App::session()->getUser()->id,
                        'insert' => ! $user || $user->id !== App::session()->getUser()->id,
                        'label' => Lang::get($this->_plugin . '.user-form-email-label'),
                    )),

                    new CheckboxInput(array(
                        'name' => 'active',
                        'label' => Lang::get($this->_plugin . '.user-form-active-label'),
                        'default' => 0,
                        'notDisplayed' => !$user
                    )),

                    new SelectInput(array(
                        'name' => 'roleId',
                        'options' => $roles,
                        'label' => Lang::get($this->_plugin . '.user-form-roleId-label')
                    )),

                    new HiddenInput(array(
                        'name' => 'createTime',
                        'default' => time(),
                        'independant' => $user,
                        'notDisplayed' => $user
                    )),

                    new HiddenInput(array(
                        'name' => 'createIp',
                        'default' => App::request()->clientIp(),
                        'independant' => $user,
                        'notDisplayed' => $user
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

        if(!$form->submitted()) {
            return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
                'page' => $form,
                'title' => Lang::get($this->_plugin . '.user-form-title'),
                'icon' => 'user',
            ));
        }
        else{
            if($form->submitted() === 'delete') {
                $this->remove();
            }
            else {
                if($form->check()) {
                    if(!$user) {
                        // Creating a new user
                        $form->register(false);
                        $user = $form->object;

                        // Send an email to the new user to validate the registration
                        $tokenData = array(
                            'username' => $user->username,
                            'email' => $user->email,
                            'createTime' => $user->createTime,
                            'createIp' => $user->createIp
                        );
                        $token = Crypto::aes256Encode(json_encode($tokenData));
                        $url = App::router()->getUrl('validate-third-registration', array('token' => $token));


                        $data = array(
                            'themeBaseCss' => Theme::getSelected()->getBaseCssUrl(),
                            'themeCustomCss' => Theme::getSelected()->getCustomCssUrl(),
                            'logoUrl' =>  Option::get('main.logo') ?
                                Plugin::get('main')->getUserfilesUrl(Option::get('main.logo')) :
                                Plugin::get('main')->getStaticUrl('img/hawk-logo.png'),
                            'sitename' => Option::get('main.sitename'),
                            'url' => $url
                        );

                        $mailContent = View::make(Plugin::get('main')->getView('admin-registration-validation-email.tpl'), $data);

                        $mail = new Mail();
                        $mail->from(Option::get('main.mailer-from'))
                            ->fromName(Option::get('main.mailer-from-name'))
                            ->to($user->email)
                            ->title(Lang::get('main.register-email-title', array('sitename' => Option::get('main.sitename'))))
                            ->content($mailContent)
                            ->subject(Lang::get('main.register-email-title', array('sitename' => Option::get('main.sitename'))))
                            ->send();

                        return $form->response(Form::STATUS_SUCCESS, Lang::get('main.register-send-email-success'));
                    }
                    else {
                        return $form->register();
                    }
                }
            }
        }
    }

    /**
     * Remove a user
     */
    public function remove(){
        $user = User::getByUsername($this->username);
        if($user && $user->isRemovable()) {
            $user->delete();
        }
    }

    /**
     * Activate / Deactivate a user
     */
    public function activate(){
        $user = User::getByUsername($this->username);
        if($user && $user->isRemovable()) {
            $user->set("active", $this->value);
            $user->save();
        }
    }
}
