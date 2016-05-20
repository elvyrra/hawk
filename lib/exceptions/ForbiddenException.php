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
class ForbiddenException extends \Exception {
    /**
     * The reason this exception has been thrown : 'login', 'permission'
     *
     * @var string
     */
    private $reason;

    /**
     * Constructor
     *
     * @param string $message The exception message
     * @param string $reason  The exception reason
     */
    public function __construct($message, $reason) {
        if(!$message) {
            $message = Lang::get('main.403-message');
        }

        parent::__construct($message);

        $this->reason = $reason;
    }

    /**
     * Get the exception reason
     *
     * @return string
     */
    public function getReason() {
        return $this->reason;
    }
}