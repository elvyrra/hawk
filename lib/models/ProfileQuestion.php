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

     public static function getRoles($name){
        $question = self::getByName($name);
        
        if(!$question)
            return array();
         
         $params = json_decode($question->parameters, true);

        if(!empty($params['roles']))
            return $params['roles'];
        else
            return array();
    }

    public static function allowToRole($name, $roleId){
        $question = self::getByName($name);
        
        $params = json_decode($question->parameters, true);

        if(!empty($params['roles'])){
            if(in_array($roleId, $params['roles']))
                return true;
            else
                return false;
        }
        else
            return false;
    }
}