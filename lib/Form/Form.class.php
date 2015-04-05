<?php
/**********************************************************************
 *    						Form.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
class Form{
    const DEFAULT_TYPE = "text";
	const NO_CHECK = false;
	const CHECK = true;
	const GET_FROM_DATABASE = true;
	
	const NO_EXIT = false;
	const EXIT_JSON = true;
	    
    const VIEWS_DIR = 'form/';
	
	const STATUS_SUCCESS = 'success';
	const STATUS_ERROR = 'error';
	const STATUS_CHECK_ERROR = 'check-error';
	
	const HTTP_CODE_SUCCESS = 200;
	const HTTP_CODE_CHECK_ERROR = 412;
	const HTTP_CODE_ERROR = 424;
	
	const ACTION_REGISTER = 'register';
	const ACTION_DELETE = 'delete';

	const DEFAULT_MODEL = "GenericModel";
	
	// Default properties values
	public	$type = 'json',
			$method = 'post',
			$model = self::DEFAULT_MODEL,
			$labelWidth = '150px',
			$status = null,
			$columns = 1,
			$ajax = true,
			$binds = array(),
			$returns = array(),
			$errors = array(),
			$class = '',			
			$target = '',
			$autocomplete = true,
			$title = '',
			$enctype = '',
			$fields = array(),
			$dbaction = self::ACTION_REGISTER;
	private $example = null;
	
	public function __construct($param){
		/*
		 * Default values
		 */
		$this->action = $_SERVER['REQUEST_URI'];		
				
        Lang::load('form', Plugin::get('main')->getLangDir().'form');	
		
		// Get the parameters of the instance
        foreach($param as $key => $value){
            $this->$key = $value;
        }
		
		if(isset($this->model) && isset($this->reference)){
			$model = $this->model;				
			$this->example = new DBExample($this->reference);
			$this->object = $model::getByExample($this->example);				
		}
		else{
			$this->object = null;
		}		

		$this->new = $this->object === null;
		

		// Get the fields in the "fields" instance property, and add the default values for the fieldsets and fields
		$this->groups = $this->fieldsets;
		$this->fieldsets = array();
        if(!empty($this->groups)){
	        foreach($this->groups as $name => &$fieldset){
	            $inputs = array();
	            $params = array();
	            foreach($fieldset as $key => &$field){
					if($field instanceof FormInput){									
						if(defined(get_class($field).'::INDEPENDANT')){
							// This field is independant from the database
							$field->independant = true;
						}
						
						if(defined(get_class($field).'::NO_LABEL')){                    
							// This field cannot have any label
							unset($field->label);
						}
						
						$labelWidth = $this->labelWidth;
						if(isset($fieldset['labelWidth'])){
							$labelWidth = $fieldset['labelWidth'];
						}
						if(isset($field->labelWidth)){
							$labelWidth = $field->labelWidth;
						}
						$field->labelWidth = $labelWidth;
						
						if($name == '_submits' && !isset($field->nl)){                   
							// A field in this fieldset cannot return to the new line
							$field->nl = false;
						}
							
						$this->fields[$field->name] = &$field;					
						$inputs[$field->name] = &$field;
					}
					else{
						$params[$key] = $field;
					}
	            }
	            $this->fieldsets[$name] = new FormFieldset($this, $name, $inputs, $params);
	        }
        }
		// get the data of the form to display or register
        $this->reload();		
	}
	
	public function getFromInput(){		
		$input = file_get_contents("php://input");
		if(!empty($input)){
			preg_match("/^(\-{6}\w+)/i", $input, $m);
			$boundary = $m[1];
			$input = str_replace($boundary . '--', '', $input);
			$data = preg_split('/'.$boundary.'\r?\n/is', $input, -1, PREG_SPLIT_NO_EMPTY);
			$_POST = array();
			$_FILES = array();
			foreach($data as $field){			
				if(preg_match('/^Content\-Disposition\: form\-data; name="(.+?)"; filename="(.+)"\r?\nContent\-Type: (.+?)\r?\n\r?\n(.*?)\r?\n$/is', $field, $match)){								
					$name = $match[1];
					$filename = $match[2];
					$mime = $match[3];
					$content = $match[4];
					$tmpname = uniqid('/tmp/');
					file_put_contents($tmpname, $content);
					if(preg_match('/^(\w+)\[(.*)\]$/', $name, $m)){
						if(!isset($_FILES[$m[1]]))
							$_FILES[$m[1]] = array();
						if(empty($m[2])){
							$_FILES[$m[1]]['name'][] = $filename;
							$_FILES[$m[1]]['type'][] = $mime;
							$_FILES[$m[1]]['tmp_name'][] = $tmpname;
							$_FILES[$m[1]]['error'][] = UPLOAD_ERR_OK;
							$_FILES[$m[1]]['size'][] = filesize($tmpname);
						}
						else{
							$_FILES[$m[1]]['name'][$m[2]] = $filename;
							$_FILES[$m[1]]['type'][$m[2]] = $mime;
							$_FILES[$m[1]]['tmp_name'][$m[2]] = $tmpname;
							$_FILES[$m[1]]['error'][$m[2]] = UPLOAD_ERR_OK;
							$_FILES[$m[1]]['size'][$m[2]] = filesize($tmpname);
						}
					}
					else{
						$_FILES[$name]['name'] = $filename;
						$_FILES[$name]['type'] = $mime;
						$_FILES[$name]['tmp_name'] = $tmpname;
						$_FILES[$name]['error'] = UPLOAD_ERR_OK;
						$_FILES[$name]['size'] = filesize($tmpname);
					}
				}
				elseif(preg_match('/^Content\-Disposition\: form\-data; name="(.+?)"\r?\n\r?\n(.*?)\r?\n$/is', $field, $match)){				
					$name = $match[1];
					$value = $match[2];				
					if(preg_match('/^(\w+)\[(.*)\]$/', $name, $m)){
						if(!isset($_POST[$m[1]]))
							$_POST[$m[1]] = array();
						if(empty($m[2])){
							$_POST[$m[1]][] = $value;
						}
						else{
							$_POST[$m[1]][$m[2]] = $value;
						}
					}
					else{
						$_POST[$name] = $value;
					}
				}
			}
		}
	}
	
	/*
	 * Prototype: public function reload()
	 * Description: Reload the data of the form
	 */
	public function reload($database = false, $data = array()){
        if($this->upload){
			$this->getFromInput();
		}
		
		// Set default value
		$data = array();
		foreach($this->fields as $name => $field){					
			if(isset($field->default)){
				$data[$name] = $field->default;
			}

			if(!$this->submitted() && isset($this->object->$name)){
				$data[$name] = $this->object->$name;
			}			
		}

		if($this->submitted()){
			$entry = $this->method == 'get' ? $_GET : $_POST;
			foreach($entry as $name => $value){
				$data[$name] = $value;				
			}
		}

		// Set the value in all inputs instances		
		$this->set($data);		
	}
	
	/*
	 * Prototype: public function set($field, $value)
	 * Description: Force the value of a field in the form
	 */
	public function set($data, $prefix = ''){
		foreach($data as $key => $value){
			$field = $prefix ? $prefix."[$key]" : $key;
			if(isset($this->fields[$field])){
				$this->fields[$field]->set($value);				
			}
			elseif(is_array($value)){
				$this->set($value, $field);
			}
			
		}
	}
	
	public function getData($name = null){
		if($name){
			return $this->fields[$name]->value;
		}
		else{
			$result = array();
			foreach($this->fields as $name => $field){
				$result[$name] = $field->value;
			}
			
			return $result;
		}
	}
	
	/*
	 * Prototype: public function addInput($fieldset, $input, $after = 'last')
	 * Description: Add a new input in the form
	 */
	public function addInput($fieldset, $input){
		$labelWidth = $this->labelWidth;
		if(isset($this->fieldsets[$fieldset]->labelWidth)){
			$labelWidth = $this->fieldsets[$fieldset]->labelWidth;
		}
		if(isset($input->labelWidth)){
			$labelWidth = $input->labelWidth;
		}
		$input->labelWidth = $labelWidth;

		$this->fieldsets[$fieldset]->inputs[$input->name] = $input;		
		$this->fields[$input->name] = &$input;
	}
	
	
	/*
     * Prototype : public function submitted()
     * Description : Détermine si le formulaire a été soumis ou non
     */
    public function submitted(){
    	if(Request::method() == "delete"){
    		return self::ACTION_DELETE;
    	}
        $entry = $this->method == 'get' ? $_GET : $_POST;
		return isset($entry['_FORM_ACTION_']) ? $entry['_FORM_ACTION_'] : null;        
    }  
	
	/*
	 * Prototype: public function wrap($content)
	 * Description : wrap the content of the form with the form tag and return the string result
	 * @param : string $content, the content of the form
	 * @return : string
	 */
	public function wrap($content){
		return View::make(Plugin::get('main')->getView('form/form.tpl'), array(
			'form' => $this,
			'content' => $content
		));
	}
	
	/*
	 * Prototype public function display($template)
	 * Description : Display the form
	 * @param : $template(optionnal), the template to display the form
	 * @return : string
	 */
	public function display(){
		// $fieldsets = array();		
		// foreach($this->fieldsets as $name => $fieldset){
		// 	$fieldsets[$name] = $this->displayFieldset($name);
		// }		
		
		$content = View::make(Plugin::get('main')->getView('form/form-content.tpl') , array(
			'form' => $this,
			'fields' => $fields,
			'fieldsets' => $this->fieldsets,
			'column' => 0		
		));
		return $this->wrap($content);
	}
	/*
	 * Prototype : public function __toString()
	 * Description : Overload lagic method __toString
	 */
	public function __toString(){
		return $this->display();
	}
	
	/*
	 * Display a fieldset
	 * Prototype : public function displayFieldset($blockname)
	 * @param: string $blockname, the name of the fieldset
	 */
	public function displayFieldset($fieldset){
		$fields = array();
		
		foreach($this->fieldsets[$fieldset] as &$field){
			if($field instanceof FormInput){
				$fields[] = &$field;
			}
		}
		if(!empty($fields)){
			$fields[0]->first = true;
			$fields[count($fields) - 1]->last = true;
		}
		
        return View::make(Plugin::get('main')->getView('form/form-fieldset.tpl'), array(
            'form' => $this,
            'fieldset' => $this->fieldsets[$fieldset],
            'name' => $fieldset,
			'fields' => $fields
        ));
    }
	
	/*
     * Prototype : public function displayInput($fieldset, $field)
     * Description : Display a field and it label
     * @param : 
     */
    public function displayInput($field){
		return $this->fields[$field];		
    }
	
	/*
	 * Prototype: public function check($exit = self::JSON)
	 * Description : Check the data submitted by the form
	 * @param : $exit, if true and errors are catched, the script willl end
	 */
	public function check($exit = self::EXIT_JSON){				
		if(empty($this->errors))			
			$this->errors = array();
			
		foreach($this->fields as $name => $field){					
			$field->check($this);
		}	
		
		if(!empty($this->errors)){
			$this->status = self::STATUS_ERROR;
			if($exit){
				/*** The form check failed ***/
				$this->response(self::STATUS_CHECK_ERROR, Lang::get('form.error-fill'));
			}
			else{
				$this->addReturn('message', Lang::get('form.error-fill'));
				return false;
			}
		}
		
		/*** The form check return OK status ***/
		return true;
	}
	
	/*
	 * Prototype : public function resgister($exit = self::JSON)
	 * Register the data into the database for new elements or elemnt upation
	 * @param : $exit, if true, exit the script when finished
	 */
	public function register($exit = self::EXIT_JSON, $success = "", $error = ""){			
		try{
			$this->dbaction = self::ACTION_REGISTER;
			
			
			if($this->model == self::DEFAULT_MODEL || !$this->reference){
				throw new Exception("The method register of the class Form can be called only if model and reference properties are set");
			}
			if(!$this->object){
				$model = $this->model; 
				$this->object = new $model();
			}
			$this->object->set($this->reference);
						
			foreach($this->fields as $name => $field){								
				/* Determine if we have to insert this field in the set of inserted values
				 * A field can't be inserted if :
				 * 	it type is in the independant types
				 * 	the field is defined as independant
				 * 	the fiels is defined as no insert			
				 */			
				if(!$field->independant && $field->insert !== false && !$field->disabled){
					/*** Insert the field value in the set ***/						
					$this->object->set($name, $field->dbvalue());
				}									
			}

						
			if(!$this->new){							
				$this->object->update();
			}
			else{
				$this->object->save();
			}
			
			
			$id = $this->object->getPrimaryColumn();
			
			$this->addReturn(array(
				'primary' => $this->object->$id,
				'action' => self::ACTION_REGISTER,
				'new' => $this->new
			));	
			$this->status = self::STATUS_SUCCESS;
			
			if($exit){
				// output the response
				$this->response(self::STATUS_SUCCESS, $success ? $success : Lang::get('form.success-register'));
			}
			return $this->object->$id;	
		}
		catch(DatabaseException $e){				
			$this->status = self::STATUS_ERROR;			
			if($exit){
				$this->response(self::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : ($error ? $error : Lang::get('form.error-register')));
			}
			throw $e;
		}	
	}        
       
	
	/*
	 * Prototype: public function delete($exit = self::JSON)
	 * Description : delete an element from the database
	 * @param : $exit, if true, exit the script after delete
	 */
	public function delete($exit = self::EXIT_JSON, $success = "", $error = ""){
		try{
			$this->dbaction = self::ACTION_DELETE;

			if($this->model == self::DEFAULT_MODEL || !$this->reference){
				throw new Exception("The method delete of the class Form can be called only if model and reference properties are set");
			}
			
			$model = $this->model;
			$object = $model::getByExample($this->example);
			
			if(!$object){
				throw new Exception("This object instance cannot be removed : No such object");
			}
			
			$object->delete();
			$id = $object->getPrimaryColumn();
			
			$this->addReturn(array(
				'primary' => $object->$id,
				'action' => self::ACTION_DELETE
			));
			$this->status = self::STATUS_SUCCESS;
			
			if($exit){
				$this->response(self::STATUS_SUCCESS, $success ? $success : Lang::get('form.success-delete'));
			}
			return $object->$id;
		}
		catch(DatabaseException $e){		
			$this->status = self::STATUS_ERROR;
			
			if($exit){
				$this->response(self::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : ($error ? $error : Lang::get('form.error-delete')));
			}
			throw $e;
		}
	}
	
	/*
	 * Prototype: public function error($name, $error)
	 * Description: Add an error to the set of errors in the form
	 */
	public function error($name, $error){
		$this->errors[$name] = $error;
	}
	
	/*
	 * Prototype: public function addReturn($name, $message)
	 * Description : Add an information to return to the client
	 */
	public function addReturn($name, $message= ""){
		if(is_array($name)){
			foreach($name as $key => $value)
				$this->addReturn($key, $value);
		}
		else
			$this->returns[$name] = $message;
	}
	
	/*
	 * Prototype: public function response($status, $message)
	 * Description: return the response to the client
	 */ 
	public function response($status, $message = ''){
		$response = array();
		switch($status){
			case self::STATUS_SUCCESS :
				// The form has been submitted correctly
				http_response_code(self::HTTP_CODE_SUCCESS);
				if(! $this->nomessage){
					$response['message'] = $message ? $message : Lang::get('form.'.$status.'-'.$this->dbaction);
				}
				$response['data'] = $this->returns;				
				break;
			
			case self::STATUS_CHECK_ERROR :
				// An error occured while checking field syntaxes
				http_response_code(self::HTTP_CODE_CHECK_ERROR);
				$response['message'] = $message ? $message : Lang::get('form.'.$status.'-'.$this->dbaction);
				$response['errors'] = $this->errors;			
				break;
			
			case self::STATUS_ERROR :
			default :
				http_response_code(self::HTTP_CODE_ERROR);
				$response['message'] = $message ? $message : Lang::get('form.'.$status.'-'.$this->dbaction);
				$response['errors'] = $this->errors;
				break;
		}
		
		Response::set(json_encode($response, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK));
		Response::end();
	}
	
    /*
	 * Prototype: public function treat($exit = self::JSON)
	 * Description: Generic treatment of the form
	 */ 
	public function treat($exit = self::EXIT_JSON){
		if($this->submitted() == self::ACTION_DELETE){
			return $this->delete($exit);
		}
		else{
			if($this->check($exit)){
				return $this->register($exit);		
			}
		}
	}	
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/