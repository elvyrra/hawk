<?php
/**
 * ConflictException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */


namespace Hawk;

/**
 * This class describes exceptions thrown when the user try to register / delete data that creates a conflict
 *
 * @package Exceptions
 */
class ConflictException extends HTTPException {
    const STATUS_CODE = 409;
}