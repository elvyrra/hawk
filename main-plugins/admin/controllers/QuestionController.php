<?php
namespace Hawk\Plugins\Admin;

class QuestionController extends Controller{
	public function listQuestions(){
		$questions = ProfileQuestion::getAll();
		$param = array(
			'id' => 'display-questions-form',
			'action' => App::router()->getUri('profile-questions'),
			'fieldsets' => array(
				'form' => array(),

				'_submits' => array(
					new SubmitInput(array(
						'name' => 'valid',
						'value' => Lang::get('main.valid-button'),
					)),

					new ButtonInput(array(
						'name' => 'new-question',
						'value' => Lang::get($this->_plugin . '.new-question-btn'),
						'class' => 'btn-success',
						'href' => App::router()->getUri('edit-profile-question', array('name' => '_new')),
						'target' => 'dialog',
						'icon' => 'plus'
					))
				)
			)
		);

		foreach($questions as $question){
			// Add the input to display in register form
			$param['fieldsets']['form'][] = new CheckboxInput(array(
				'name' => "register-display-$question->name",
				'default' => $question->displayInRegister,
				'nl' => false
			));

			// Add the input to display in the user profile
			$param['fieldsets']['form'][] = new CheckboxInput(array(
				'name' => "profile-display-$question->name",
				'default' => $question->displayInProfile,
				'nl' => false
			));
		}

		$form = new Form($param);

		$list = new ItemList(array(
			'id' => 'profile-questions-list',
			'model' => 'ProfileQuestion',
			'lines' => 'all',
			'navigation' => false,
			'sort' => array('order' => DB::SORT_ASC),
			'fields' => array(
				'name' => array(
					'hidden' => true
				),

				'editable' => array(
					'hidden' => true
				),

				'actions' => array(
					'independant' => true,
					'display' => function($value, $field, $line){
						return 	$line->editable ? "<i class='icon icon-pencil text-info' href='" . App::router()->getUri('edit-profile-question', array('name' => $line->name)) . "' target='dialog' title='". Lang::get($this->_plugin . 'edit-profile-question')."' ></i>".
												 "<i class='icon icon-times text-danger delete-question' data-question='$line->name' title='" . Lang::get($this->_plugin . 'delete-profile-question') . "'></i>" : "";
					},
					'sort' => false,
					'search' => false,
				),

				'label' => array(
					'independant' => true,
					'display' => function($value, $field, $line){
						return Lang::get($this->_plugin . ".profile-question-$line->name-label") . " ( $line->name )";
					},
					'sort' => false,
					'search' => false,
				),

				'displayInRegister'=> array(
					'label' => Lang::get($this->_plugin . ".list-questions-register-visible-label"),
					'sort' => false,
					'search' => false,
					'display' => function($value, $field, $line) use($form){
						return $form->inputs["register-display-$line->name"];
					}
				),

				'displayInProfile' => array(
					'label' => Lang::get($this->_plugin . '.list-questions-profile-visible-label'),
					'sort' => false,
					'search' => false,
					'display' => function($value, $field, $line) use($form){
						return $form->inputs["profile-display-$line->name"];
					}

				)
			),
		));

		if(!$form->submitted()){
			$this->addKeysToJavaScript($this->_plugin . ".confirm-delete-question");
			$content = View::make(Plugin::current()->getView("questions-list.tpl"), array(
				'list' => $list,
				'form' => $form
			));

			return $form->wrap($content);
		}
		else{
			try{
				$save = array();
				foreach($form->inputs as $name => $field){
					if(preg_match("/^(register|profile)\-display\-(\w+)$/", $name, $match)){
						$qname = $match[2];
						$func = $match[1] == "register" ? 'displayInRegister' : 'displayInProfile';
						if(!isset($save[$qname])){
							$save[$qname] = new ProfileQuestion();
							$save[$qname]->set('name', $qname);
						}
						$save[$qname]->set($func, (int) App::request()->getBody($name) );
					}
				}

				foreach($save as $question){
					$question->update();
				}

				return $form->response(Form::STATUS_SUCCESS);
			}
			catch(Exception $e){
				return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : '');
			}
		}
	}


	public function edit(){
		$q = ProfileQuestion::getByName($this->name);
		if(!$q || $q->editable){

			$allowedTypes = ProfileQuestion::$allowedTypes;
			$param = array(
				'id' => 'profile-question-form',
				'model' => 'ProfileQuestion',
				'reference' => array('name' => $this->name),
				'labelWidth' => '200px',
				'fieldsets' => array(
					'general' => array(
						'legend' => Lang::get($this->_plugin . '.profile-question-form-general-legend'),

						new TextInput(array(
							'name' => 'name',
							'unique' => true,
							'maxlength' => 32,
							'label' => Lang::get($this->_plugin . '.profile-question-form-name-label') . ' ' . Lang::get($this->_plugin . '.profile-question-form-name-description'),
							'required' => true,
						)),

						new HiddenInput(array(
							'name' => 'editable',
							'default' => 1,
						)),

						new SelectInput(array(
							'name' => 'type',
							'required' => true,
							'options' => array_combine($allowedTypes, array_map(function($type){
								return Lang::get($this->_plugin . '.profile-question-form-type-' . $type);
							}, $allowedTypes)),
							'label' => Lang::get($this->_plugin . '.profile-question-form-type-label'),
							'attributes' => array(
								'ko-value' => 'type',
							)
						)),

						new CheckboxInput(array(
							'name' => 'displayInRegister',
							'label' => Lang::get($this->_plugin . '.profile-question-form-displayInRegister-label')
						)),

						new CheckboxInput(array(
							'name' => 'displayInProfile',
							'label' => Lang::get($this->_plugin . '.profile-question-form-displayInProfile-label')
						))
					),

					'parameters' => array(
						'legend' => Lang::get($this->_plugin . '.profile-question-form-parameters-legend'),

						new ObjectInput(array(
							'name' => 'parameters',
							'id' => 'question-form-parameters',
							'hidden' => true,
							'attributes' => array(
								'ko-value' => 'parameters'
							)
						)),

						new CheckboxInput(array(
							'name' => 'required',
							'independant' => true,
							'label' => Lang::get($this->_plugin . '.profile-question-form-required-label'),
							'attributes' => array(
								'ko-checked' => "required",
							)
						)),

						new DatetimeInput(array(
							'name' => 'minDate',
							'independant' => true,
							'label' => Lang::get($this->_plugin . '.profile-question-form-minDate-label'),
							'attributes' => array(
								'ko-value' => "minDate"
							),
						)),

						new DatetimeInput(array(
							'name' => 'maxDate',
							'independant' => true,
							'label' => Lang::get($this->_plugin . '.profile-question-form-maxDate-label'),
							'attributes' => array(
								'ko-value' => "maxDate"
							),
						)),

						new HtmlInput(array(
							'name' => 'parameters-description',
							'value' => "<p class='alert alert-info'><i class='icon icon-exclamation-circle'></i>" . Lang::get($this->_plugin . '.profile-question-form-translation-description') . "</p>"
						)),

						new TextInput(array(
							'name' => 'label',
							'required' => true,
							'independant' => true,
							'label' => Lang::get($this->_plugin . '.profile-question-form-label-label'),
							'default' => $this->name != '_new' ? Lang::get($this->_plugin . '.profile-question-' . $this->name . '-label') : ''
						)),

						new TextareaInput(array(
							'name' => 'options',
							'independant' => true,
							'required' => App::request()->getBody('type') == 'select' || App::request()->getBody('type') == 'radio',
							'label' => Lang::get($this->_plugin . '.profile-question-form-options-label') . '<br />' . Lang::get($this->_plugin . '.profile-question-form-options-description'),
							'labelClass' => 'required',
							'attributes' => array(
								'ko-value' => "options",
							),
							'cols' => 20,
							'rows' => 10
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
							'notDisplayed' => $this->name == '_new'
						)),

						new ButtonInput(array(
							'name' => 'cancel',
							'value' => Lang::get('main.cancel-button'),
							'onclick' => 'app.dialog("close")'
						))
					)

				),
				'onsuccess' => 'app.dialog("close"); app.load(app.getUri("profile-questions"), {selector : "#admin-questions-tab"})',
			);

			$form = new Form($param);

			if(!$form->submitted()){
				$content = View::make(Plugin::current()->getView("question-form.tpl"), array(
					'form' => $form
				));

				return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
					'title' => Lang::get($this->_plugin . ".users-questions-title"),
					'icon' => 'file-word-o',
					'page' => $content
				));
			}
			else{
				if($form->submitted() == "delete"){
					$this->compute('delete');

					return $form->response(Form::STATUS_SUCCESS);
				}
				else{
					if($form->check()){
						$form->register(Form::NO_EXIT);

						Language::current()->saveTranslations(array(
							'admin' => array(
								'profile-question-' . $form->getData("name") . '-label' => App::request()->getBody('label')
							)
						));

						// Create the lang options
						if($form->inputs['options']->required){
							$keys = array('admin'=> array());
							foreach(explode(PHP_EOL, $form->getData("options")) as $i => $option){
								if(!empty($option)){
									$keys['admin']['profile-question-' . $form->getData("name") . '-option-' . $i] = trim($option);
								}
							}
							Language::current()->saveTranslations($keys);
						}

						return $form->response(Form::STATUS_SUCCESS);
					}
				}
			}
		}
		else{
			return '';
		}
	}

	public function delete(){
		$question = ProfileQuestion::getByName($this->name);

		if($question->editable){
			$params = json_decode($question->parameters, true);

			$question->delete();

			// Remove the language keys for the label and the options
			$keysToRemove = array(
				'admin' => array(
					'profile-question-' . $this->name . '-label',
				)
			);

			if(!empty($params['options'])){
				foreach($params['options'] as $i => $value){
					$keysToRemove['admin'][] = 'profile-question-' . $this->name . '-option-' . $i;
				}
			}
			foreach(Language::getAll() as $language){
				$language->removeTranslations($keysToRemove);
			}
		}
	}

}