<?php
/**
 * BadRequestException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes exceptions thrown when a request has a bad format
 *
 * @package Exceptions
 */
class BadRequestException extends HTTPException {
    const STATUS_CODE = 400;
}