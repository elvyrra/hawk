<?php
/**
 * ProfileQuestionValue.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk;

/**
 * This model describes the profile data value
 *
 * @package BaseModels
 */
class ProfileQuestionValue extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    protected static $tablename = "ProfileQuestionValue";

    /**
     * The model fields
     */
    protected static $fields = array(
        'id' => array(
            'type' => 'INT(11)',
            'auto_increment' => true
        ),
        'question' => array(
            'type' => 'VARCHAR(32)'
        ),
        'userId' => array(
            'type' => 'INT(11)'
        ),
        'value' => array(
            'type' => 'TEXT'
        )
    );

    /**
     * The model constraints
     */
    protected static $constraints = array(
        'question' => array(
            'type' => 'foreign',
            'fields' => array(
                'question'
            ),
            'references' => array(
                'model' => 'ProfileQuestion',
                'fields' => array('name')
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ),

        'question_2' => array(
            'type' => 'unique',
            'fields' => array(
                'question',
                'userId'
            )
        )
    );
}
