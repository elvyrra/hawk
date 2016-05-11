<?php
/**
 * ProfileQuestionValue.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk;

/**
 * This model describes a the value of the permissions for a role
 *
 * @package BaseModels
 */
class RolePermission extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    protected static $tablename = "RolePermission";

    /**
     * The model fields
     */
    protected static $fields = array(
        'id' => array(
            'type' => 'INT(11)',
            'auto_increment' => true
        ),
        'roleId' => array(
            'type' => 'INT(11)'
        ),
        'permissionId' => array(
            'type' => 'INT(11)'
        ),
        'value' => array(
            'type' => 'TINYINT(1)'
        )
    );

    /**
     * The model constraints
     */
    protected static $constraints = array(
        'roleId' => array(
            'type' => 'foreign',
            'fields' => array(
                'roleId'
            ),
            'references' => array(
                'model' => 'Role',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ),
        'permissionId' => array(
            'type' => 'foreign',
            'fields' => array(
                'permissionId'
            ),
            'references' => array(
                'model' => 'Permission',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ),
        'roleId_2' => array(
            'type' => 'unique',
            'fields' => array(
                'roleId',
                'permissionId'
            )
        )
    );
}
