<?php
/**
 * PluginModel.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;


/**
 * This model describes the plugin data
 *
 * @package BaseModels
 */
class PluginModel extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    protected static $tablename = "Plugin";

    /**
     * The primary column
     */
    protected static $primaryColumn = 'name';

    /**
     * The DB instance name to get data in database default MAINDB
     *
     * @var string
     */
    protected static $dbname = MAINDB;

    /**
     * The model fields
     *
     * @var array
     */
    protected static $fields = array(
        'name' => array(
            'type' => 'VARCHAR(32)',
        ),
        'active' => array(
            'type' => 'TINYINT(1)'
        )
    );
}