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
	
	const ACTION_REGISTER = 'register';
	const ACTION_DELETE = 'delete';
	
	// Default properties values 
	public $method = 'post';
	public $ajax = true;
	public $returns = array(), $errors = array();
	public $dbaction = self::ACTION_REGISTER;
	
	/*
     * 
     * Constructeur. Le constructeur prend en paramètre un tableau de données pour construire le formulaire.
     * Le tableau de paramètres contient les données suivantes :
     * id => le nom du formulaire (apprataîtra dans l'attribut id)
     * method => 'get' ou 'post'
	 * type => 'json', 'ajax', 'raw', default 'json'
	 * columns
     * action
     * enctype (optionnel) : pour les formulaires d'upload
     * database : instance de base de données MySQLClient
     * table : table ou jointure de tables concernées par le formulaire
     * reference : Permet de définir l'élément de la base à traiter, sous la forme array('field1' => 'value1', 'field2' => 'value2')
     * autoincrement : Si true, alors la clé primaire sera auto incrémentée (uniquement pour MongoDB)     
	 * onadd : Callback exécuté si l'ajout de l'élément réussit
	 * onupdate : callback exécuté si la mise à jour de l'élément réussit
	 * ondelete : callback exécuté si la suppression de l'élément réussit	 
     * fieldsets : un tableau contenant les paramètres des champs du formulaire, découpés dans les fielsets du formulaire	 
     * 
     * Le champs fields a la structure suivante :
     * 'fieldsets' => array(
     *      'fieldset1' => array(
     *          'legend' => "Identité de l'utilisateur",
     *          'nofieldset' => true, // si true, n'affiche pas le fieldset, default false         
     *          'fields' => array
     *              'name1' => array(
     *                  'field' => 'surname',
     *                  'type' => '{text|select|textarea|checkbox|radio|password|...}',
     *                  'label' => 'Nom',
     *                  'pattern' => '{regex à respecter, optionnel}',
     *                  'required' => {true|false},
     *                  'readonly' => {true|false},
     *                  'disabled' => {true|false},
     *                  'placeholder' => 'Tapez votre nom',
     *                  'maxlength' => 100,
     *                  'title' => 'Le nom de l\'utilisateur',
     *                  'errorAt' => 'name2', // le champ sur lequel l'erreur doit être affichée si mal saisie (pour le cas des input hiddens par exemple),
     *                  'before' => 'html à afficher avant le champ',
     *                  'after' => 'html à afficher après le champ',
     *                  'nl' => true | false, // Indique si on insère le champ dans une nouvelle ligne, default true,
     *                  'displayed' => true | false, // indique si le champ doit êter affiché ou non, default true
     *                  'independant' => true | false, // indique si le champ est indépendant de la base de données (récupéré depuis un variable par exemple), default false
     *                  'insert' => true | false, // indique si le champ doit être inséré en base (false par exemple dans le cas d'un CONCAT), default true
     *                  'default' => valeur par défaut du champ, par exemple pour les formulaire de nouvel élément, pas encore rempli
     *              ),
     *          ...
     *          )
     *      ),
     * 
     *      'fieldset2 => array( 
     *          ...
     *      ),
     * 
     *      'submits' => array(
     *          'fields' => array(
     *              'valid' => array(
     *                  'field' => 'valid',
     *                  'type' => 'submit',
     *                  'value' => 'Enregistrer'
     *              ),
     *          
     *              'cancel' => array(
     *                  'field' => 'cancel',
     *                  'type' => 'button', 
     *                  'onclick' => "location='../'",
     *                  'value' => 'Annuler'
     *              ),
     * 
     *              'delete' => array(
     *                  'field' => 'delete',
     *                  'type' => 'delete',
     *                  '
     *              ),
     *          )
     *      )
     *  )
     */
	public function __construct($param){
		/*
		 * Default values
		 */
		$this->type = "json";
		$this->method = "post";
		$this->action = $_SERVER['REQUEST_URI'];
		$this->status = null;
		$this->columns = 1;
		$this->labelWidth = '150px';
		$this->binds = array();
		$this->database = DB::get(MAINDB);
				
        Lang::load('form', Plugin::get('main')->getLangDir().'form');	
		
		// Get the parameters of the instance
        foreach($param as $key => $value){
            $this->$key = $value;
        }
        
		if(!empty($this->reference)){
			$this->condition = $this->database->parse($this->reference, $this->binds);			
		}
		
		if(!isset($this->new))
			$this->new = empty($this->binds) || empty($this->table) || !$this->database->count($this->table, $this->condition, $this->binds);
        
        // Get the fields in the "fields" instance property, and add the default values for the fieldsets and fields
        $this->fields = array();
		$this->aliases = array();
        foreach($this->fieldsets as $name => &$fieldset){            
            if($name == '_submits')
				// The fieldset containint the submit buttons does'nt have a fieldset tag                
                $fieldset['nofieldset'] = true;
				
			foreach($fieldset as $key => &$field){
				if($field instanceof Input){									
					if(defined(get_class($field).'::INDEPENDANT')){
						// This field is independant from the database
						$field->independant = true;
					}
					
					if(defined(get_class($field).'::NO_LABEL')){                    
						// This field cannot have any label
						unset($field->label);
					}
					
					$labelWidth = $this->labelWidth;
					if(isset($fieldset['labelWidth']))
						$labelWidth = $fieldset['labelWidth'];
					if(isset($field->labelWidth))
						$labelWidth = $field->labelWidth;
					$field->labelWidth = $labelWidth;
					
					if($name == '_submits' && !isset($field->nl))                    
						// A field in this fieldset cannot return to the new line
						$field->nl = false;
						
					$this->fields[$field->name] = &$field;
					$this->aliases[$field->field] = $field->name;
				}
            }            
        }
        
		// get the data of the form to display or register
        $this->reload();		
	}
	
	/*
	 * Prototype: public function get(){
	 * Description : Get the data of the form, from database or post or get
	 */
	public function get($database = false){
		$result = array();
		if(! $this->submitted() || $database){            
			// No data was submitted, we get the data in the database
			
			foreach($this->fields as $alias => $field){					
				if(isset($field->default))
					$result[$field->name] = $field->default;					
			}				
            
            if(!$this->new){
				// The form is related to an existing element, we get the data from the database
                $fields = array();
                foreach($this->fields as $alias => $field){                    
                    if(!$field->independant)
                        $fields[$field->field] = $field->name;
                }
                $result = array_merge($result, $this->database->select(array(
                    'table' => $this->table,
                    'condition' => $this->condition,
					'binds' => $this->binds,
					'group' => $this->group,
                    'fields' => $fields,
                    'one' => true
                )));
            }
        }
        else{
            // Le formulaire a été soumis, on récupère les données envoyées par GET ou POST
            $entry = $this->method == 'get' ? $_GET : $_POST;
            foreach($this->fields as $field){
				if(! $field instanceof ButtonInput)
					$result[$field->name] = $entry[$field->name];
            }
        }		
		return $result;
	}
	
	public function getData($prop = ""){
		return $prop ? $this->data[$prop] : $this->data;
	}
	
	/*
	 * Prototype: public function set($field, $value)
	 * Description: Force the value of a field in the form
	 */
	public function set($data, $prefix = null){
		foreach($data as $key => $value){
			$field = $prefix ? $prefix."[$key]" : $key;
			if(isset($this->fields[$field])){
				$this->fields[$field]->set($value);
				$this->data[$field] = $value;
			}
			elseif(is_array($value)){
				$this->set($value, $field);
			}
			
		}	
	}
	
	/*
	 * Prototype: public function reload()
	 * Description: Reload the data of the form
	 */
	public function reload(){
        $this->set($this->get());
	}
	
	/*
	 * Prototype: public function addInput($fieldset, $input, $after = 'last')
	 * Description: Add a new input in the form
	 */
	public function addInput($fieldset, $input){
		$labelWidth = $this->labelWidth;
		if(isset($this->fieldsets[$fieldset]['labelWidth']))
			$labelWidth = $this->fieldsets[$fieldset]['labelWidth'];
		if(isset($input->labelWidth))
			$labelWidth = $input->labelWidth;
		$input->labelWidth = $labelWidth;
					
		$this->fieldsets[$fieldset][] = $input;
		$id = array_search($input, $this->fieldsets[$fieldset]);
		$this->fields[$input->name] = &$this->fieldsets[$fieldset][$id];
		$this->aliases[$input->field] = $input->name;
	}
	
	
	/*
     * Prototype : public function submitted()
     * Description : Détermine si le formulaire a été soumis ou non
     */
    public function submitted(){
        $entry = $this->method == 'get' ? $_GET : $_POST;
		return isset($entry['_FORM_ACTION_']) ? $entry['_FORM_ACTION_'] : false;        
    }  
	
	/*
	 * Prototype: public function wrap($content)
	 * Description : wrap the content of the form with the form tag and return the string result
	 * @param : string $content, the content of the form
	 * @return : string
	 */
	public function wrap($content){
		return View::make(ThemeManager::getSelected()->getView('form/form.tpl'), array(
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
		$fieldsets = array();		
		foreach($this->fieldsets as $name => $fieldset){
			$fieldsets[$name] = $this->displayFieldset($name);
		}		
		
		$content = View::make(ThemeManager::getSelected()->getView('form/form-content.tpl') , array(
			'form' => $this,
			'fields' => $fields,
			'fieldsets' => $fieldsets,
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
			if($field instanceof Input){
				$fields[] = &$field;
			}
		}
		if(!empty($fields)){
			$fields[0]->first = true;
			$fields[count($fields) - 1]->last = true;
		}
		
        return View::make(ThemeManager::getSelected()->getView('form/form-fieldset.tpl'), array(
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
			if($exit)
				/*** The form check failed ***/
				$this->response(self::STATUS_ERROR, Lang::get('form.error-fill'));
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
		$insert = array();
		foreach($this->fields as $alias => $field){								
			/* Determine if we have to insert this field in the set of inserted values
			 * A field can't be inserted if :
			 * 	it type is in the independant types
			 * 	the field is defined as independant
			 * 	the fiels is defined as no insert			
			 */			
			if(!$field->independant && $field->insert !== false && !$field->disabled){
				/*** Insert the field value in the set ***/				
				$insert[$field->field] = $field->dbvalue();
			}									
		}
		
		$this->dbaction = self::ACTION_REGISTER;
		try{
			if($this->new){
				/*** Add a new record in the database ***/
				$id = $this->database->insert($this->table, $insert); /*** Return the Id of the new record ***/
			}
			else{
				/*** Update the record defined by the reference ***/				
				$this->database->update($this->table, $this->condition, $insert, $this->binds);
			}
			$this->addReturn(array(
				'primary' => $id,
				'action' => self::ACTION_REGISTER,
				'new' => $this->new
			));	
			$this->status = self::STATUS_SUCCESS;
			if(isset($this->onregister) && is_callable($this->onregister)){
				$function = $this->register;
				$function();
			}
			
			if($exit)
				// output the response
				$this->response(self::STATUS_SUCCESS, $success ? $success : Lang::get('form.success-register'));
			return true;	
		}
		catch(DatabaseException $e){				
			$this->status = self::STATUS_ERROR;			
			if($exit)
				$this->response(self::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : ($error ? $error : Lang::get('form.error-register')));
			throw $e;
		}	
	}        
       
	
	/*
	 * Prototype: public function delete($exit = self::JSON)
	 * Description : delete an element from the database
	 * @param : $exit, if true, exit the script after delete
	 */
	public function delete($exit = self::EXIT_JSON, $success = "", $error = ""){
		$result = array();
		$this->dbaction = self::ACTION_DELETE;
		try{		
			$this->database->delete($this->table, $this->condition, $this->binds);
			$test = true;
			$this->addReturn(array(
				'primary' => count($this->reference) == 1 ? $this->reference[0] : array_values($this->reference),
				'action' => $this->dbaction
			));
			$this->status = self::STATUS_SUCCESS;
			if(isset($this->ondelete) && is_callable($this->ondelete))
				$this->ondelete();
			if($exit){
				$this->response(self::STATUS_SUCCESS, $success ? $success : Lang::get('form.success-delete'));
			}
			return true;
		}
		catch(DatabaseException $e){		
			$this->status = self::STATUS_ERROR;
			if($exit)
				$this->response(self::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : ($error ? $error : Lang::get('form.error-delete')));	
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
		// Return the status of the form submission
		$this->addReturn('status', $status);
				
		if($this->nomessage)
			// Return no message
			$this->addReturn('nomessage', true);
		else
			// Return the message to display
			$this->addReturn('message', $message ? $message : Lang::get('form.'.$status.'-'.$this->dbaction));
			
		$this->returns = array('errors' => $this->errors, 'data' => $this->returns);
		
		Response::set(json_encode($this->returns, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK));
		Response::end();
	}
	
    /*
	 * Prototype: public function treat($exit = self::JSON)
	 * Description: Generic treatment of the form
	 */ 
	public function treat($exit = self::EXIT_JSON){
		if($this->submitted() == self::ACTION_DELETE)
			return $this->delete($exit);
		else
			if($this->check($exit))
				return $this->register($exit);		
	}	
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/