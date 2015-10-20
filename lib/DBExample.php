<?php
/**
 * DBExample.php
 * @author Elvyrra SAS
 * @license MIT
 */

namespace Hawk;

/**
 * This class is used to construct SQL WHERE expressions from arrays. 
 * This can be useful to build simple conditions without writing SQL query and manage binding values
 * This class is used in classes Model, Form, ItemList to get data from the database.
 * Example : To make the expression 'field1 = "value1" AND (field2 IS NOT NULL OR field3 < 12)', create an DBExample like :
 * new DBExample(array(
 *		array('field1' => "value1"),
 *		array('$or' => array(
 *			'field2' => '$notnull',
 *			'field3' => array('$lt' => 12)
 *		))
 * ))
 * @package Core
 */
class DBExample{
	/**
	 * The example content 
	 * @var array
	 */
	public $example = array();
	
	/**
	 * The supported binary operators
	 */
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
	
	/**
	 * The supported unary operators
	 */
	private static $unaryOperators = array(
		'$null'  => 'IS NULL',
		'$notnull' => 'IS NOT NULl'
	);
	
	/**
	 * Constructor
	 * @param array $example The DBExample structure
	 */
	public function __construct($example){
		$this->example = $example;
	}
	
	/**
	 * Create a DBExample and parse it
	 * @param array $example The DBExample structure
  	 * @param array $binds This variables, passed by reference, will be filled with the binded values during example parsing
  	 * @return string the parsed SQL expression
	 */
	public static function make($example, &$binds){
		$instance = new self($example);
		return $instance->parse($binds);
	}
	

	/**
	 * Parse the example to create the corresponding SQL expression
	 * @param array $binds This variable, passed by reference, will be filled with the binded values during example parsing
	 * @return string the parsed SQL expression
	 */
	public function parse(&$binds){		
		return $this->parseElements($binds);
	}


	/**
	 * Parse a substructure. This method is used internally and recursively by the method parse
	 * @param array $binds The binded values, filles during parsing
	 * @param array $elements The substructure to parse
	 * @param string $operator The operator to separate the parsed substructures
	 * @param string $upperKey The key of the parent structure. For example, if you parse array('$gt' => 3), in the whole structure array('field' => array('$gt' => 3)), $upperKey will be set to 'field'
	 * @return string The SQL expression, result of the elements parsing 
	 */
	private function parseElements(&$binds = null, $example = null, $operator = 'AND', $upperKey = null){
		if($example === null){
			$example = $this->example;
		}
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
							throw new DBExampleException("The value must be a scalar value, given : $key => " . var_export($value, true));
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


/**
 * This class describes the behavior of the exceptions throwed by DBExample class
 */
class DBExampleException extends \Exception{	
}