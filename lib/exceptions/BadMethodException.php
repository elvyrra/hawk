<?php
/**
 * BadMethodException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes exceptions thrown when a route is called with a bad method
 *
 * @package Exceptions
 */
class BadMethodException extends HTTPException {
    const STATUS_CODE = 405;

    /**
     * Constructor
     * @param string $path   The called path
     * @param string $method The bad method
     */
    public function __construct($path, $method) {
        $details = array(
            'path' => $path,
            'method' => $method
        );

        $message = Lang::get('main.http-error-' . self::STATUS_CODE . '-message', $details);

        parent::__construct($message, $details);
    }
}