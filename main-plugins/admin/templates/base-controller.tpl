<?php

/**
 * BaseController.class.php
 */

namespace {{ $namespace }};

class BaseController extends Controller{

    /**
     * Display a simple page with 'Hello world'
     */
    public function index(){
        return NoSidebarTab::make(array(
            'icon' => 'plug',
            'title' => Lang::get($this->_plugin . '.index-title'),
            'page' => Lang::get($this->_plugin . '.index-welcome-message', array('name' => $this->_plugin))
        ));
    }
}