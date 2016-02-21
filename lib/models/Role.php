<?php
/**
 * Role.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk;

/**
 * This model describes the roles that can be affected to the users
 *
 * @package BaseModels
 */
class Role extends Model{
    /**
     * The associated table
     *
     * @var string
     */
    public static $tablename = "Role";

    /**
     * The id corresponding to the 'Guest' role
     */
    const GUEST_ROLE_ID = 0;

    /**
     * The id corresponding to the 'Admin' role
     */
    const ADMIN_ROLE_ID = 1;

    /**
     * Get all roles
     *
     * @param string $index        The field to use as key in the returned array
     * @param array  $fields       The fields to get for each role. If not set, all table fields are got
     * @param array  $order        The order instructions for the returned array
     * @param bool   $includeGuest If set to true, then include 'Guest' role to the result
     *
     * @return array The roles list
     */
    public static function getAll($index = null, $fields = array(), $order = array(), $includeGuest= false){
        if($includeGuest) {
            return parent::getAll($index, $fields, $order);
        }
        else{
            $example = array('id' => array('$ne' => self::GUEST_ROLE_ID));
            return self::getListByExample(new DBExample($example), $index, $fields, $order);
        }
    }

    /**
     * Check if the role is removable. A role is removable if it's not 'Guest' or 'Admin', and not the default role
     *
     * @return boolean
     */
    public function isRemovable(){
        return $this->removable && Option::get('roles.default-role') != $this->id;
    }

    /**
     * Get the label of the role in the current language
     *
     * @return string
     */
    public function getLabel(){
        return Lang::get('roles.role-' . $this->id . '-label');
    }
}