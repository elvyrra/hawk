<?php
/**
 * UserOption.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;


/**
 * This model describes the user options
 *
 * @package BaseModels
 */
class UserOption extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    protected static $tablename = "UserOption";

    /**
     * The DB instance name to get data in database default MAINDB
     *
     * @var string
     */
    protected static $dbname = MAINDB;

    /**
     * The primary column
     */
    protected static $primaryColumn = 'id';

    /**
     * The model fields
     *
     * @var array
     */
    protected static $fields = array(
        'userId' => array(
            'type' => 'INT(11)'
        ),
        'plugin' => array(
            'type' => 'VARCHAR(32)'
        ),
        'key' => array(
            'type' => 'VARCHAR(64)'
        ),
        'value' => array(
            'type' => 'VARCHAR(4096)'
        )
    );

    /**
     * The model constraints
     *
     * @var array
     */
    protected static $constraints = array(
        'userId' => array(
            'type' => 'unique',
            'fields' => array(
                'userId',
                'plugin',
                'key'
            )
        )
    );
}