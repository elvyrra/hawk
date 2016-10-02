<?php
/**
 * HTTPException.php
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
abstract class HTTPException extends \Exception {
    /**
     * The Exception status code
     *
     * @var int
     */
    protected $status;

    /**
     * The exception message
     *
     * @var string
     */
    protected $message;

    /**
     * The exception details
     *
     * @var array
     */
    protected $details = array();

    /**
     * Constructor
     *
     * @param string $message The exception message
     * @param Array  $details The exception details
     */
    public function __construct($message = '', $details = array()) {
        $status = static::STATUS_CODE;

        if(!$message) {
            $message = Lang::get('main.http-error-' . $status . '-message');
        }

        parent::__construct($message);

        $this->details = $details;
        $this->status = $status;
    }

    /**
     * Get the error status code
     *
     * @return int The status code of the exception
     */
    public function getStatusCode() {
        return $this->status;
    }

    /**
     * Get the exception details
     *
     * @return Array The exception details
     */
    public function getDetails() {
        return $this->details;
    }
}