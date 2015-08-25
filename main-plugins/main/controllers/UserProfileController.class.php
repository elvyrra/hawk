<?php


class UserProfileController extends Controller{

    /**
     * Create or edit an user
     */
    public function edit(){
        if(!$this->userId){
            $user = Session::getUser();
        }         
        else{
            $user = User::getById($this->userId);
        }
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
                        'disabled' => true,
                    )),
                    
                    new EmailInput(array(
                        'name' => 'email',
                        'required' => true,
                        'label' => Lang::get('admin.user-form-email-label'),
                        'disabled' => true,
                    )),
                    
                    new SelectInput(array(
                        'name' => 'roleId',
                        'options' => $roles,
                        'label' => Lang::get('admin.user-form-roleId-label'),
                        'disabled' => true,
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
                    
                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                ),
            ),

            'onsuccess' => 'app.dialog("close")',
        );

        // Get the user profile questions
        $questions = ProfileQuestion::getAll('name', array(), array('order' => DB::SORT_ASC));

        
        

        // Generate the question fields
        foreach($questions as $question){
            $classname = ucwords($question->type) . 'Input';
            $field = json_decode($question->parameters, true);
            $field['name'] = $question->name;
            $field['id'] = 'user-form-' . $question->name. '-input';
            $field['independant'] = true;
            $field['label'] = Lang::get('admin.profile-question-' . $question->name . '-label');
            
            if($user){
                if($question->type == "file"){
                    $field['after'] = "<img src='" . ( $user->getProfileData($question->name) ? $user->getProfileData($question->name) : "") . "' class='profile-image' />";
                }
                else{
                    $field['value'] = $user->getProfileData($question->name);
                }
            }
            
            if($question->name == 'language'){
                // Get language options
                $languages = Language::getAllActive();
                $options = array();
                foreach($languages as $language){
                    $options[$language->tag] = $language->label;
                }
                $field['options'] = $options;
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
            try{
                foreach($questions as $question){
                    if($question->type === 'file'){
                        $upload = Upload::getInstance($question->name);

                        if($upload){
                            $file = $upload->getFile(0);
                            $dir = Plugin::current()->getUserfilesDir()  . 'img/';
                            $url = Plugin::current()->getUserfilesUrl() . 'img/';
                            if(!is_dir($dir)){
                                mkdir($dir, 0755, true);
                            }
                            
                            $upload->move($file, $dir);
                            $user->setProfileData($question->name, $url . $file->basename);
                        }
                    }
                    else{
                        $user->setProfileData($question->name, $form->fields[$question->name]->dbvalue());
                    }
                }            

                $user->saveProfile();
                return $form->response(Form::STATUS_SUCCESS, Lang::get('main.user-profile-update-success'));
            }
            catch(Exception $e){
                return $form->response(Form::STATUS_ERROR, Lang::get('main.user-profile-update-error'));
            }
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
                'icon' => 'lock',
                'page' => $form
            ));
        }
        else{
            if($form->check()){
                $me = Session::getUser();
                if($me->password != Crypto::saltHash($form->getData('current-password'))){
                    return $form->response(Form::STATUS_ERROR, Lang::get('main.update-password-bad-current-password'));
                }
                try{
                    $me->set('password', Crypto::saltHash($form->getData('new-password')));
                    $me->save();

                    return $form->response(Form::STATUS_SUCCESS, Lang::get('main.update-password-success'));
                }
                catch(Exception $e){
                    return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('main.update-password-error'));
                }

            }
        }

    }  


}