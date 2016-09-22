<?php
/**
 * ForbiddenException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */


namespace Hawk;

/**
 * This class describes exceptions thrown when the user try to perform a forbidden action
 *
 * @package Exceptions
 */
class ForbiddenException extends HTTPException {
    const STATUS_CODE = 403;
}