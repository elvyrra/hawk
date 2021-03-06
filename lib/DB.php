<?php
/**
 * DB.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class is a manager for MySQL connection and queries
 *
 * @package Core
 */
class DB {
    use Utils;

    /**
     * List of servers to connect
     */
    private static $servers = array();

    /**
     * List of connections instances
     */
    public static $instances = array();

    /**
     * PDO Object representing the connection to the server
     */
    private $connection;

    /**
     * The hosname of the connection
     */
    public $host,

    /**
     * The user connected to the database
     */
    $username,

    /**
     * The password of the user
     */
    $password,

    /**
     * The selected database
     */
    $dbname,


    /**
     * The charset
     */
    $charset = 'utf8',

    /**
     * The logged queries
     */
    $log = array();


    // Status constants
    const RETURN_STATUS = 0; // Returns the status of the query execution
    const RETURN_ARRAY = 1;    // Return each line of the query result in an array
    const RETURN_OBJECT = 3; // Return each line of the query result in an object
    const RETURN_LAST_INSERT_ID = 5; // Return The last inserted id
    const RETURN_AFFECTED_ROWS = 6; // Return the rows affected by the query
    const RETURN_QUERY = 7; // Return the query it self
    const RETURN_CURSOR = 8; // Return a cursor

    // Sort constants
    const SORT_ASC = 'ASC'; // Sort ascending
    const SORT_DESC = 'DESC'; // Sort descending

    /**
     * A salt key, generated for each script instance, to encode raw sql expressions
     * @var string
     */
    private static $sqlExpressionSalt = '';

    /**
     * The list of declared raw sql expressions
     * @var array
     */
    private static $sqlExpressions = array();

    /**
     * Constructor
     *
     * @param array $data - the connection properties, with keys host, dbname, username, password
     */
    public function __construct($data) {
        $this->map($data);

        if(strpos($this->host, ':') !== false) {
            list($this->host, $this->port) = explode(':', $this->host, 2);
        }
        try    {
            $dns = "mysql:host=$this->host";
            if(!empty($this->dbname)) {
                $dns .= ";dbname=$this->dbname";
            }
            if(!empty($this->port)) {
                $dns .= ";port=$this->port";
            }

            $this->connection = new \PDO($dns, $this->username, $this->password);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 1);

            $this->connection->query('SET NAMES "' . $this->charset . '"');
        }
        catch(\PDOException $e) {
            throw new DBException($e->getMessage(), DBException::CONNECTION_ERROR, $this->host);
        }
    }


    /**
     * Add a configuration for a database connection
     *
     * @param string $name   The instance name
     * @param array  $params The connection parameters.
     *                       Each element of this array is an array itself, containing the connections data :
     *                           - host : The database hostname / ip and port,
     *                           - username : The user name
     *                           - password : The user password
     *                           - dbname : The database name,
     *                       When openning the connection, if the first connection fails,
     *                       a connection will be tried on the second element, and then.
     *                       This is usefull for master / slave connections
     */
    public static function add($name, $params){
        self::$servers[$name] = $params;
    }

    /**
     * Get the open connection, or open it if not already open.
     * This method manages master / slaves connections
     *
     * @param string $name     The name of the instance
     * @param string $replication The master or slave replication
     *
     * @return DB the connected instance
     */
    public static function get($name, $replication = 'master') {
        if(!isset(self::$instances[$name][$replication])) {
            $servers = self::$servers[$name];

            if(isset($servers[$replication])) {
                $server = $servers[$replication];
            }
            else {
                if($replication === 'master') {
                    $server = reset($servers);
                }
                else {
                    $server = end($servers);
                }
            }

            if(!isset(self::$instances[$name])) {
                self::$instances[$name] = array();
            }

            try {
                self::$instances[$name][$replication] = new self($server);
                // The connection succeed
                App::logger()->debug('Connection to db ' . $name . ' successfull');
            }
            catch(DBException $e){
                // The connection failed

                App::logger()->error('Impossible to connect to db ' . $name . ' : ' . $e->getMessage());
                throw $e;
            }
        }

        return self::$instances[$name][$replication];
    }

    /**
     * Execute a SQL query on the SQL server, and get the result of execution
     *
     * @param string $sql     The query to execute,
     * @param array  $binds   The binded value to send to the server
     * @param array  $options The result options. This array can contains the following data :
     *                        - 'return' (mixed) : The type of return (default DB::RETURN_STATUS). It can be set to :
     *                            . DB::RETURN_STATUS : Returns the execution status
     *                            . DB::RETURN_ARRAY : Each result row is an associative array, with columns as keys
     *                            . DB::RETURN_OBJECT : Eeach result row is a StdClass instance, with columns as properties
     *                            . DB::RETURN_LAST_INSERT_ID : Return the last inserted id
     *                            . DB::RETURN_AFFECTED_ROWS : Returns the number of affected rows
     *                            . DB::RETURN_QUERY : Returns the query itself
     *                            . DB::RETURN_CURSOR : Returns a cursor to fetch the results
     *                            . A classname : Each result row is an instance of the given classname, with columns as properties
     *                        - 'onerow' (bool) : If true, the function will return the first row of the result set (default false)
     *                         - 'index' (string) : Defines the column values to get as array result index (default null)
     *                        - 'args' (array) : This can be used when you define a classname as 'return', to pass arguments to the class constructor
     *
     * @return mixed The execution result, depending on what type of return has been defined in $options parameter
     */
    public function query($sql, $binds = array(), $options = array()){
        $default = array(
            'return' => self::RETURN_STATUS,
            'onerow' => false,
            'index' => null,
            'args' => array()
        );

        foreach($default as $name => $value){
            if(!isset($options[$name])) {
                $options[$name] = $value;
            }
        }

        try {
            // Prepare the query
            $stmt = $this->connection->prepare($sql);

            // Prepare query logging
            $log = $sql;

            // Bind values to the query
            foreach($binds as $key => $bind){
                $stmt->bindValue(":$key", $bind);
                $log = str_replace(":$key", $this->connection->quote($bind), $log);
            }

            // Execute the query
            $start = microtime(true);
            $stmt->execute();
            $end = microtime(true);

            // Get the result
            $result;
            if(is_int($options['return'])) {
                switch($options['return']){
                 // Return the query status
                    case self::RETURN_STATUS:
                        $result =  true;
                        break;

                    case self::RETURN_ARRAY:
                        if($options['onerow']) {
                            // Return the first row as associative array
                            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                        }
                        else{
                            // Return an array of associative arrays
                            $data = array();
                            while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
                                if($options['index']) {
                                    $data[$row[$options['index']]] = $row;
                                }
                                else{
                                    $data[]= $row;
                                }
                            }
                            $result = $data;
                        }
                        break;

                    case self::RETURN_OBJECT :
                        if($options['onerow']) {
                            // Return the first row as an StdClass instance
                            $result = $stmt->fetch(\PDO::FETCH_OBJ);
                        }
                        else{
                            // Return an array of StdClass instances
                            $data = array();
                            while($row = $stmt->fetch(\PDO::FETCH_OBJ)){
                                if($options['index']) {
                                    $index = $options['index'];
                                    $data[$row->$index]= $row;
                                }
                                else{
                                    $data[] = $row;
                                }
                            }
                            $result = $data;
                        }
                        break;

                    case self::RETURN_LAST_INSERT_ID :
                        // Return the last inserted array
                        $result = $this->connection->lastInsertId();
                        break;

                    case self::RETURN_AFFECTED_ROWS :
                        // Return the number of affected rows
                        $result = $stmt->rowCount();
                        break;

                    case self::RETURN_CURSOR :
                        // Return a cursor to fetch the results
                        $result = $stmt;
                        break;
                }
            }
            else {
                if($options['onerow']) {
                    // Return a model instance
                    $object = $stmt->fetchObject($options['return'], $options['args']);
                    // Set the model as a non new model instance
                    if($object) {
                        $object->new = false;
                    }

                    $result = $object !== false ? $object : null;
                }
                else{
                    // Return an array of model instances
                    $data = array();
                    while($object = $stmt->fetchObject($options['return'], $options['args'])){
                        // Set the model as a non new model instance
                        $object->new = false;

                        if($options['index']) {
                            $index = $options['index'];
                            $data[$object->$index] = $object;
                        }
                        else {
                            $data[] = $object;
                        }
                    }

                    $result = $data;
                }
            }

            // Log the query
            $this->addLog(str_replace(PHP_EOL, ' ', $log), $result, $start, $end);

            return $result;
        }
        catch(\PDOException $e) {
            $this->addLog($log, 'query failed', $start, microtime(true));

            $code = $e->getCode() === '23000' ? DBException::CONSTRAINT_ERROR : DBException::QUERY_ERROR;

            throw new DBException($e->getMessage(), $code, $sql);
        }
    }


    /**
     * Select a database
     *
     * @param string $dbname The database to select
     *
     * @return boolean true if the database has been sucessfully selected, false in other cases
     */
    public function selectDb($dbname){
        return $this->query('use ' . $dbname);
    }

    /**
     * Build and execute a SELECT query on the selected database, and return the result
     *
     * @param array $query The parameters of the query. This parameter can have the following data :
     *                        - 'from' string (required) : The table name where to search
     *                        - 'fields' array (optionnal) : The fields to get. Each element of this array can be :
     *                            - A single element of the column name
     *                            - A key/value combination that will be parse to 'key as value' in the SQL query
     *                        - 'where' string | DBExample (optionnal) : The search condition. Can be a SQL expression or a DBExample instance
     *                        - 'group' array (optionnal) : The columns to group the result, each column in an array element
     *                        - 'having' string (optinnal) : The 'HAVING' expression, formatted as a SQL expression
     *                        - 'orderby' array (optionnal) : The result sort, where each key is a column name, and the values define the order (ASC, DESC)
     *                        - 'start' int (optionnal) : The first row number to get the results
     *                        - 'limit' int (optionnal) : The maximum number of rows to return
     *                        - 'one' bool (optionnal) : If set to true, then the first row will be returned
     *                        - 'binds' array (optionnal) : The binded values
     *                        - 'index' string (optionnal) : If set, the result arrar will be indexed by the value of the column set by this property
     *                        - 'return' mixed (default : DB::RETURN_ARRAY) : The return type (all possible values are defined on the method 'query')
     *
     *    @return mixed The query result
     */
    public function select($query) {
        $query = (object) $query;

        if(!isset($query->from)) {
            throw new DBException("'from' parameter is mandatory in select method", DBException::QUERY_ERROR, '');
        }

        /*** Treat the paramters ***/
        if(empty($query->fields)) {
            $query->fields = "*";
        }
        elseif(is_array($query->fields)) {
            $tmp = array();
            foreach($query->fields as $name => $alias){
                if(is_numeric($name)) {
                    $tmp[] = $alias;
                }
                else{
                    $tmp[] = "$name as $alias";
                }
            }
            $query->fields = implode(",", $tmp);
        }

        if(isset($query->where) && $query->where instanceof DBExample) {
            $query->where = $query->where->parse($query->binds);
        }
        $where = !empty($query->where) ? "WHERE $query->where" : '';

        $group = !empty($query->group) ? 'GROUP BY '. implode(',', array_map(function($group) {
            return self::formatField($group);
        }, $query->group)) : "";

        if(isset($query->having) && $query->having instanceof DBExample) {
            $query->having = $query->having->parse($query->binds);
        }
        $having = !empty($query->having) ? "HAVING $query->having" : '';

        if(!empty($query->orderby)) {
            $orders = array();
            foreach($query->orderby as $field => $value){
                $orders[] = self::formatField($field) . " " . $value;
            }
            $orderby = "ORDER BY " . implode(",", $orders);
        }
        else{
            $orderby = "";
        }

        $limit = "";
        if(!empty($query->one)) {
            $limit = "LIMIT 1";
        }
        elseif(!empty($query->limit)) {
            $limit = " LIMIT ".(!empty($query->start) ? "$query->start, " : ""). "$query->limit";
        }

        $sql = "SELECT $query->fields FROM $query->from $where $group $having $orderby $limit";

        if(empty($query->binds)) {
            $query->binds = array();
        }

        if(empty($query->return)) {
            $query->return = self::RETURN_ARRAY;
        }

        return $this->query(
            $sql, $query->binds, array(
            'return' => $query->return,
            'onerow' => !empty($query->one),
            'index' => empty($query->index) ? '' : $query->index
            )
        );
    }


    /**
     * Insert a row in a table
     *
     * @param string $table          The table where to insert data
     * @param array  $insert         The data to insert, where keys are the columns names, and values the values to insert
     * @param string $flag           A flag on the INSERT query (IGNORE or DELAYED)
     * @param string $onduplicatekey The ON DUPLICATE KEY expression
     *
     * @return mixed The value of the last inserted id
     */
    public function insert($table, $insert = array(), $flag = '', $onduplicatekey = ''){
        $keys = array();
        $values = array();
        $binds = array();

        foreach($insert as $key => $value) {
            $keys[] = self::formatField($key);

            if(isset(self::$sqlExpressions[$value])) {
                $values[] = self::$sqlExpressions[$value];
            }
            else {
                $uniq = uniqid();
                $values[] = ':' . $uniq;
                $binds[$uniq] = $value;
            }
        }

        $keys = implode(',', $keys);
        $values = implode(',', $values);

        $sql= 'INSERT ' . $flag . ' INTO ' . $table . ' (' . $keys . ') VALUES (' . $values . ')';

        if($onduplicatekey) {
            $sql .= ' ON DUPLICATE KEY UPDATE ' . $onduplicatekey;
        }

        return $this->query($sql, $binds, array('return' => self::RETURN_LAST_INSERT_ID));
    }

    /**
     * Replace data in a table
     *
     * @param string $table  the table where to replace data
     * @param array  $insert The data to insert, where keys are the columns names, and values the values to insert
     *
     * @return mixed The value of the last inserted id
     */
    public function replace($table, $insert = array()){
        $keys = array();
        $values = array();
        $binds = array();

        foreach($insert as $key => $value) {
            $keys[] = self::formatField($key);

            if(isset(self::$sqlExpressions[$value])) {
                $values[] = self::$sqlExpressions[$value];
            }
            else {
                $uniq = uniqid();
                $values[] = ':' . $uniq;
                $binds[$uniq] = $value;
            }
        }

        $keys = implode(',', $keys);
        $values = implode(' , ', $values);

        $sql = 'REPLACE INTO ' . $table . ' (' . $keys . ') VALUES (' . $values  .')';

        return $this->query($sql, $binds, array('return' => self::RETURN_LAST_INSERT_ID));
    }

    /**
     * Update records in a table
     *
     * @param string           $table  The table to update
     * @param string|DBExample $where  The condition to find rows to update
     * @param array            $update The columns to update, where keys are the columns names and values are the values to update
     * @param array            $binds  The binded values, in case of $where is a SQL expression
     *
     * @return int The number of updated rows
     */
    public function update($table, $where = null, $update = array(), $binds = array()) {
        if(!empty($where)) {
            if($where instanceof DBExample) {
                $where = $where->parse($binds);
            }
            $where = ' WHERE ' . $where;
        }

        $updates = array();
        foreach($update as $key => $value) {
            if(isset(self::$sqlExpressions[$value])) {
                $updates[] = self::formatField($key) . ' = ' . self::$sqlExpressions[$value];
            }
            else {
                $bind = uniqid();
                $updates[] = self::formatField($key) . ' = :' . $bind;
                $binds[$bind] = $value;
            }

        }

        $sql = 'UPDATE ' . $table . ' SET '. implode(',', $updates) . $where;

        return $this->query($sql, $binds, array('return' => self::RETURN_AFFECTED_ROWS));
    }

    /**
     * Delete records in a table
     *
     * @param string           $table The table to update
     * @param string|DBExample $where The condition to find rows to delete
     * @param array            $binds The binded values, in case of $where is a SQL expression
     *
     * @return int The number of deleted rows
     */
    public function delete($table, $where = null, $binds = array()) {
        if(!empty($where)) {
            if($where instanceof DBExample) {
                $where = $where->parse($binds);
            }
            $where = 'WHERE ' . $where;
        }

        $sql = 'DELETE FROM ' . $table . ' ' . $where;

        return $this->query($sql, $binds, array('return' => self::RETURN_AFFECTED_ROWS));
    }

    /**
     * Count elements in a table
     *
     * @param string           $table The table to count elements
     * @param string|DBExample $where The condition to find rows to count
     * @param array            $binds The binded values, in case of $where is a SQL expression
     * @param string           $field To count a specific field
     * @param array            $group Groups rows before counting them
     *
     * @return int The number of found elements
     */
    public function count($table , $where = null, $binds = array(), $field = null, $group = array()) {
        if($where instanceof DBExample) {
            $where = $where->parse($binds);
        }
        if (!empty($where)) {
            $where = 'WHERE ' . $where;
        }

        if(!empty($group)) {
            $group = 'GROUP BY ' . implode(',', array_map(function($gr) {
                return self::formatField($gr);
            }, $group));
        }
        else {
            $group = '';
        }

        if(empty($field)) {
            $field = '*';
        }
        else{
            $field = $field;
        }

        $sql = 'SELECT COUNT(' . $field . ') as counter FROM ' . $table .' ' . $where . ' ' . $group;

        return (int) $this->query($sql, $binds, array('return' => self::RETURN_OBJECT, 'onerow' => true))->counter;
    }


    /**
     * Format a string to a SQL field format : [`table`.]`field`
     *
     * @param string $str The string to format
     *
     * @return string The formatted string
     */
    public static function formatField($str) {

        $regex = '/^(\w+)(\.(\w+))?$/';

        if(preg_match($regex, $str)) {
            return preg_replace_callback(
                '/^(\w+)(\.(\w+))?$/', function ($m) {
                    return '`' . $m[1] . '`' . (isset($m[2]) && isset($m[3]) ? '.`' . $m[3] . '`' : '');
                }, $str
            );
        }

        return $str;  //return '`' . $str . '`';
    }


    /**
     * Log a query in the internal log system of this class. This method can be used to get all the executed queries to optimize your scripts
     *
     * @param string $query  The query to log
     * @param mixed  $result The result of the query
     * @param int    $start  The start time of the query execution in the script process
     * @param int    $end    The end time of the query execution in the script process
     */
    private function addLog($query, $result, $start, $end){
        $this->logs[] = array(
            'query' => $query,
            'start' => $start - SCRIPT_START_TIME,
            'end' => $end - SCRIPT_START_TIME,
            'time' => $end - $start
        );
    }


    /**
     * Get the DB logs
     *
     * @return array The logged queries
     */
    public function getLogs(){
        return $this->logs;
    }


    /**
     * Quote a string
     *
     * @param string $str  The data to quote
     * @param int    $type The data type
     *
     * @return string The quoted string
     */
    public function quote($str, $type = \PDO::PARAM_STR){
        return $this->connection->quote($str, $type);
    }


    /**
     * Get the real name of a table, with the configured prefix
     *
     * @param string $table  The base table name
     * @param string $prefix If set, this prefix will replace the one configured for the application
     *
     * @return string The complete name of the table
     */
    public static function getFullTablename($table, $prefix = null){
        if($prefix === null) {
            $prefix = App::conf()->get('db.prefix');
        }

        return $prefix . $table;
    }

    /**
     * This method is used to insert a raw SQL expression in an example or a insertion,
     * and avoid this value to be binded
     * @param  string $expression The SQL expression to insert
     * @return string             The matching expression
     */
    public static function sqlExpression($expression) {
        if(!self::$sqlExpressionSalt) {
            self::$sqlExpressionSalt = PHP_VERSION_ID < 70000 ? mcrypt_create_iv(8, MCRYPT_RAND) : random_bytes(8);
        }

        $hash = md5(self::$sqlExpressionSalt . $expression);

        self::$sqlExpressions[$hash] = $expression;

        return $hash;
    }

    public static function getSqlExpressions() {
        return self::$sqlExpressions;
    }
}