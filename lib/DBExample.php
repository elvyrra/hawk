<?php
/**
 * DBExample.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is used to construct SQL WHERE expressions from arrays.
 * This can be useful to build simple conditions without writing SQL query and manage binding values
 * This class is used in classes Model, Form, ItemList to get data from the database.
 * Example : To make the expression 'field1 = "value1" AND (field2 IS NOT NULL OR field3 < 12)',
 * create an DBExample like :
 * <code>
 * new DBExample(array(
 *        array('field1' => "value1"),
 *        array('$or' => array(
 *            array(
 *                'field2' => '$notnull'
 *            ),
 *            array(
 *                'field3' => array('$lt' => 12)
 *            )
 *        ))
 * ))
 * </code>
 *
 * @package Core
 */
class DBExample{
    /**
     * The example content
     *
     * @var array
     */
    public $example = array();

    /**
     * The supported binary operators
     *
     * @var array
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
     *
     * @var array
     */
    private static $unaryOperators = array(
        '$null'  => 'IS NULL',
        '$notnull' => 'IS NOT NULl'
    );


    private static $complexOperators = array(
        '$between' => '{field} BETWEEN %s AND %s',
        '$match' => 'MATCH ({field}) AGAINST (%s)'
    );

    /**
     * Constructor
     *
     * @param array $example The DBExample structure
     */
    public function __construct($example){
        $this->example = $example;
    }

    /**
     * Create a DBExample and parse it
     *
     * @param array $example The DBExample structure
     * @param array $binds   This variables, passed by reference,
     *                        will be filled with the binded values during example parsing during example parsing
     *
     * @return string the parsed SQL expression
     */
    public static function make($example, &$binds){
        $instance = new self($example);
        return $instance->parse($binds);
    }


    /**
     * Parse the example to create the corresponding SQL expression
     *
     * @param array $binds This variable, passed by reference,
     *                      will be filled with the binded values during example parsing
     *
     * @return string the parsed SQL expression
     */
    public function parse(&$binds){
        return $this->parseElements($binds);
    }


    /**
     * Parse a substructure. This method is used internally and recursively by the method parse
     *
     * @param array  $binds    The binded values, filles during parsing
     * @param array  $example  The substructure to parse
     * @param string $operator The operator to separate the parsed substructures
     * @param string $upperKey The key of the parent structure. For example, if you parse array('$gt' => 3),
     *                         in the whole structure array('field' => array('$gt' => 3)),
     *                         $upperKey will be set to 'field'
     *
     * @return string The SQL expression, result of the elements parsing
     */
    private function parseElements(&$binds = null, $example = null, $operator = 'AND', $upperKey = null){
        if($example === null) {
            $example = $this->example;
        }
        if(empty($binds)) {
            $binds = array();
        }

        if(!is_array($example)) {
            throw new DBExampleException(
                'The example to parse must be an integer , ' .
                gettype($example) . ' given : ' .
                var_export($example, true)
            );
        }

        $sql = "";
        $elements = array();
        $sqlExpressions = DB::getSqlExpressions();

        foreach($example as $key => $value){
            // Binary operations (= , <, >, LIKE, IN, NOT IN, .. etc)
            if(isset(self::$binaryOperators[$key])) {
                $op = self::$binaryOperators[$key];
                if(!$upperKey) {
                    throw new DBExampleException("The operation '$op' needs to be in a array associated to a field");
                }
                if(is_array($value)) {
                    $values = array();
                    foreach($value as $unitValue) {
                        if(isset($sqlExpressions[$unitValue])) {
                            $values[] = $sqlExpressions[$unitValue];
                        }
                        else {
                            $bindKey = uniqid();
                            $binds[$bindKey] = $unitValue;
                            $values[] = ':' . $bindKey;
                        }
                    }
                    $elements[] = DB::formatField($upperKey) . " $op (" . implode(',', $values) . ")";
                }
                else{
                    if(isset($sqlExpressions[$value])) {
                        $val = $sqlExpressions[$value];
                    }
                    else {
                        $bindKey = uniqid();
                        $binds[$bindKey] = $value;
                        $val = ':' . $bindKey;
                    }
                    $elements[] = DB::formatField($upperKey) . " $op $val";
                }
            }

            // Unary operations (IS NULL, IS NOT NULL, ... etc)
            elseif(is_scalar($value) && isset(self::$unaryOperators[$value])) {
                $op = self::$unaryOperators[$value];
                $elements[] = DB::formatField($key) . " $op";
            }

            // Complex operators (BETWEEN, ...)
            elseif(isset(self::$complexOperators[$key])) {
                $expression = str_replace('{field}', DB::formatField($upperKey), self::$complexOperators[$key]);

                if(is_scalar($value)) {
                    $bindKey = uniqid();
                    $binds[$bindKey] = $value;
                    $val = ':' . $bindKey;

                    $elements[] = sprintf($expression, $val);
                }
                else {
                    $vals = array();
                    foreach($value as $unitvalue) {
                        $bindKey = uniqid();
                        $binds[$bindKey] = $unitvalue;
                        $val = ':' . $bindKey;

                        $vals[] = $val;
                    }
                    $elements[] = vsprintf($expression, $vals);
                }
            }

            // Parse a sub element
            elseif(is_numeric($key) && is_array($value)) {
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
                        if(is_scalar($value)) {
                            if(isset($sqlExpressions[$value])) {
                                $val = $sqlExpressions[$value];
                            }
                            else {
                                $bindKey = uniqid();
                                $binds[$bindKey] = $value;
                                $val = ':' . $bindKey;
                            }

                            $elements[] = DB::formatField($key) . ' = ' . $val;
                        }
                        elseif(is_array($value)) {
                            $elements[] = $this->parseElements($binds, $value, $operator, $key);
                        }
                        else{
                            throw new DBExampleException(
                                'The value must be a scalar value, given : ' .
                                $key . ' => ' . var_export($value, true)
                            );
                        }
                        break;
                }
            }
        }

        if($upperKey) {
            return '(' . implode(" $operator ", $elements) . ')';
        }
        else{
            return implode(" $operator ", $elements);
        }
    }
}