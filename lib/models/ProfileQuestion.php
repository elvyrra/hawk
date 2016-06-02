<?php
/**
 * ProfileQuestion.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk;


/**
 * This model describes the customized profile data types
 *
 * @package BaseModels
 */
class ProfileQuestion extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    protected static $tablename = "ProfileQuestion";

    /**
     * The primary column in the table
     *
     * @var string
     */
    protected static $primaryColumn = 'name';

    /**
     * The model fields
     */
    protected static $fields = array(
        'name' => array(
            'type' => 'VARCHAR(32)'
        ),
        'type' => array(
            'type' => 'VARCHAR(16)'
        ),
        'parameters' => array(
            'type' => 'TEXT'
        ),
        'editable' => array(
            'type' => 'TINYINT(1)'
        ),
        'displayInRegister' => array(
            'type' => 'TINYINT(1)'
        ),
        'displayInProfile' => array(
            'type' => 'TINYINT(1)',
            'default' => '0'
        ),
        'order' => array(
            'type' => 'INT(11)'
        )
    );

    /**
     * The allowed input types for customization of profile questions
     *
     * @var array
     */
    public static $allowedTypes = array(
        'text',
        'textarea',
        'checkbox',
        'radio',
        'select',
        'datetime',
        'file'
    );

    /**
     * Get a question by it name
     *
     * @param string $name The name to search
     *
     * @return ProfileQuestion
     */
    public static function getByName($name){
        return self::getById($name);
    }

    /**
     * Get the profile questions that are displayed in the register form
     *
     * @return array
     */
    public static function getRegisterQuestions(){
        $example = array(
            'displayInRegister' => 1
        );
        return self::getListByExample(new DBExample($example), self::$primaryColumn, array(), array('order' => DB::SORT_ASC));
    }

    /**
     * Get the profile questions that are displayed publically on the users' profile
     *
     * @return array
     */
    public static function getDisplayProfileQuestions(){
        $example = array(
        'displayInProfile' => 1
        );
        return self::getListByExample(new DBExample($example), self::$primaryColumn, array(), array('order' => DB::SORT_ASC));
    }


    /**
     * Get the roles the question is configured for
     *
     * @return [type]       [description]
     */
    public function getRoles(){
        $params = json_decode($this->parameters, true);

        if(!empty($params['roles'])) {
            return $params['roles'];
        }

        return array();
    }

    /**
     * Check if a question is allowed for a given role
     *
     * @param int $roleId The role id
     *
     * @return boolean
     */
    public function isAllowedForRole($roleId){
        $roles = $this->getRoles();

        return in_array($roleId, $roles);
    }
}