<?php
/**
 * InternalErrorException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes exceptions thrown by a fatal error or a uncaught exception
 *
 * @package Exceptions
 */
class InternalErrorException extends HTTPException {
    const STATUS_CODE = 500;
}