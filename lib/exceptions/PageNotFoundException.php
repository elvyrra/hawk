<?php

namespace Hawk;

/**
 * This class describes the exceptions thrown when the page (or a resource) is not found
 */
class PageNotFoundException extends \Exception {
    /**
     * The called URL
     */
    private $url;

    /**
     * Constructor
     * @param string $message The exception message
     */
    public function __construct($message = '') {
        $this->url = App::request()->getFullUrl();

        if(!$message) {
            $message = Lang::get('main.404-message', array('uri' => $this->url));
        }

        parent::__construct($message);
    }


    public function getUrl() {
        return $this->url;
    }
}