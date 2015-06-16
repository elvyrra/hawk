<?php
/**********************************************************************
 *    						FormInput.class.php
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

class FormInput{	
    protected static $uniqid;
	
	public static $attr = array(
		'checked' => 'bool',
		'class' => 'text',
		'cols' => 'int',
		'disabled' => 'bool',
		'id' => 'text',
		'maxlength' => 'int',
		'multiple' => 'bool',
		'name' => 'text',
		'placeholder' => 'html',
		'readonly' => 'bool',
		'rows' => 'int',
		'size' => 'int',
		'style' => 'text',
		'title' => 'html',
		'type' => 'text',
		'value' => 'html'
	);
    public  $attributes = array(),
    		$class = '',
    		$title = '',
    		$style = '',
    		$name,
    		$id,
    		$value = '',
    		$placeholder = '',
    		$maxlength = 0,
    		$disabled = false,
    		$readonly = false,
    		$required = false,
    		$custom = array(),
    		$errorAt = '',
    		$nl = true,
    		$notDisplayed = false,
    		$hidden = false,
    		$blockClass = '',
    		$before = '',
    		$after = '',
    		$label = '',
    		$beforeLabel = false,
    		$labelStyle = '',
    		$last = false,
    		$first = false,
    		$pattern = '',
    		$mask = '';

	
    /*
     * Constructeur par défaut de la classe FormInput
     * @param : 
     *  - String $name : L'alias, et attribut name du champ
     *  - Array $param : Les paramètres du champ
     *  - Form $form : Le formulaire auquel appartient ce champ
     */
    public function __construct($param) {
        $this->custom = array();        
        
        foreach($param as $key => $value){
            $this->$key = $value;
        }
		if(!isset($this->name)){
			$this->name = $this->field; 
		}
			
        if(!isset($this->id)){		
			if(!isset(self::$uniqid))
				self::$uniqid = uniqid();
            $this->id = self::$uniqid.'-'.$this->name;
		}
		
		$this->type = static::TYPE;
              
		$theme = ThemeManager::getSelected();
		
        $file = ThemeManager::getSelected()->getView(Form::VIEWS_DIR . 'form-input-' . static::TYPE . '.tpl');
        $this->tpl = is_file($file) ? $file : ThemeManager::getSelected()->getView(Form::VIEWS_DIR . 'form-input.tpl');
    }
	
	/*
     * Affichage de l'input sous forme string
     */
    public function __toString(){
		$theme = ThemeManager::getSelected();
		
		if($this->name == $this->errorAt){
			unset($this->errorAt);
		}

		$inputLabel = $this->label ? View::make(ThemeManager::getSelected()->getView(Form::VIEWS_DIR . 'form-input-label.tpl'), array(
			'input' => $this
		)) : '';
		
		$inputDisplay = View::make($this->tpl, array(
			'input' => $this
		));
            
        return View::make(ThemeManager::getSelected()->getView(Form::VIEWS_DIR . 'form-input-block.tpl'), array(
            'input' => $this, 
			'inputLabel' => $inputLabel,
			'inputDisplay' => $inputDisplay
        ));
    }
	
	/*
	 * Prototype: public function check(array &$errors)
	 * Description: Check the data send by the form and set the errors if not
	 */
	public function check(&$form = null){				
		if(empty($this->errorAt))
			$this->errorAt = $this->name;
		
		if(!empty($this->required) && ((string)$this->value == '' || $this->emptyValue && $this->value === $this->emptyValue)){
			// The field is required but not filled
			$form && $form->errors[$this->errorAt] = Lang::get('form.required-field');
			return false;
		}
		if(!empty($this->value) && $this->pattern){
			// test the format of the field
			if(!preg_match($this->pattern, $this->value)){
				// the format of the field value is not correct				
				$form && $form->errors[$this->errorAt] = isset($this->errorMessage) ? $this->errorMessage : (Lang::exists('form.'.static::TYPE."-format") ? Lang::get('form.'.static::TYPE."-format") : Lang::get('form.field-format'));
				return false;
			}
		}
		
		if(!empty($this->value) && $this->unique && $form ){
			$example = new DBExample(array(
				'$not' => $form->reference,
				array($this->name => $this->dbvalue())
			));
				
			$model = $form->model;
			if($model::getByExample($example)){
				// The field must be unique but is not
				$form->errors[$this->errorAt] = Lang::get('form.unique-field');
				return false;
			}
		}
		
		if(!empty($this->validators)){
			foreach($this->validators as $validator){
				if(is_callable($validator) && !$validator($this, $error)){
					$form->errors[$this->errorAt] = $error;
					return false;
				}
			}
		}
		
		// The field is correctly filled (for the common checks)
		return true;
	}
	
	/*
	 * Prototype: public function dbValue()
	 * Description : return the value, formatted for the database
	 */
	public function dbvalue(){
		switch(strtolower($this->dataType)){
			case "boolean" :
			case "bool" :
				return (bool) $this->value;				
			break;
			
			case "integer" :
				return (int) $this->value;
			break;
			
			case "numeric" :
			case "float" :
				return (float) $this->value;
			break;
			
			default :
				return $this->value;
			break;
		}		
	}
	
	public function set($value){
		$this->value = $value;
	}
	
	public static function getInstance($parameters){
		if(!isset($parameters['type']))
			$parameters['type'] = 'text';
			
		$classname = ucwords($parameters['type']).'Input';
		unset($parameters['type']);
		
		return new $classname($parameters);		
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/