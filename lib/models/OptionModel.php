<?php
/**
 * OptionModel.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;


/**
 * This model describes the options data
 *
 * @package BaseModels
 */
class OptionModel extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    protected static $tablename = "Option";

    /**
     * The primary columns
     */
    protected static $primaryColumn = array('plugin', 'key');

    /**
     * The model fields
     *
     * @var array
     */
    protected static $fields = array(
        'plugin' => array(
            'type' => 'VARCHAR(32)',
        ),
        'key' => array(
            'type' => 'VARCHAR(64)'
        ),
        'value' => array(
            'type' => 'VARCHAR(1024)'
        )
    );
}