<?php
/**
 * SessionModel.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;


/**
 * This model describes the sessions data
 *
 * @package BaseModels
 */
class SessionModel extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    protected static $tablename = "Session";

    /**
     * The model fields
     *
     * @var array
     */
    protected static $fields = array(
        'id' => array(
            'type' => 'VARCHAR(64)'
        ),
        'data' => array(
            'type' => 'MEDIUMTEXT'
        ),
        'mtime' => array(
            'type' => 'INT(11)'
        )
    );
}