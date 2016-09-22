<?php
/**
 * PageNotFoundException.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the exceptions thrown when the page (or a resource) is not found
 *
 * @package Exceptions
 */
class PageNotFoundException extends HTTPException {
    const STATUS_CODE = 404;

    /**
     * Constructor
     *
     * @param string $message The exception message
     */
    public function __construct($url = '') {
        if(!$url) {
            $url = App::request()->getFullUrl();
        }

        $details = array(
            'url' => $url
        );

        $message = Lang::get('main.http-error-404-message', $details);

        parent::__construct($message, $details);
    }

    /**
     * Get the not found URL
     *
     * @return string
     */
    public function getUrl() {
        return $this->details['url'];
    }
}