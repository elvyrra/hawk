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
     * @param string $url The not found URL
     * @param array $details The exception details
     */
    public function __construct($url = '', $details = array()) {
        if(!$url) {
            $url = App::request()->getFullUrl();
        }

        $details['url'] = $url;

        $message = Lang::get('main.http-error-404-message', $details);

        parent::__construct($message, $details);
    }

    /**
     * Get the not found URL
     *
     * @return string The exception URL
     */
    public function getUrl() {
        return $this->details['url'];
    }
}