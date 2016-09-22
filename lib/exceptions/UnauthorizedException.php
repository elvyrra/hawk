<?php
/**
 * UnauthorizedException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */


namespace Hawk;

/**
 * This class describes exceptions thrown when the user try to perform an action that needs to be logged,
 * whereas it is not logged in
 *
 * @package Exceptions
 */
class UnauthorizedException extends HTTPException {
    const STATUS_CODE = 401;
}