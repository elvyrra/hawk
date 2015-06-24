<?php

class UserProfileController extends Controller{

    /** 
     * Display the list of the users 
     */
    public function listUsers(){
        $example = array('id' => array('$ne' => 0));
        $filter = (new UserFilterWidget())->getFilter();
        switch($filter){
            case 'inactive' :
                $example['active'] = 0;
                break;

            case 'active' :
                $example['active'] = 1;
                break;
        }
        $users = User::getListByExample(new DBExample($example));
        
        foreach($users as $user){
            $user->color = Role::getById($user->roleId)->color;
            $user->getProfileData();
        }   
        
        $questions = ProfileQuestion::getDisplayProfileQuestions();
        if(Option::get('user.display-realname')){
            unset($questions['realname']);
        }
        unset($questions['avatar']);

        return View::make(Plugin::current()->getView("users-list.tpl"), array(
            'users' => $users,
            'questions' => $questions,
        ));
    }
    



    /**
     * Create or edit an user
     */
    public function edit(){         
        $user = User::getByUsername($this->username);
        $roles = array_map(function($role){ return $role->getLabel(); }, Role::getAll('id'));

        $param = array(
            'id' => 'user-form',
            'upload' => true,           
            'object' => $user,
            'fieldsets' => array(
                'general' => array(
                    'legend' => Lang::get('admin.user-form-general-legend'),
                    
                    new TextInput(array(
                        'name' => 'username',
                        'required' => true,
                        'label' => Lang::get('admin.user-form-username-label'),
                    )),
                    
                    new EmailInput(array(
                        'name' => 'email',
                        'required' => true,
                        'label' => Lang::get('admin.user-form-email-label'),
                    )),
                    
                    new CheckboxInput(array(
                        'name' => 'active',
                        'hidden' => true,
                    )),
                    
                    new CheckboxInput(array(
                        'name' => 'suspended',
                        'hidden' => true,
                    )),
                    
                    new SelectInput(array(
                        'name' => 'roleId',
                        'options' => $roles,
                        'label' => Lang::get('admin.user-form-roleId-label')
                    ))
                ),
                
                'profile' => array(
                    'legend' => Lang::get('admin.user-form-profile-legend'),
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),
                    
                    new DeleteInput(array(
                        'name' => 'delete',
                        'value' => Lang::get('main.delete-button'),
                        'notDisplayed' => !$user,
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                ),
            )
        );

        
        foreach(ProfileQuestion::getAll('name', array(), array('order' => DB::SORT_ASC)) as $question){
            $classname = ucwords($question->type) . 'Input';
            $field = json_decode($question->parameters, true);
            $field['name'] = $question->name;
            $field['id'] = 'user-form-' . $question->name. '-input';
            $field['independant'] = true;
            $field['label'] = Lang::get('admin.profile-question-' . $question->name . '-label');
            
            if($user){
                if($question->type == "file"){
                    $field['after'] = "<img src='" . ( $user->getProfileData($question->name) ? "background: url(" . $user->getProfileData($question->name) . ")" : "") . "' class='profile-image' />";
                }
                else{
                    $field['value'] = $user->getProfileData($question->name);
                }
            }
            $param['fieldsets']['profile'][] = new $classname($field);
        }
        
        $form = new Form($param);
        if(!$form->submitted()){
            return View::make($this->theme->getView("dialogbox.tpl"), array(
                'page' => $form,
                'title' => Lang::get('admin.user-form-title'),
                'icon' => 'user',
            ));
        }
        else{

            
        }
        
    } 

    /**
     * Change the current user password
     */
    public function changePassword(){
        $params = array(
            'id' => 'update-password-form',
            'fieldsets' => array(
                'form' => array(
                    new PasswordInput(array(
                        'name' => 'current-password',
                        'label' => Lang::get('main.update-password-current-password-label'),
                        'required' => true,
                    )),

                    new PasswordInput(array(
                        'name' => 'new-password',
                        'required' => true,
                        'label' => Lang::get('main.update-password-new-password-label'),                        
                    )),

                    new PasswordInput(array(
                        'name' => 'password-confirm',
                        'required' => true,
                        'label' => Lang::get('main.update-password-new-password-confirm-label'),
                        'compare' => 'new-password'
                    ))
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button'),                        
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                ),

            ),
            'onsuccess' => 'app.dialog("close")'
        );

        $form = new Form($params);

        if(!$form->submitted()){
            return View::make($this->theme->getView("dialogbox.tpl"), array(
                'title' => Lang::get('main.update-password-title'),
                'page' => $form
            ));
        }
        else{
            if($form->check()){
                $me = Session::getUser();
                if($me->password != Crypto::saltHash($form->getData('current-password'))){
                    $form->response(Form::STATUS_ERROR, Lang::get('main.update-password-bad-current-password'));
                }
                try{
                    $me->set('password', Crypto::saltHash($form->getData('new-password')));
                    $me->save();

                    $form->response(Form::STATUS_SUCCESS, Lang::get('main.update-password-success'));
                }
                catch(Exception $e){
                    $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('main.update-password-error'));
                }

            }
        }

    }  


}