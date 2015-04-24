<?php

class DBExample{
	public $example;
	
	private static $binaryOperators = array(
		'$ne' => '<>',
		'$lt' => '<',
		'$lte' => '<=',
		'$gt' => '>',
		'$gte' => '>=',
		'$like' => 'LIKE',
		'$nlike' => 'NOT LIKE',
		'$in' => 'IN',
		'$nin' => 'NOT IN'
	);
	
	private static $unaryOperators = array(
		'$null'  => 'IS NULL',
		'$notnull' => 'IS NOT NULl'
	);
	
	public function __construct($example){
		$this->example = $example;
	}
	
	public static function make($example, &$binds){
		$instance = new self($example);
		return $instance->parse($binds);
	}
	
	public function parse(&$binds){		
		return $this->parseElements($binds);
	}

	private function parseElements(&$binds = null, $example = null, $operator = 'AND', $upperKey = null){
		if($example === null)
			$example = $this->example;
		if(empty($binds)){
			$binds = array();
		}
		
		if(!is_array($example)){
			throw new DBExampleException("The example to parse must be an integer , " . gettype($example) . " given : " . var_export($example, true));
		}
		
		$sql = "";
		$elements = array();
		
		foreach($example as $key => $value){
			$bindKey = uniqid();
			
			// Binary operations (= , <, >, LIKE, IN, NOT IN, .. etc)
			if(isset(self::$binaryOperators[$key])){
				$op = self::$binaryOperators[$key];
				if(!$upperKey){
					throw new DBExampleException("The operation '$op' needs to be in a array associated to a field");
				}
				if(is_array($value)){
					$keys = array();
					foreach($value as $val){
						$bindKey = uniqid();
						$binds[$bindKey] = $val;							
						$keys[] = ':'.$bindKey;
					}
					$elements[] = DB::formatField($upperKey) . " $op (" . implode(',', $keys) . ")";
				}
				else{
					$binds[$bindKey] = $value;				
					$elements[] = DB::formatField($upperKey) . " $op :$bindKey";	
				}				
			}
			
			// Unary operations (IS NULL, IS NOT NULL, ... etc)
			elseif(is_scalar($value) && isset(self::$unaryOperators[$value])){
				$op = self::$unaryOperators[$value];					
				$elements[] = DB::formatField($key) . " $op";
			}
			
			// Parse a sub element
			elseif(is_numeric($key) && is_array($value)){
				$elements[] = $this->parseElements($binds, $value, $operator, $key);
			}
			
			else{
				switch($key){
					case '$not':
						// NOT (...)
						$elements[] = 'NOT ('.$this->parseElements($binds, $value) .')';
						break;

					case '$or' :
						// ... OR ...
						$elements[] = '(' . $this->parseElements($binds, $value, 'OR') . ')';
						break;

					case '$and' :
						// ... AND ...
						$elements[] = '(' . $this->parseElements($binds, $value, 'AND') . ')';
						break;

					default :
						if(is_scalar($value)){
							$binds[$bindKey] = $value;
							$elements[] = DB::formatField($key) . " = :$bindKey";
						}
						elseif(is_array($value)){
							$elements[] = $this->parseElements($binds, $value, $operator, $key);
						}
						else{							
							throw new DBExampleException("The value must be a scalar value");
						}
						break;
				}
			}
		}
		
		if($upperKey){
			return '(' . implode(" $operator ", $elements) . ')';
		}
		else{
			return implode(" $operator ", $elements);
		}
	}
	
		
}

class DBExampleException extends Exception{	
}